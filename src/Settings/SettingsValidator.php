<?php

namespace JeexWebp\Settings;

defined( 'ABSPATH' ) || exit;

class SettingsValidator {

    /**
     * Sanitize a setting value based on its key.
     */
    public function sanitize( string $key, $value ) {
        switch ( $key ) {
            case 'quality':
                return $this->sanitizeQuality( $value );

            case 'method':
                return $this->sanitizeChoice( $value, [ 'auto', 'imagick', 'gd' ] );

            case 'output_format':
                return $this->sanitizeChoice( $value, [ 'webp', 'avif', 'both' ] );

            case 'serving_mode':
                return $this->sanitizeChoice( $value, [ 'auto', 'htaccess', 'passthru' ] );

            case 'auto_convert':
            case 'only_smaller':
            case 'keep_metadata':
            case 'cron_enabled':
                return (bool) $value;

            case 'directories':
                return $this->sanitizeDirectories( $value );

            case 'exclude_dirs':
                return sanitize_text_field( (string) $value );

            case 'conversion_limit':
                return (int) $value;

            case 'max_resolution':
                return absint( $value );

            case 'cron_batch_size':
                return max( 1, min( 50, absint( $value ) ) );

            default:
                return sanitize_text_field( (string) $value );
        }
    }

    private function sanitizeQuality( $value ): int {
        $quality = absint( $value );
        return max( 50, min( 100, $quality ) );
    }

    private function sanitizeChoice( $value, array $choices ): string {
        $value = sanitize_text_field( (string) $value );
        return in_array( $value, $choices, true ) ? $value : $choices[0];
    }

    private function sanitizeDirectories( $value ): array {
        if ( ! is_array( $value ) ) {
            return [ 'uploads' ];
        }

        $allowed = [ 'uploads', 'themes', 'plugins' ];
        return array_values( array_intersect( $value, $allowed ) );
    }
}
