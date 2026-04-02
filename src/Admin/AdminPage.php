<?php

namespace JeexWebp\Admin;

use JeexWebp\Conversion\Method\MethodFactory;
use JeexWebp\Settings\SettingsManager;

class AdminPage {

    private SettingsManager $settings;
    private string $hookSuffix = '';

    public function __construct( SettingsManager $settings ) {
        $this->settings = $settings;
    }

    /**
     * Register admin menu page.
     */
    public function register(): void {
        $this->hookSuffix = add_management_page(
            __( 'Jeex WebP', 'jeex-webp' ),
            __( 'Jeex WebP', 'jeex-webp' ),
            'manage_options',
            'jeex-webp',
            [ $this, 'render' ]
        );
    }

    /**
     * Enqueue admin assets only on plugin page.
     */
    public function enqueueAssets( string $hook ): void {
        if ( $hook !== $this->hookSuffix ) {
            return;
        }

        wp_enqueue_style(
            'jeex-webp-admin',
            JEEX_WEBP_URL . 'assets/css/admin.css',
            [],
            JEEX_WEBP_VERSION
        );

        wp_enqueue_script(
            'jeex-webp-admin',
            JEEX_WEBP_URL . 'assets/js/admin.js',
            [],
            JEEX_WEBP_VERSION,
            true
        );

        wp_localize_script( 'jeex-webp-admin', 'jeexWebp', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'jeex_webp_nonce' ),
            'i18n'    => [
                'scanning'       => __( 'Scanning images...', 'jeex-webp' ),
                'converting'     => __( 'Converting images...', 'jeex-webp' ),
                'complete'       => __( 'Conversion complete!', 'jeex-webp' ),
                'error'          => __( 'An error occurred.', 'jeex-webp' ),
                'confirmClear'   => __( 'Are you sure you want to delete all converted WebP files? This cannot be undone.', 'jeex-webp' ),
                'cleared'        => __( 'Cache cleared successfully.', 'jeex-webp' ),
                'saving'         => __( 'Saving settings...', 'jeex-webp' ),
                'saved'          => __( 'Settings saved!', 'jeex-webp' ),
                'noImages'       => __( 'No unconverted images found.', 'jeex-webp' ),
            ],
        ] );
    }

    /**
     * Render the admin page.
     */
    public function render(): void {
        // Handle settings save
        if ( isset( $_POST['jeex_webp_save_settings'] ) ) {
            $this->handleSaveSettings();
        }

        $settings = $this->settings->getAll();
        $methods  = MethodFactory::getAvailableMethods();

        include JEEX_WEBP_DIR . 'templates/admin-page.php';
    }

    private function handleSaveSettings(): void {
        if ( ! check_admin_referer( 'jeex_webp_settings' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $data = [
            'quality'        => isset( $_POST['quality'] ) ? absint( $_POST['quality'] ) : 80,
            'method'         => isset( $_POST['method'] ) ? sanitize_text_field( wp_unslash( $_POST['method'] ) ) : 'auto',
            'output_format'  => isset( $_POST['output_format'] ) ? sanitize_text_field( wp_unslash( $_POST['output_format'] ) ) : 'webp',
            'auto_convert'   => ! empty( $_POST['auto_convert'] ),
            'only_smaller'   => ! empty( $_POST['only_smaller'] ),
            'keep_metadata'  => ! empty( $_POST['keep_metadata'] ),
            'serving_mode'   => isset( $_POST['serving_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['serving_mode'] ) ) : 'auto',
            'directories'    => isset( $_POST['directories'] ) ? array_map( 'sanitize_text_field', (array) $_POST['directories'] ) : [ 'uploads' ],
            'exclude_dirs'   => isset( $_POST['exclude_dirs'] ) ? sanitize_text_field( wp_unslash( $_POST['exclude_dirs'] ) ) : '',
            'cron_enabled'   => ! empty( $_POST['cron_enabled'] ),
            'cron_batch_size' => isset( $_POST['cron_batch_size'] ) ? absint( $_POST['cron_batch_size'] ) : 10,
        ];

        $this->settings->saveAll( $data );

        // Regenerate htaccess if serving mode changed
        $htaccess = new \JeexWebp\Serving\HtaccessStrategy( new \JeexWebp\Conversion\PathResolver( $this->settings ) );
        $htaccess->activate();

        add_settings_error( 'jeex_webp', 'settings_saved', __( 'Settings saved.', 'jeex-webp' ), 'success' );
    }
}
