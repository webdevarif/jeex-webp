<?php

namespace JeexWebp\Conversion\Method;

defined( 'ABSPATH' ) || exit;

use JeexWebp\Conversion\ConversionResult;
use Imagick;

class ImagickMethod implements MethodInterface {

    public function isAvailable(): bool {
        if ( ! extension_loaded( 'imagick' ) || ! class_exists( '\Imagick' ) ) {
            return false;
        }

        $formats = \Imagick::queryFormats( 'WEBP' );
        return ! empty( $formats );
    }

    public function getName(): string {
        return 'imagick';
    }

    public function getSupportedFormats(): array {
        $formats = [ 'webp' ];

        if ( class_exists( '\Imagick' ) ) {
            $avif = \Imagick::queryFormats( 'AVIF' );
            if ( ! empty( $avif ) ) {
                $formats[] = 'avif';
            }
        }

        return $formats;
    }

    public function convert( string $source, string $dest, array $options = [] ): ConversionResult {
        $quality      = $options['quality'] ?? 80;
        $keepMetadata = $options['keep_metadata'] ?? false;
        $format       = $options['format'] ?? 'webp';

        if ( ! file_exists( $source ) ) {
            return ConversionResult::failed( $source, __( 'Source file not found.', 'jeex-webp' ) );
        }

        // Check format support
        if ( 'avif' === $format && ! in_array( 'avif', $this->getSupportedFormats(), true ) ) {
            return ConversionResult::failed( $source, __( 'AVIF is not supported by your Imagick library.', 'jeex-webp' ) );
        }

        try {
            $img = new Imagick( $source );

            // Skip animated images (animated GIF)
            if ( $img->getNumberImages() > 1 ) {
                $img->destroy();
                return ConversionResult::skipped( $source, __( 'Animated image skipped.', 'jeex-webp' ) );
            }

            // Fix EXIF orientation
            $img->autoOrient();

            // Handle CMYK colorspace
            if ( $img->getImageColorspace() === Imagick::COLORSPACE_CMYK ) {
                $img->transformImageColorspace( Imagick::COLORSPACE_SRGB );
            }

            // Set output format and quality
            $img->setImageFormat( $format );
            $img->setImageCompressionQuality( min( (int) $quality, 100 ) );

            // Format-specific settings
            if ( 'webp' === $format ) {
                if ( $img->getImageAlphaChannel() ) {
                    $img->setOption( 'webp:alpha-quality', '100' );
                }
                $img->setOption( 'webp:method', '4' );
            } elseif ( 'avif' === $format ) {
                $img->setOption( 'heic:speed', '6' );
            }

            // Strip metadata unless configured to keep it
            if ( ! $keepMetadata ) {
                $img->stripImage();
            }

            // Ensure output directory exists
            $outputDir = dirname( $dest );
            if ( ! is_dir( $outputDir ) ) {
                wp_mkdir_p( $outputDir );
            }

            $img->writeImage( $dest );
            $img->destroy();

            // Verify conversion succeeded and file exists
            if ( ! file_exists( $dest ) ) {
                return ConversionResult::failed( $source, __( 'Output file was not created.', 'jeex-webp' ) );
            }

            $originalSize  = filesize( $source );
            $convertedSize = filesize( $dest );

            return ConversionResult::success( $source, $dest, $originalSize, $convertedSize );

        } catch ( \ImagickException $e ) {
            return ConversionResult::failed( $source, $e->getMessage() );
        } catch ( \Exception $e ) {
            return ConversionResult::failed( $source, $e->getMessage() );
        }
    }
}
