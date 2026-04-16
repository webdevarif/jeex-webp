<?php

namespace JeexWebp\Admin;

defined( 'ABSPATH' ) || exit;

use JeexWebp\Conversion\Method\MethodFactory;
use JeexWebp\Settings\SettingsManager;

class AdminNotice {

    private SettingsManager $settings;

    public function __construct( SettingsManager $settings ) {
        $this->settings = $settings;
    }

    /**
     * Show admin notices on the plugin page only.
     */
    public function maybeShowNotices(): void {
        $screen = get_current_screen();
        if ( ! $screen || 'tools_page_jeex-webp' !== $screen->id ) {
            return;
        }

        $this->checkServerSupport();
    }

    private function checkServerSupport(): void {
        $methods = MethodFactory::getAvailableMethods();
        $hasMethod = false;

        foreach ( $methods as $method ) {
            if ( $method['available'] ) {
                $hasMethod = true;
                break;
            }
        }

        if ( ! $hasMethod ) {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html__( 'Jeex WebP: No image conversion library available. Please install the Imagick or GD PHP extension with WebP support.', 'jeex-webp' )
            );
        }
    }
}
