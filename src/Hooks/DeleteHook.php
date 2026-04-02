<?php

namespace JeexWebp\Hooks;

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
                @unlink( $outputPath );
            }
        }

        // Clean up empty directories
        $this->cleanupEmptyDirs( $sourcePath );
    }

    private function cleanupEmptyDirs( string $sourcePath ): void {
        $outputPath = $this->pathResolver->getOutputPath( $sourcePath, 'webp' );
        if ( empty( $outputPath ) ) {
            return;
        }

        $dir       = dirname( $outputPath );
        $outputDir = rtrim( $this->pathResolver->getOutputDir(), '/' );

        // Walk up and remove empty directories
        while ( $dir !== $outputDir && is_dir( $dir ) ) {
            $items = @scandir( $dir );
            if ( false === $items || count( $items ) <= 2 ) { // Only . and ..
                @rmdir( $dir );
                $dir = dirname( $dir );
            } else {
                break;
            }
        }
    }
}
