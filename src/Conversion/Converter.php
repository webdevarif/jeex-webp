<?php

namespace JeexWebp\Conversion;

use JeexWebp\Conversion\Method\MethodInterface;
use JeexWebp\Settings\SettingsManager;

class Converter {

    private MethodInterface $method;
    private PathResolver $pathResolver;
    private SettingsManager $settings;

    public function __construct( MethodInterface $method, PathResolver $pathResolver, SettingsManager $settings ) {
        $this->method       = $method;
        $this->pathResolver = $pathResolver;
        $this->settings     = $settings;
    }

    /**
     * Convert a single image file.
     *
     * If output_format is 'both', converts to both WebP and AVIF.
     * Returns the result of the primary (first successful) conversion.
     */
    public function convert( string $sourcePath ): ConversionResult {
        if ( ! $this->method->isAvailable() ) {
            return ConversionResult::failed( $sourcePath, __( 'No conversion method available.', 'jeex-webp' ) );
        }

        if ( ! $this->pathResolver->isSupportedFile( $sourcePath ) ) {
            return ConversionResult::skipped( $sourcePath, __( 'Unsupported file format.', 'jeex-webp' ) );
        }

        if ( ! file_exists( $sourcePath ) ) {
            return ConversionResult::failed( $sourcePath, __( 'Source file not found.', 'jeex-webp' ) );
        }

        // Check conversion limit
        if ( ! $this->isWithinLimit() ) {
            return ConversionResult::skipped( $sourcePath, __( 'Conversion limit reached.', 'jeex-webp' ) );
        }

        if ( apply_filters( 'jeex_webp_skip_file', false, $sourcePath ) ) {
            return ConversionResult::skipped( $sourcePath, __( 'Skipped by filter.', 'jeex-webp' ) );
        }

        $outputFormat = $this->settings->get( 'output_format', 'webp' );
        $formats      = $this->getFormatsToConvert( $outputFormat );

        $primaryResult = null;

        foreach ( $formats as $format ) {
            $result = $this->convertToFormat( $sourcePath, $format );

            if ( null === $primaryResult ) {
                $primaryResult = $result;
            }
        }

        return $primaryResult ?? ConversionResult::failed( $sourcePath, __( 'No formats to convert.', 'jeex-webp' ) );
    }

    /**
     * Convert a single file to a specific format.
     */
    private function convertToFormat( string $sourcePath, string $format ): ConversionResult {
        // Check if this method supports the format
        if ( ! in_array( $format, $this->method->getSupportedFormats(), true ) ) {
            return ConversionResult::skipped(
                $sourcePath,
                /* translators: %s: format name */
                sprintf( __( '%s not supported by current method.', 'jeex-webp' ), strtoupper( $format ) )
            );
        }

        $outputPath = $this->pathResolver->getOutputPath( $sourcePath, $format );

        if ( empty( $outputPath ) ) {
            return ConversionResult::failed( $sourcePath, __( 'Cannot resolve output path.', 'jeex-webp' ) );
        }

        // Skip if already converted
        if ( file_exists( $outputPath ) ) {
            return ConversionResult::skipped( $sourcePath, __( 'Already converted.', 'jeex-webp' ) );
        }

        $options = [
            'quality'       => apply_filters( 'jeex_webp_quality', $this->settings->get( 'quality', 80 ), $sourcePath ),
            'keep_metadata' => $this->settings->get( 'keep_metadata', false ),
            'format'        => $format,
        ];

        do_action( 'jeex_webp_before_convert', $sourcePath, $outputPath, $options );

        $result = $this->method->convert( $sourcePath, $outputPath, $options );

        // Handle "only serve if smaller" setting
        if ( $result->isSuccess() && $this->settings->get( 'only_smaller', true ) ) {
            if ( $result->getConvertedSize() >= $result->getOriginalSize() ) {
                wp_delete_file( $outputPath );
                return ConversionResult::skipped(
                    $sourcePath,
                    /* translators: %s: format name */
                    sprintf( __( '%s file is not smaller than original.', 'jeex-webp' ), strtoupper( $format ) )
                );
            }
        }

        if ( $result->isSuccess() ) {
            $this->incrementConversionCount();
        }

        do_action( 'jeex_webp_after_convert', $result, $sourcePath );

        return $result;
    }

    /**
     * Get the list of formats to convert to.
     *
     * @return string[]
     */
    private function getFormatsToConvert( string $outputFormat ): array {
        if ( 'both' === $outputFormat ) {
            return [ 'avif', 'webp' ]; // AVIF first (preferred), WebP as fallback
        }

        return [ $outputFormat ];
    }

    /**
     * Convert multiple files.
     *
     * @param string[] $paths Array of source file paths.
     * @return ConversionResult[]
     */
    public function convertBatch( array $paths ): array {
        $results = [];

        foreach ( $paths as $path ) {
            $results[] = $this->convert( $path );
        }

        return $results;
    }

    /**
     * Get the current conversion method.
     */
    public function getMethod(): MethodInterface {
        return $this->method;
    }

    private function isWithinLimit(): bool {
        $limit = (int) $this->settings->get( 'conversion_limit', -1 );

        if ( $limit < 0 ) {
            return true; // Unlimited
        }

        $count = (int) get_transient( 'jeex_webp_monthly_count' );
        return $count < $limit;
    }

    private function incrementConversionCount(): void {
        $count = (int) get_transient( 'jeex_webp_monthly_count' );
        set_transient( 'jeex_webp_monthly_count', $count + 1, MONTH_IN_SECONDS );
    }
}
