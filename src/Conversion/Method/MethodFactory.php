<?php

namespace JeexWebp\Conversion\Method;

use JeexWebp\Settings\SettingsManager;

class MethodFactory {

    /**
     * Create the best available conversion method.
     */
    public static function create( SettingsManager $settings ): MethodInterface {
        $preferred = $settings->get( 'method', 'auto' );

        if ( 'imagick' === $preferred ) {
            $method = new ImagickMethod();
            if ( $method->isAvailable() ) {
                return $method;
            }
        }

        if ( 'gd' === $preferred ) {
            $method = new GdMethod();
            if ( $method->isAvailable() ) {
                return $method;
            }
        }

        // Auto mode: try Imagick first, then GD
        $imagick = new ImagickMethod();
        if ( $imagick->isAvailable() ) {
            return $imagick;
        }

        $gd = new GdMethod();
        if ( $gd->isAvailable() ) {
            return $gd;
        }

        // Return Imagick as default (will fail gracefully on convert)
        return $imagick;
    }

    /**
     * Get all methods with their availability status.
     */
    public static function getAvailableMethods(): array {
        $methods = [
            new ImagickMethod(),
            new GdMethod(),
        ];

        $result = [];
        foreach ( $methods as $method ) {
            $result[ $method->getName() ] = [
                'name'      => $method->getName(),
                'available' => $method->isAvailable(),
                'formats'   => $method->isAvailable() ? $method->getSupportedFormats() : [],
            ];
        }

        return $result;
    }
}
