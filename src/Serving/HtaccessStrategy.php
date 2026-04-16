<?php

namespace JeexWebp\Serving;

defined( 'ABSPATH' ) || exit;

use JeexWebp\Conversion\PathResolver;

class HtaccessStrategy implements ServeStrategy {

    private PathResolver $pathResolver;
    private RewriteRulesGenerator $generator;

    public function __construct( PathResolver $pathResolver ) {
        $this->pathResolver = $pathResolver;
        $this->generator    = new RewriteRulesGenerator( $pathResolver );
    }

    public function getName(): string {
        return 'htaccess';
    }

    public function activate(): bool {
        $success = true;

        // 1. Write rewrite rules to uploads/.htaccess
        $uploadsHtaccess = $this->pathResolver->getUploadsDir() . '.htaccess';
        if ( ! $this->addRulesToFile( $uploadsHtaccess, $this->generator->getUploadsRewriteRules() ) ) {
            $success = false;
        }

        // 2. Write MIME + caching rules to output dir/.htaccess
        $outputHtaccess = $this->pathResolver->getOutputDir() . '.htaccess';
        $this->pathResolver->ensureOutputDir();
        if ( ! $this->addRulesToFile( $outputHtaccess, $this->generator->getOutputDirRules() ) ) {
            $success = false;
        }

        // 3. Write Vary header rules to uploads parent directory .htaccess
        $uploadsParent   = dirname( $this->pathResolver->getUploadsDir() );
        $contentHtaccess = trailingslashit( $uploadsParent ) . '.htaccess';
        if ( ! $this->addRulesToFile( $contentHtaccess, $this->generator->getVaryHeaderRules() ) ) {
            $success = false;
        }

        return $success;
    }

    public function deactivate(): bool {
        $uploadsParent = dirname( $this->pathResolver->getUploadsDir() );

        $files = [
            $this->pathResolver->getUploadsDir() . '.htaccess',
            $this->pathResolver->getOutputDir() . '.htaccess',
            trailingslashit( $uploadsParent ) . '.htaccess',
        ];

        foreach ( $files as $file ) {
            $this->removeRulesFromFile( $file );
        }

        return true;
    }

    public function isActive(): bool {
        global $wp_filesystem;

        $htaccess = $this->pathResolver->getUploadsDir() . '.htaccess';

        if ( ! $this->initFilesystem() || ! $wp_filesystem->exists( $htaccess ) ) {
            return false;
        }

        $content = $wp_filesystem->get_contents( $htaccess );
        return false !== $content && false !== strpos( $content, '# BEGIN Jeex WebP' );
    }

    /**
     * Get the rewrite rules generator for display purposes.
     */
    public function getGenerator(): RewriteRulesGenerator {
        return $this->generator;
    }

    private function addRulesToFile( string $filepath, string $rules ): bool {
        global $wp_filesystem;

        if ( ! $this->initFilesystem() ) {
            return false;
        }

        $content = '';

        if ( $wp_filesystem->exists( $filepath ) ) {
            $content = $wp_filesystem->get_contents( $filepath );
            if ( false === $content ) {
                return false;
            }

            // Remove existing rules first.
            $content = $this->stripExistingRules( $content );
        }

        $content = trim( $rules ) . "\n\n" . trim( $content );

        return $wp_filesystem->put_contents( $filepath, trim( $content ) . "\n", FS_CHMOD_FILE );
    }

    private function removeRulesFromFile( string $filepath ): bool {
        global $wp_filesystem;

        if ( ! $this->initFilesystem() ) {
            return false;
        }

        if ( ! $wp_filesystem->exists( $filepath ) ) {
            return true;
        }

        $content = $wp_filesystem->get_contents( $filepath );
        if ( false === $content ) {
            return false;
        }

        $cleaned = $this->stripExistingRules( $content );
        $cleaned = trim( $cleaned );

        if ( empty( $cleaned ) ) {
            wp_delete_file( $filepath );
            return true;
        }

        return $wp_filesystem->put_contents( $filepath, $cleaned . "\n", FS_CHMOD_FILE );
    }

    private function stripExistingRules( string $content ): string {
        $pattern = '/[\r\n]*# BEGIN Jeex WebP.*?# END Jeex WebP[\r\n]*/s';
        return preg_replace( $pattern, "\n", $content );
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
