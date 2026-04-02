<?php

namespace JeexWebp\Conversion\Method;

use JeexWebp\Conversion\ConversionResult;

class GdMethod implements MethodInterface {

    public function isAvailable(): bool {
        if ( ! extension_loaded( 'gd' ) ) {
            return false;
        }

        $info = gd_info();
        return ! empty( $info['WebP Support'] );
    }

    public function getName(): string {
        return 'gd';
    }

    public function getSupportedFormats(): array {
        $formats = [ 'webp' ];

        if ( function_exists( 'imageavif' ) ) {
            $info = gd_info();
            if ( ! empty( $info['AVIF Support'] ) ) {
                $formats[] = 'avif';
            }
        }

        return $formats;
    }

    public function convert( string $source, string $dest, array $options = [] ): ConversionResult {
        $quality = $options['quality'] ?? 80;
        $format  = $options['format'] ?? 'webp';

        if ( ! file_exists( $source ) ) {
            return ConversionResult::failed( $source, __( 'Source file not found.', 'jeex-webp' ) );
        }

        // Check format support
        if ( 'avif' === $format && ! in_array( 'avif', $this->getSupportedFormats(), true ) ) {
            return ConversionResult::failed( $source, __( 'AVIF is not supported by your GD library.', 'jeex-webp' ) );
        }

        $imageInfo = @getimagesize( $source );
        if ( false === $imageInfo ) {
            return ConversionResult::failed( $source, __( 'Cannot read image info.', 'jeex-webp' ) );
        }

        $mime = $imageInfo['mime'] ?? '';

        // Check memory before loading
        if ( ! $this->hasEnoughMemory( $imageInfo[0], $imageInfo[1] ) ) {
            return ConversionResult::skipped( $source, __( 'Image too large for available memory.', 'jeex-webp' ) );
        }

        try {
            $img = $this->loadImage( $source, $mime );
            if ( null === $img ) {
                return ConversionResult::failed( $source, __( 'Unsupported image format.', 'jeex-webp' ) );
            }

            // Skip animated GIFs
            if ( 'image/gif' === $mime && $this->isAnimatedGif( $source ) ) {
                imagedestroy( $img );
                return ConversionResult::skipped( $source, __( 'Animated GIF skipped.', 'jeex-webp' ) );
            }

            // Fix EXIF orientation for JPEG
            if ( in_array( $mime, [ 'image/jpeg', 'image/jpg' ], true ) ) {
                $img = $this->correctOrientation( $img, $source );
            }

            // Preserve PNG transparency
            if ( 'image/png' === $mime ) {
                imagesavealpha( $img, true );
                imagealphablending( $img, false );
            }

            // Ensure output directory exists
            $outputDir = dirname( $dest );
            if ( ! is_dir( $outputDir ) ) {
                wp_mkdir_p( $outputDir );
            }

            $quality = max( 20, min( 100, (int) $quality ) );

            // Convert based on format
            if ( 'avif' === $format ) {
                // AVIF quality: 0 = worst, 100 = best. Speed: 0 = slowest, 10 = fastest.
                $result = imageavif( $img, $dest, $quality, 6 );
            } else {
                $result = imagewebp( $img, $dest, $quality );
            }

            imagedestroy( $img );

            if ( ! $result || ! file_exists( $dest ) ) {
                /* translators: %s: output format name */
                return ConversionResult::failed( $source, sprintf( __( '%s conversion failed.', 'jeex-webp' ), strtoupper( $format ) ) );
            }

            $originalSize  = filesize( $source );
            $convertedSize = filesize( $dest );

            return ConversionResult::success( $source, $dest, $originalSize, $convertedSize );

        } catch ( \Exception $e ) {
            return ConversionResult::failed( $source, $e->getMessage() );
        }
    }

    /**
     * @return \GdImage|resource|null
     */
    private function loadImage( string $path, string $mime ) {
        switch ( $mime ) {
            case 'image/jpeg':
            case 'image/jpg':
                return @imagecreatefromjpeg( $path ) ?: null;

            case 'image/png':
                return @imagecreatefrompng( $path ) ?: null;

            case 'image/gif':
                return @imagecreatefromgif( $path ) ?: null;

            default:
                return null;
        }
    }

    /**
     * @param \GdImage|resource $img
     * @return \GdImage|resource
     */
    private function correctOrientation( $img, string $source ) {
        if ( ! function_exists( 'exif_read_data' ) ) {
            return $img;
        }

        $exif = @exif_read_data( $source );
        if ( empty( $exif['Orientation'] ) ) {
            return $img;
        }

        switch ( $exif['Orientation'] ) {
            case 2:
                imageflip( $img, IMG_FLIP_HORIZONTAL );
                break;
            case 3:
                $img = imagerotate( $img, 180, 0 );
                break;
            case 4:
                imageflip( $img, IMG_FLIP_VERTICAL );
                break;
            case 5:
                $img = imagerotate( $img, 270, 0 );
                imageflip( $img, IMG_FLIP_HORIZONTAL );
                break;
            case 6:
                $img = imagerotate( $img, 270, 0 );
                break;
            case 7:
                $img = imagerotate( $img, 90, 0 );
                imageflip( $img, IMG_FLIP_HORIZONTAL );
                break;
            case 8:
                $img = imagerotate( $img, 90, 0 );
                break;
        }

        return $img;
    }

    private function isAnimatedGif( string $path ): bool {
        $fh    = @fopen( $path, 'rb' );
        if ( ! $fh ) {
            return false;
        }

        $count = 0;
        while ( ! feof( $fh ) && $count < 2 ) {
            $chunk = fread( $fh, 102400 );
            $count += substr_count( $chunk, "\x00\x21\xF9\x04" );
        }
        fclose( $fh );

        return $count > 1;
    }

    private function hasEnoughMemory( int $width, int $height ): bool {
        $estimated = $width * $height * 4 * 1.8; // RGBA + safety margin
        $limit     = $this->getMemoryLimit();

        if ( $limit <= 0 ) {
            return true; // No limit set
        }

        $used = memory_get_usage( true );
        return ( $used + $estimated ) < $limit;
    }

    private function getMemoryLimit(): int {
        $limit = ini_get( 'memory_limit' );

        if ( '-1' === $limit ) {
            return 0;
        }

        $value = (int) $limit;
        $unit  = strtolower( substr( $limit, -1 ) );

        switch ( $unit ) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
                break;
        }

        return $value;
    }
}
