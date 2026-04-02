<?php

namespace JeexWebp\Serving;

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

        // 3. Write Vary header rules to wp-content/.htaccess
        $contentHtaccess = trailingslashit( WP_CONTENT_DIR ) . '.htaccess';
        if ( ! $this->addRulesToFile( $contentHtaccess, $this->generator->getVaryHeaderRules() ) ) {
            $success = false;
        }

        return $success;
    }

    public function deactivate(): bool {
        $files = [
            $this->pathResolver->getUploadsDir() . '.htaccess',
            $this->pathResolver->getOutputDir() . '.htaccess',
            trailingslashit( WP_CONTENT_DIR ) . '.htaccess',
        ];

        foreach ( $files as $file ) {
            $this->removeRulesFromFile( $file );
        }

        return true;
    }

    public function isActive(): bool {
        $htaccess = $this->pathResolver->getUploadsDir() . '.htaccess';

        if ( ! file_exists( $htaccess ) ) {
            return false;
        }

        $content = @file_get_contents( $htaccess );
        return false !== $content && false !== strpos( $content, '# BEGIN Jeex WebP' );
    }

    /**
     * Get the rewrite rules generator for display purposes.
     */
    public function getGenerator(): RewriteRulesGenerator {
        return $this->generator;
    }

    private function addRulesToFile( string $filepath, string $rules ): bool {
        $content = '';

        if ( file_exists( $filepath ) ) {
            $content = @file_get_contents( $filepath );
            if ( false === $content ) {
                return false;
            }

            // Remove existing rules first
            $content = $this->stripExistingRules( $content );
        }

        $content = trim( $rules ) . "\n\n" . trim( $content );

        return false !== @file_put_contents( $filepath, trim( $content ) . "\n" );
    }

    private function removeRulesFromFile( string $filepath ): bool {
        if ( ! file_exists( $filepath ) ) {
            return true;
        }

        $content = @file_get_contents( $filepath );
        if ( false === $content ) {
            return false;
        }

        $cleaned = $this->stripExistingRules( $content );
        $cleaned = trim( $cleaned );

        if ( empty( $cleaned ) ) {
            @unlink( $filepath );
            return true;
        }

        return false !== @file_put_contents( $filepath, $cleaned . "\n" );
    }

    private function stripExistingRules( string $content ): string {
        $pattern = '/[\r\n]*# BEGIN Jeex WebP.*?# END Jeex WebP[\r\n]*/s';
        return preg_replace( $pattern, "\n", $content );
    }
}
