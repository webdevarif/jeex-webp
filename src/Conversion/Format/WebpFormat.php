<?php

namespace JeexWebp\Conversion\Format;

defined( 'ABSPATH' ) || exit;

class WebpFormat implements FormatInterface {

    public function getName(): string {
        return 'WebP';
    }

    public function getExtension(): string {
        return 'webp';
    }

    public function getMimeType(): string {
        return 'image/webp';
    }

    public function isAvailable(): bool {
        return true;
    }
}
