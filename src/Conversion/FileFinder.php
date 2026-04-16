<?php

namespace JeexWebp\Conversion;

defined( 'ABSPATH' ) || exit;

use JeexWebp\Settings\SettingsManager;

class FileFinder {

    private PathResolver $pathResolver;
    private SettingsManager $settings;

    public function __construct( PathResolver $pathResolver, SettingsManager $settings ) {
        $this->pathResolver = $pathResolver;
        $this->settings     = $settings;
    }

    /**
     * Find all source images that can be converted.
     *
     * @return string[] Array of absolute file paths.
     */
    public function findAll(): array {
        return $this->findInDirectory( $this->pathResolver->getUploadsDir() );
    }

    /**
     * Find source images that haven't been converted yet.
     *
     * @return string[] Array of absolute file paths.
     */
    public function findUnconverted(): array {
        $all           = $this->findAll();
        $outputFormat  = $this->settings->get( 'output_format', 'webp' );
        $formats       = ( 'both' === $outputFormat ) ? [ 'webp', 'avif' ] : [ $outputFormat ];
        $unconverted   = [];

        foreach ( $all as $path ) {
            $needsConversion = false;
            foreach ( $formats as $format ) {
                if ( ! $this->pathResolver->hasConvertedFile( $path, $format ) ) {
                    $needsConversion = true;
                    break;
                }
            }
            if ( $needsConversion ) {
                $unconverted[] = $path;
            }
        }

        return $unconverted;
    }

    /**
     * Find unconverted files with pagination.
     *
     * @param int $offset Starting offset.
     * @param int $limit  Max files to return.
     * @return string[] Array of absolute file paths.
     */
    public function findUnconvertedBatch( int $offset, int $limit ): array {
        $unconverted = $this->findUnconverted();
        return array_slice( $unconverted, $offset, $limit );
    }

    /**
     * Count all source images.
     */
    public function countAll(): int {
        return count( $this->findAll() );
    }

    /**
     * Count unconverted images.
     */
    public function countUnconverted(): int {
        return count( $this->findUnconverted() );
    }

    /**
     * Count already converted images.
     */
    public function countConverted(): int {
        return $this->countAll() - $this->countUnconverted();
    }

    /**
     * Calculate total size of all source images.
     */
    public function getTotalSourceSize(): int {
        $total = 0;
        foreach ( $this->findAll() as $path ) {
            $total += filesize( $path ) ?: 0;
        }
        return $total;
    }

    /**
     * Calculate total size of converted images.
     */
    public function getTotalConvertedSize(): int {
        $total  = 0;
        $format = $this->settings->get( 'output_format', 'webp' );

        foreach ( $this->findAll() as $path ) {
            $outputPath = $this->pathResolver->getOutputPath( $path, $format );
            if ( file_exists( $outputPath ) ) {
                $total += filesize( $outputPath ) ?: 0;
            }
        }

        return $total;
    }

    /**
     * Calculate total saved bytes.
     */
    public function getTotalSavedBytes(): int {
        $saved  = 0;
        $format = $this->settings->get( 'output_format', 'webp' );

        foreach ( $this->findAll() as $path ) {
            $outputPath = $this->pathResolver->getOutputPath( $path, $format );
            if ( file_exists( $outputPath ) ) {
                $origSize    = filesize( $path ) ?: 0;
                $convertSize = filesize( $outputPath ) ?: 0;
                $saved += max( 0, $origSize - $convertSize );
            }
        }

        return $saved;
    }

    /**
     * Find images in a specific directory.
     *
     * @return string[]
     */
    private function findInDirectory( string $dir ): array {
        $files      = [];
        $extensions = $this->pathResolver->getSupportedExtensions();
        $excludes   = $this->getExcludedDirs();

        if ( ! is_dir( $dir ) ) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ( $iterator as $file ) {
            if ( ! $file->isFile() ) {
                continue;
            }

            $path = wp_normalize_path( $file->getPathname() );

            // Check exclusions
            if ( $this->isExcluded( $path, $excludes ) ) {
                continue;
            }

            $ext = strtolower( $file->getExtension() );
            if ( in_array( $ext, $extensions, true ) ) {
                $files[] = $path;
            }
        }

        return $files;
    }

    private function getExcludedDirs(): array {
        $exclude = $this->settings->get( 'exclude_dirs', '' );
        if ( empty( $exclude ) ) {
            return [];
        }

        return array_map( 'trim', explode( ',', $exclude ) );
    }

    private function isExcluded( string $path, array $excludes ): bool {
        foreach ( $excludes as $exclude ) {
            if ( ! empty( $exclude ) && strpos( $path, $exclude ) !== false ) {
                return true;
            }
        }
        return false;
    }
}
