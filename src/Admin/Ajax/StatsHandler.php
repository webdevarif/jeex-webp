<?php

namespace JeexWebp\Admin\Ajax;

use JeexWebp\Conversion\PathResolver;
use JeexWebp\Conversion\FileFinder;

class StatsHandler {

    private PathResolver $pathResolver;
    private FileFinder $fileFinder;

    public function __construct( PathResolver $pathResolver, FileFinder $fileFinder ) {
        $this->pathResolver = $pathResolver;
        $this->fileFinder   = $fileFinder;
    }

    public function handle(): void {
        check_ajax_referer( 'jeex_webp_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized.', 'jeex-webp' ), 403 );
        }

        $total       = $this->fileFinder->countAll();
        $converted   = $this->fileFinder->countConverted();
        $unconverted = $total - $converted;
        $savedBytes  = $this->fileFinder->getTotalSavedBytes();
        $sourceSize  = $this->fileFinder->getTotalSourceSize();

        wp_send_json_success( [
            'total'            => $total,
            'converted'        => $converted,
            'unconverted'      => $unconverted,
            'saved_bytes'      => $savedBytes,
            'saved_formatted'  => size_format( $savedBytes ),
            'source_size'      => $sourceSize,
            'source_formatted' => size_format( $sourceSize ),
            'percent'          => $total > 0 ? round( ( $converted / $total ) * 100, 1 ) : 0,
        ] );
    }
}
