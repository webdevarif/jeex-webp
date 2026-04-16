<?php

namespace JeexWebp\Hooks;

defined( 'ABSPATH' ) || exit;

use JeexWebp\Conversion\PathResolver;

class DeleteHook {

    private PathResolver $pathResolver;

    public function __construct( PathResolver $pathResolver ) {
        $this->pathResolver = $pathResolver;
    }

    /**
     * Delete converted WebP files when an attachment is deleted.
     *
     * @param int $attachmentId Attachment post ID.
     */
    public function onDelete( int $attachmentId ): void {
        $file = get_attached_file( $attachmentId );
        if ( ! $file ) {
            return;
        }

        // Delete converted version of the main file
        $this->deleteConvertedFile( $file );

        // Delete converted versions of thumbnails
        $metadata = wp_get_attachment_metadata( $attachmentId );
        if ( ! empty( $metadata['sizes'] ) ) {
            $uploadDir = dirname( $file );

            foreach ( $metadata['sizes'] as $size ) {
                if ( ! empty( $size['file'] ) ) {
                    $thumbPath = $uploadDir . '/' . $size['file'];
                    $this->deleteConvertedFile( $thumbPath );
                }
            }
        }
    }

    private function deleteConvertedFile( string $sourcePath ): void {
        $formats = [ 'webp', 'avif' ];

        foreach ( $formats as $format ) {
            $outputPath = $this->pathResolver->getOutputPath( $sourcePath, $format );
            if ( ! empty( $outputPath ) && file_exists( $outputPath ) ) {
                wp_delete_file( $outputPath );
            }
        }

        // Clean up empty directories
        $this->cleanupEmptyDirs( $sourcePath );
    }

    private function cleanupEmptyDirs( string $sourcePath ): void {
        global $wp_filesystem;

        $outputPath = $this->pathResolver->getOutputPath( $sourcePath, 'webp' );
        if ( empty( $outputPath ) ) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();

        if ( ! $wp_filesystem instanceof \WP_Filesystem_Base ) {
            return;
        }

        $dir       = dirname( $outputPath );
        $outputDir = rtrim( $this->pathResolver->getOutputDir(), '/' );

        // Walk up and remove empty directories
        while ( $dir !== $outputDir && $wp_filesystem->is_dir( $dir ) ) {
            $dirlist = $wp_filesystem->dirlist( $dir );
            if ( empty( $dirlist ) ) {
                $wp_filesystem->rmdir( $dir );
                $dir = dirname( $dir );
            } else {
                break;
            }
        }
    }
}
