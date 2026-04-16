<?php

namespace JeexWebp;

defined( 'ABSPATH' ) || exit;

use JeexWebp\Admin\AdminPage;
use JeexWebp\Admin\AdminNotice;
use JeexWebp\Admin\Ajax\BulkConvertHandler;
use JeexWebp\Admin\Ajax\StatsHandler;
use JeexWebp\Admin\Ajax\DeleteCacheHandler;
use JeexWebp\Conversion\Converter;
use JeexWebp\Conversion\PathResolver;
use JeexWebp\Conversion\FileFinder;
use JeexWebp\Conversion\Method\MethodFactory;
use JeexWebp\Hooks\UploadHook;
use JeexWebp\Hooks\DeleteHook;
use JeexWebp\Hooks\CronHook;
use JeexWebp\Serving\HtaccessStrategy;
use JeexWebp\Serving\PassthruStrategy;
use JeexWebp\Settings\SettingsManager;

final class Plugin {

    private static ?Plugin $instance = null;

    private string $pluginFile;
    private SettingsManager $settings;
    private PathResolver $pathResolver;
    private Converter $converter;

    public static function init( string $pluginFile ): void {
        if ( null === self::$instance ) {
            self::$instance = new self( $pluginFile );
        }
    }

    public static function getInstance(): ?Plugin {
        return self::$instance;
    }

    private function __construct( string $pluginFile ) {
        $this->pluginFile = $pluginFile;

        $this->settings     = new SettingsManager();
        $this->pathResolver = new PathResolver( $this->settings );
        $this->converter    = new Converter(
            MethodFactory::create( $this->settings ),
            $this->pathResolver,
            $this->settings
        );

        $this->registerActivation();
        $this->registerHooks();
    }

    private function registerActivation(): void {
        register_activation_hook( $this->pluginFile, [ Activator::class, 'activate' ] );
        register_deactivation_hook( $this->pluginFile, [ Deactivator::class, 'deactivate' ] );
    }

    private function registerHooks(): void {
        // Admin
        if ( is_admin() ) {
            $adminPage = new AdminPage( $this->settings );
            add_action( 'admin_menu', [ $adminPage, 'register' ] );
            add_action( 'admin_enqueue_scripts', [ $adminPage, 'enqueueAssets' ] );
            add_filter( 'plugin_action_links_' . JEEX_WEBP_BASENAME, [ $adminPage, 'addSettingsLink' ] );

            // AJAX handlers
            $fileFinder = new FileFinder( $this->pathResolver, $this->settings );

            $bulkHandler = new BulkConvertHandler( $this->converter, $fileFinder );
            add_action( 'wp_ajax_jeex_webp_scan', [ $bulkHandler, 'handleScan' ] );
            add_action( 'wp_ajax_jeex_webp_convert_batch', [ $bulkHandler, 'handleConvertBatch' ] );

            $statsHandler = new StatsHandler( $this->pathResolver, $fileFinder );
            add_action( 'wp_ajax_jeex_webp_stats', [ $statsHandler, 'handle' ] );

            $deleteHandler = new DeleteCacheHandler( $this->pathResolver );
            add_action( 'wp_ajax_jeex_webp_clear_cache', [ $deleteHandler, 'handle' ] );

            // Admin notices
            $adminNotice = new AdminNotice( $this->settings );
            add_action( 'admin_notices', [ $adminNotice, 'maybeShowNotices' ] );
        }

        // Upload & delete hooks
        $uploadHook = new UploadHook( $this->converter, $this->pathResolver );
        add_filter( 'wp_generate_attachment_metadata', [ $uploadHook, 'onGenerateMetadata' ], 10, 2 );

        $deleteHook = new DeleteHook( $this->pathResolver );
        add_action( 'delete_attachment', [ $deleteHook, 'onDelete' ] );

        // Cron — register schedule early so WP knows about the interval before scheduling.
        $cronHook = new CronHook( $this->converter, new FileFinder( $this->pathResolver, $this->settings ), $this->settings );
        $cronHook->registerSchedule();
        add_action( 'jeex_webp_cron_convert', [ $cronHook, 'run' ] );
        add_action( 'init', [ $cronHook, 'scheduleIfEnabled' ] );

        // Serving strategy
        $this->registerServingStrategy();
    }

    private function registerServingStrategy(): void {
        $mode = $this->settings->get( 'serving_mode', 'auto' );

        if ( 'passthru' === $mode ) {
            $passthru = new PassthruStrategy( $this->pathResolver );
            add_action( 'init', [ $passthru, 'maybeServe' ] );
        }
    }

    public function getSettings(): SettingsManager {
        return $this->settings;
    }

    public function getConverter(): Converter {
        return $this->converter;
    }

    public function getPathResolver(): PathResolver {
        return $this->pathResolver;
    }
}
