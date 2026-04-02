<?php

namespace JeexWebp\Serving;

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

                readfile( $outputPath );
                exit;
            }
        }
    }

    private function getPassthruFilePath(): string {
        return trailingslashit( WP_CONTENT_DIR ) . 'jeex-webp-passthru.php';
    }

    private function generatePassthruFile(): bool {
        $outputDir  = addslashes( $this->pathResolver->getOutputDir() );
        $uploadsDir = addslashes( $this->pathResolver->getUploadsDir() );

        $content = <<<'PHP'
<?php
/**
 * Jeex WebP Passthrough Server
 *
 * This file serves WebP/AVIF images when .htaccess rewrites are not available.
 * Generated automatically by Jeex WebP plugin.
 *
 * @package JeexWebp
 */

if ( ! isset( $_GET['src'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    http_response_code( 400 );
    exit;
}

// Sanitize: strip null bytes, parent traversal, and trim slashes.
$src = isset( $_GET['src'] ) ? (string) $_GET['src'] : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
$src = str_replace( array( '..', "\0", "\r", "\n" ), '', $src );
$src = preg_replace( '/[^a-zA-Z0-9_\-\/\.]/', '', $src );
$src = ltrim( $src, '/' );

if ( empty( $src ) ) {
    http_response_code( 400 );
    exit;
}

$extensions = array( 'jpg', 'jpeg', 'png', 'gif' );
$ext = strtolower( pathinfo( $src, PATHINFO_EXTENSION ) );

if ( ! in_array( $ext, $extensions, true ) ) {
    http_response_code( 403 );
    exit;
}

$accept = isset( $_SERVER['HTTP_ACCEPT'] ) ? (string) $_SERVER['HTTP_ACCEPT'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

PHP;

        $content .= <<<PHP
\$uploadsDir = '{$uploadsDir}';
\$outputDir  = '{$outputDir}';
PHP;

        $content .= <<<'PHP2'


$sourcePath = $uploadsDir . $src;
$avifPath   = $outputDir . $src . '.avif';
$webpPath   = $outputDir . $src . '.webp';

// Path traversal check.
$realSource  = realpath( dirname( $sourcePath ) );
$realUploads = realpath( $uploadsDir );

if ( false === $realSource || false === $realUploads || strpos( $realSource, $realUploads ) !== 0 ) {
    http_response_code( 403 );
    exit;
}

// Serve AVIF first if browser supports it and file exists.
if ( strpos( $accept, 'image/avif' ) !== false && file_exists( $avifPath ) ) {
    header( 'Content-Type: image/avif' );
    header( 'Content-Length: ' . filesize( $avifPath ) );
    header( 'Cache-Control: public, max-age=31536000' );
    header( 'Vary: Accept' );
    header( 'X-Jeex-WebP: avif' );
    readfile( $avifPath );
    exit;
}

// Serve WebP if browser supports it and file exists.
if ( strpos( $accept, 'image/webp' ) !== false && file_exists( $webpPath ) ) {
    header( 'Content-Type: image/webp' );
    header( 'Content-Length: ' . filesize( $webpPath ) );
    header( 'Cache-Control: public, max-age=31536000' );
    header( 'Vary: Accept' );
    header( 'X-Jeex-WebP: webp' );
    readfile( $webpPath );
    exit;
}

// Serve original if it exists.
if ( file_exists( $sourcePath ) ) {
    $mimeMap = array(
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
    );

    $mime = isset( $mimeMap[ $ext ] ) ? $mimeMap[ $ext ] : 'application/octet-stream';

    header( 'Content-Type: ' . $mime );
    header( 'Content-Length: ' . filesize( $sourcePath ) );
    header( 'Cache-Control: public, max-age=31536000' );
    readfile( $sourcePath );
    exit;
}

http_response_code( 404 );
exit;
PHP2;

        $filepath = $this->getPassthruFilePath();
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        $result = file_put_contents( $filepath, $content );
        return false !== $result;
    }
}
