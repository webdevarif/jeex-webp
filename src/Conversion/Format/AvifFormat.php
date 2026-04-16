<?php

namespace JeexWebp\Conversion\Format;

defined( 'ABSPATH' ) || exit;

use JeexWebp\Conversion\Method\MethodFactory;
use JeexWebp\Settings\SettingsManager;

/**
 * AVIF format support.
 */
class AvifFormat implements FormatInterface {

    public function getName(): string {
        return 'AVIF';
    }

    public function getExtension(): string {
        return 'avif';
    }

    public function getMimeType(): string {
        return 'image/avif';
    }

    /**
     * Check if AVIF is available via any conversion method.
     */
    public function isAvailable(): bool {
        $methods = MethodFactory::getAvailableMethods();

        foreach ( $methods as $method ) {
            if ( $method['available'] && in_array( 'avif', $method['formats'], true ) ) {
                return true;
            }
        }

        return false;
    }
}
