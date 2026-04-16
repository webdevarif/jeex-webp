<?php

namespace JeexWebp\Admin\Ajax;

defined( 'ABSPATH' ) || exit;

use JeexWebp\Conversion\PathResolver;

class DeleteCacheHandler {

    private PathResolver $pathResolver;

    public function __construct( PathResolver $pathResolver ) {
        $this->pathResolver = $pathResolver;
    }

    public function handle(): void {
        check_ajax_referer( 'jeex_webp_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized.', 'jeex-webp' ), 403 );
        }

        $deleted = $this->pathResolver->clearOutputDir();

        wp_send_json_success( [
            'deleted'  => $deleted,
            /* translators: %d: number of files deleted */
            'message'  => sprintf( __( '%d converted files deleted.', 'jeex-webp' ), $deleted ),
        ] );
    }
}
