<?php

namespace JeexWebp\Admin\Ajax;

defined( 'ABSPATH' ) || exit;

use JeexWebp\Conversion\Converter;
use JeexWebp\Conversion\FileFinder;

class BulkConvertHandler {

    private Converter $converter;
    private FileFinder $fileFinder;

    public function __construct( Converter $converter, FileFinder $fileFinder ) {
        $this->converter  = $converter;
        $this->fileFinder = $fileFinder;
    }

    /**
     * Handle scan request - count total and unconverted images.
     */
    public function handleScan(): void {
        check_ajax_referer( 'jeex_webp_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized.', 'jeex-webp' ), 403 );
        }

        $total       = $this->fileFinder->countAll();
        $unconverted = $this->fileFinder->countUnconverted();
        $converted   = $total - $unconverted;

        wp_send_json_success( [
            'total'       => $total,
            'converted'   => $converted,
            'unconverted' => $unconverted,
        ] );
    }

    /**
     * Handle batch conversion request.
     */
    public function handleConvertBatch(): void {
        check_ajax_referer( 'jeex_webp_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized.', 'jeex-webp' ), 403 );
        }

        $batchSize = 5;
        $offset    = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;

        $files = $this->fileFinder->findUnconvertedBatch( 0, $batchSize );

        if ( empty( $files ) ) {
            wp_send_json_success( [
                'done'      => true,
                'processed' => 0,
                'converted' => 0,
                'skipped'   => 0,
                'failed'    => 0,
                'results'   => [],
            ] );
        }

        $results   = $this->converter->convertBatch( $files );
        $converted = 0;
        $skipped   = 0;
        $failed    = 0;
        $details   = [];

        foreach ( $results as $result ) {
            if ( $result->isSuccess() ) {
                $converted++;
            } elseif ( $result->isSkipped() ) {
                $skipped++;
            } else {
                $failed++;
            }

            $details[] = [
                'file'    => basename( $result->getSourcePath() ),
                'status'  => $result->getStatus(),
                'saved'   => $result->isSuccess() ? size_format( $result->getSavedBytes() ) : '',
                'message' => $result->getMessage(),
            ];
        }

        // Check remaining
        $remaining = $this->fileFinder->countUnconverted();

        wp_send_json_success( [
            'done'      => $remaining <= 0,
            'processed' => count( $results ),
            'converted' => $converted,
            'skipped'   => $skipped,
            'failed'    => $failed,
            'remaining' => $remaining,
            'results'   => $details,
        ] );
    }
}
