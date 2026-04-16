<?php

namespace JeexWebp\Conversion;

defined( 'ABSPATH' ) || exit;

use JeexWebp\Settings\SettingsManager;

class PathResolver {

    private SettingsManager $settings;
    private ?string $uploadsDir = null;
    private ?string $outputDir  = null;

    public function __construct( SettingsManager $settings ) {
        $this->settings = $settings;
    }

    /**
     * Get the uploads base directory (absolute path).
     */
    public function getUploadsDir(): string {
        if ( null === $this->uploadsDir ) {
            $uploadDir        = wp_upload_dir();
            $this->uploadsDir = trailingslashit( $uploadDir['basedir'] );
        }
        return $this->uploadsDir;
    }

    /**
     * Get the WebP output base directory (absolute path).
     */
    public function getOutputDir(): string {
        if ( null === $this->outputDir ) {
            $contentDir      = trailingslashit( WP_CONTENT_DIR );
            $dirName         = apply_filters( 'jeex_webp_output_dir', 'uploads-webpc' );
            $this->outputDir = $contentDir . $dirName . '/';
        }
        return $this->outputDir;
    }

    /**
     * Get the output path for a given source image.
     *
     * Example:
     *   Source: /wp-content/uploads/2024/01/photo.jpg
     *   Output: /wp-content/uploads-webpc/2024/01/photo.jpg.webp
     */
    public function getOutputPath( string $sourcePath, string $format = 'webp' ): string {
        $relativePath = $this->getRelativePath( $sourcePath );

        if ( empty( $relativePath ) ) {
            return '';
        }

        return $this->getOutputDir() . $relativePath . '.' . $format;
    }

    /**
     * Get the relative path from the uploads directory.
     */
    public function getRelativePath( string $absolutePath ): string {
        $uploadsDir = $this->getUploadsDir();

        // Normalize path separators
        $absolutePath = wp_normalize_path( $absolutePath );
        $uploadsDir   = wp_normalize_path( $uploadsDir );

        if ( strpos( $absolutePath, $uploadsDir ) !== 0 ) {
            return '';
        }

        return substr( $absolutePath, strlen( $uploadsDir ) );
    }

    /**
     * Get the source path from a relative path.
     */
    public function getSourcePath( string $relativePath ): string {
        return $this->getUploadsDir() . $relativePath;
    }

    /**
     * Check if a converted file exists for the given source.
     */
    public function hasConvertedFile( string $sourcePath, string $format = 'webp' ): bool {
        $outputPath = $this->getOutputPath( $sourcePath, $format );
        return ! empty( $outputPath ) && file_exists( $outputPath );
    }

    /**
     * Ensure the output directory exists.
     */
    public function ensureOutputDir(): bool {
        $dir = $this->getOutputDir();

        if ( is_dir( $dir ) ) {
            return true;
        }

        return wp_mkdir_p( $dir );
    }

    /**
     * Get the uploads base URL.
     */
    public function getUploadsUrl(): string {
        $uploadDir = wp_upload_dir();
        return trailingslashit( $uploadDir['baseurl'] );
    }

    /**
     * Get the output base URL.
     */
    public function getOutputUrl(): string {
        $contentUrl = trailingslashit( content_url() );
        $dirName    = apply_filters( 'jeex_webp_output_dir', 'uploads-webpc' );
        return $contentUrl . $dirName . '/';
    }

    /**
     * Get supported source extensions.
     */
    public function getSupportedExtensions(): array {
        return [ 'jpg', 'jpeg', 'png', 'gif' ];
    }

    /**
     * Check if a file has a supported extension.
     */
    public function isSupportedFile( string $path ): bool {
        $ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
        return in_array( $ext, $this->getSupportedExtensions(), true );
    }

    /**
     * Delete all converted files in the output directory.
     *
     * @return int Number of files deleted.
     */
    public function clearOutputDir(): int {
        $dir = $this->getOutputDir();

        if ( ! is_dir( $dir ) ) {
            return 0;
        }

        return $this->deleteDirectoryContents( $dir );
    }

    private function deleteDirectoryContents( string $dir ): int {
        global $wp_filesystem;

        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();

        $count = 0;
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ( $items as $item ) {
            if ( $item->isFile() ) {
                wp_delete_file( $item->getPathname() );
                $count++;
            } elseif ( $item->isDir() && $wp_filesystem instanceof \WP_Filesystem_Base ) {
                $wp_filesystem->rmdir( $item->getPathname() );
            }
        }

        return $count;
    }
}
