<?php

namespace JeexWebp\Hooks;

use JeexWebp\Conversion\Converter;
use JeexWebp\Conversion\PathResolver;

class UploadHook {

    private Converter $converter;
    private PathResolver $pathResolver;

    public function __construct( Converter $converter, PathResolver $pathResolver ) {
        $this->converter    = $converter;
        $this->pathResolver = $pathResolver;
    }

    /**
     * Convert image and its thumbnails after WordPress generates attachment metadata.
     *
     * @param array $metadata      Attachment metadata.
     * @param int   $attachmentId  Attachment ID.
     * @return array Unmodified metadata.
     */
    public function onGenerateMetadata( array $metadata, int $attachmentId ): array {
        $settings = \JeexWebp\Plugin::getInstance()?->getSettings();
        if ( ! $settings || ! $settings->get( 'auto_convert', true ) ) {
            return $metadata;
        }

        $file = get_attached_file( $attachmentId );
        if ( ! $file || ! $this->pathResolver->isSupportedFile( $file ) ) {
            return $metadata;
        }

        // Convert the original/main image
        $this->converter->convert( $file );

        // Convert thumbnails
        if ( ! empty( $metadata['sizes'] ) ) {
            $uploadDir = dirname( $file );

            foreach ( $metadata['sizes'] as $size ) {
                if ( ! empty( $size['file'] ) ) {
                    $thumbPath = $uploadDir . '/' . $size['file'];
                    if ( file_exists( $thumbPath ) ) {
                        $this->converter->convert( $thumbPath );
                    }
                }
            }
        }

        return $metadata;
    }
}
