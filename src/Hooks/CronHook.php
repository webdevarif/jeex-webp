<?php

namespace JeexWebp\Hooks;

use JeexWebp\Conversion\Converter;
use JeexWebp\Conversion\FileFinder;
use JeexWebp\Settings\SettingsManager;

class CronHook {

    private Converter $converter;
    private FileFinder $fileFinder;
    private SettingsManager $settings;

    public const CRON_HOOK = 'jeex_webp_cron_convert';

    public function __construct( Converter $converter, FileFinder $fileFinder, SettingsManager $settings ) {
        $this->converter  = $converter;
        $this->fileFinder = $fileFinder;
        $this->settings   = $settings;
    }

    /**
     * Schedule cron event if background conversion is enabled.
     */
    public function scheduleIfEnabled(): void {
        $enabled = $this->settings->get( 'cron_enabled', false );

        if ( $enabled && ! wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_schedule_event( time(), 'jeex_webp_interval', self::CRON_HOOK );
            add_filter( 'cron_schedules', [ $this, 'addCronSchedule' ] );
        } elseif ( ! $enabled && wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_clear_scheduled_hook( self::CRON_HOOK );
        }

        // Always register the schedule so WP knows about it
        add_filter( 'cron_schedules', [ $this, 'addCronSchedule' ] );
    }

    /**
     * Add custom cron interval.
     */
    public function addCronSchedule( array $schedules ): array {
        $schedules['jeex_webp_interval'] = [
            'interval' => 5 * MINUTE_IN_SECONDS,
            'display'  => __( 'Every 5 Minutes (Jeex WebP)', 'jeex-webp' ),
        ];
        return $schedules;
    }

    /**
     * Run the cron conversion job.
     */
    public function run(): void {
        $batchSize   = (int) $this->settings->get( 'cron_batch_size', 10 );
        $unconverted = $this->fileFinder->findUnconvertedBatch( 0, $batchSize );

        if ( empty( $unconverted ) ) {
            // All done, disable cron
            wp_clear_scheduled_hook( self::CRON_HOOK );
            return;
        }

        $this->converter->convertBatch( $unconverted );
    }

    /**
     * Unschedule all cron events for this plugin.
     */
    public static function unschedule(): void {
        wp_clear_scheduled_hook( self::CRON_HOOK );
    }
}
