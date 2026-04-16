<?php

namespace JeexWebp\Serving;

defined( 'ABSPATH' ) || exit;

use JeexWebp\Conversion\PathResolver;

class PassthruStrategy implements ServeStrategy {

    private PathResolver $pathResolver;

    public function __construct( PathResolver $pathResolver ) {
        $this->pathResolver = $pathResolver;
    }

    public function getName(): string {
        return 'passthru';
    }

    public function activate(): bool {
        return $this->generatePassthruFile();
    }

    public function deactivate(): bool {
        $file = $this->getPassthruFilePath();

        if ( file_exists( $file ) ) {
            wp_delete_file( $file );
        }

        return true;
    }

    public function isActive(): bool {
        return file_exists( $this->getPassthruFilePath() );
    }

    /**
     * Handle passthrough serving (called on 'init' hook).
     */
    public function maybeServe(): void {
        if ( ! isset( $_GET['jeex_webp_src'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            return;
        }

        $src = sanitize_text_field( wp_unslash( $_GET['jeex_webp_src'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

        if ( empty( $src ) ) {
            return;
        }

        $this->serveWebp( $src );
    }

    private function serveWebp( string $requestedPath ): void {
        // Validate the path is within uploads
        $uploadsDir = $this->pathResolver->getUploadsDir();
        $sourcePath = realpath( $uploadsDir . ltrim( $requestedPath, '/' ) );

        if ( false === $sourcePath || strpos( $sourcePath, realpath( $uploadsDir ) ) !== 0 ) {
            return; // Path traversal prevention
        }

        if ( ! $this->pathResolver->isSupportedFile( $sourcePath ) ) {
            return;
        }

        $accept = isset( $_SERVER['HTTP_ACCEPT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT'] ) ) : '';

        // Try AVIF first (better compression), then WebP
        $formatsToTry = [];
        if ( strpos( $accept, 'image/avif' ) !== false ) {
            $formatsToTry[] = [ 'format' => 'avif', 'mime' => 'image/avif' ];
        }
        if ( strpos( $accept, 'image/webp' ) !== false ) {
            $formatsToTry[] = [ 'format' => 'webp', 'mime' => 'image/webp' ];
        }

        if ( empty( $formatsToTry ) ) {
            return;
        }

        foreach ( $formatsToTry as $fmt ) {
            $outputPath = $this->pathResolver->getOutputPath( $sourcePath, $fmt['format'] );

            if ( ! empty( $outputPath ) && file_exists( $outputPath ) ) {
                header( 'Content-Type: ' . $fmt['mime'] );
                header( 'Content-Length: ' . filesize( $outputPath ) );
                header( 'Cache-Control: public, max-age=31536000' );
                header( 'Vary: Accept' );
                header( 'X-Jeex-WebP: ' . $fmt['format'] );

                readfile( $outputPath ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
                exit;
            }
        }
    }

    /**
     * Get the passthru file path inside the output directory.
     */
    private function getPassthruFilePath(): string {
        return $this->pathResolver->getOutputDir() . 'passthru.php';
    }

    private function generatePassthruFile(): bool {
        global $wp_filesystem;

        if ( ! $this->initFilesystem() ) {
            return false;
        }

        // Ensure output dir exists for the passthru file.
        $this->pathResolver->ensureOutputDir();

        $outputDir  = addslashes( $this->pathResolver->getOutputDir() );
        $uploadsDir = addslashes( $this->pathResolver->getUploadsDir() );

        $content  = "<?php\n";
        $content .= "/**\n";
        $content .= " * Jeex WebP Passthrough Server\n";
        $content .= " *\n";
        $content .= " * Serves WebP/AVIF images when .htaccess rewrites are not available.\n";
        $content .= " * Generated automatically by Jeex WebP plugin.\n";
        $content .= " *\n";
        $content .= " * @package JeexWebp\n";
        $content .= " */\n\n";

        $content .= 'if ( ! isset( $_GET[\'src\'] ) ) {' . "\n";
        $content .= "    http_response_code( 400 );\n";
        $content .= "    exit;\n";
        $content .= "}\n\n";

        $content .= '$src = isset( $_GET[\'src\'] ) ? (string) $_GET[\'src\'] : \'\';' . "\n";
        $content .= '$src = str_replace( array( \'..\', "\\0", "\\r", "\\n" ), \'\', $src );' . "\n";
        $content .= '$src = preg_replace( \'/[^a-zA-Z0-9_\\-\\/\\.]/\', \'\', $src );' . "\n";
        $content .= '$src = ltrim( $src, \'/\' );' . "\n\n";

        $content .= 'if ( empty( $src ) ) {' . "\n";
        $content .= "    http_response_code( 400 );\n";
        $content .= "    exit;\n";
        $content .= "}\n\n";

        $content .= '$extensions = array( \'jpg\', \'jpeg\', \'png\', \'gif\' );' . "\n";
        $content .= '$ext = strtolower( pathinfo( $src, PATHINFO_EXTENSION ) );' . "\n\n";

        $content .= 'if ( ! in_array( $ext, $extensions, true ) ) {' . "\n";
        $content .= "    http_response_code( 403 );\n";
        $content .= "    exit;\n";
        $content .= "}\n\n";

        $content .= '$accept = isset( $_SERVER[\'HTTP_ACCEPT\'] ) ? (string) $_SERVER[\'HTTP_ACCEPT\'] : \'\';' . "\n\n";

        $content .= '$uploadsDir = \'' . $uploadsDir . "';\n";
        $content .= '$outputDir  = \'' . $outputDir . "';\n\n";

        $content .= '$sourcePath = $uploadsDir . $src;' . "\n";
        $content .= '$avifPath   = $outputDir . $src . \'.avif\';' . "\n";
        $content .= '$webpPath   = $outputDir . $src . \'.webp\';' . "\n\n";

        $content .= '$realSource  = realpath( dirname( $sourcePath ) );' . "\n";
        $content .= '$realUploads = realpath( $uploadsDir );' . "\n\n";

        $content .= 'if ( false === $realSource || false === $realUploads || strpos( $realSource, $realUploads ) !== 0 ) {' . "\n";
        $content .= "    http_response_code( 403 );\n";
        $content .= "    exit;\n";
        $content .= "}\n\n";

        $content .= 'if ( strpos( $accept, \'image/avif\' ) !== false && file_exists( $avifPath ) ) {' . "\n";
        $content .= '    header( \'Content-Type: image/avif\' );' . "\n";
        $content .= '    header( \'Content-Length: \' . filesize( $avifPath ) );' . "\n";
        $content .= '    header( \'Cache-Control: public, max-age=31536000\' );' . "\n";
        $content .= '    header( \'Vary: Accept\' );' . "\n";
        $content .= '    header( \'X-Jeex-WebP: avif\' );' . "\n";
        $content .= '    readfile( $avifPath );' . "\n";
        $content .= "    exit;\n";
        $content .= "}\n\n";

        $content .= 'if ( strpos( $accept, \'image/webp\' ) !== false && file_exists( $webpPath ) ) {' . "\n";
        $content .= '    header( \'Content-Type: image/webp\' );' . "\n";
        $content .= '    header( \'Content-Length: \' . filesize( $webpPath ) );' . "\n";
        $content .= '    header( \'Cache-Control: public, max-age=31536000\' );' . "\n";
        $content .= '    header( \'Vary: Accept\' );' . "\n";
        $content .= '    header( \'X-Jeex-WebP: webp\' );' . "\n";
        $content .= '    readfile( $webpPath );' . "\n";
        $content .= "    exit;\n";
        $content .= "}\n\n";

        $content .= 'if ( file_exists( $sourcePath ) ) {' . "\n";
        $content .= '    $mimeMap = array(' . "\n";
        $content .= "        'jpg'  => 'image/jpeg',\n";
        $content .= "        'jpeg' => 'image/jpeg',\n";
        $content .= "        'png'  => 'image/png',\n";
        $content .= "        'gif'  => 'image/gif',\n";
        $content .= "    );\n";
        $content .= '    $mime = isset( $mimeMap[ $ext ] ) ? $mimeMap[ $ext ] : \'application/octet-stream\';' . "\n";
        $content .= '    header( \'Content-Type: \' . $mime );' . "\n";
        $content .= '    header( \'Content-Length: \' . filesize( $sourcePath ) );' . "\n";
        $content .= '    header( \'Cache-Control: public, max-age=31536000\' );' . "\n";
        $content .= '    readfile( $sourcePath );' . "\n";
        $content .= "    exit;\n";
        $content .= "}\n\n";

        $content .= "http_response_code( 404 );\n";
        $content .= "exit;\n";

        $filepath = $this->getPassthruFilePath();
        return $wp_filesystem->put_contents( $filepath, $content, FS_CHMOD_FILE );
    }

    /**
     * Initialize the WP_Filesystem.
     */
    private function initFilesystem(): bool {
        global $wp_filesystem;

        if ( $wp_filesystem instanceof \WP_Filesystem_Base ) {
            return true;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        return WP_Filesystem();
    }
}
