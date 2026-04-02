<?php

namespace JeexWebp\Conversion\Method;

use JeexWebp\Conversion\ConversionResult;

interface MethodInterface {

    /**
     * Check if this conversion method is available on the server.
     */
    public function isAvailable(): bool;

    /**
     * Get the method name.
     */
    public function getName(): string;

    /**
     * Get supported output formats (e.g., ['webp']).
     */
    public function getSupportedFormats(): array;

    /**
     * Convert a single image file.
     *
     * @param string $source  Absolute path to source image.
     * @param string $dest    Absolute path for output file.
     * @param array  $options Conversion options: quality, keep_metadata, etc.
     *
     * @return ConversionResult
     */
    public function convert( string $source, string $dest, array $options = [] ): ConversionResult;
}
