<?php

namespace JeexWebp\Serving;

defined( 'ABSPATH' ) || exit;

use JeexWebp\Conversion\PathResolver;

class RewriteRulesGenerator {

    private PathResolver $pathResolver;

    public function __construct( PathResolver $pathResolver ) {
        $this->pathResolver = $pathResolver;
    }

    /**
     * Generate mod_rewrite rules for the uploads directory.
     * Supports AVIF (preferred), WebP, and both.
     */
    public function getUploadsRewriteRules(): string {
        $outputDir = $this->getRelativeOutputPath();
        $exts      = array( 'jpe?g', 'png', 'gif' );

        $rules = "# BEGIN Jeex WebP\n<IfModule mod_rewrite.c>\n  RewriteEngine On\n\n";
        $rules .= "  # Skip if ?original query string is present\n";
        $rules .= "  RewriteCond %{QUERY_STRING} original$\n";
        $rules .= "  RewriteCond %{REQUEST_FILENAME} -f\n";
        $rules .= "  RewriteRule . - [L]\n\n";

        foreach ( $exts as $ext ) {
            $rules .= "  # Serve AVIF for " . $ext . "\n";
            $rules .= "  RewriteCond %{HTTP_ACCEPT} image/avif\n";
            $rules .= "  RewriteCond %{DOCUMENT_ROOT}/" . $outputDir . '/$1.$2.avif -f' . "\n";
            $rules .= "  RewriteRule ^(.*)\\.(" . $ext . ')$ /' . $outputDir . '/$1.$2.avif [NC,T=image/avif,L]' . "\n\n";
        }

        foreach ( $exts as $ext ) {
            $rules .= "  # Serve WebP for " . $ext . "\n";
            $rules .= "  RewriteCond %{HTTP_ACCEPT} image/webp\n";
            $rules .= "  RewriteCond %{DOCUMENT_ROOT}/" . $outputDir . '/$1.$2.webp -f' . "\n";
            $rules .= "  RewriteRule ^(.*)\\.(" . $ext . ')$ /' . $outputDir . '/$1.$2.webp [NC,T=image/webp,L]' . "\n\n";
        }

        $rules .= "</IfModule>\n# END Jeex WebP";

        return $rules;
    }

    /**
     * Generate rules for the output directory (MIME types + caching).
     */
    public function getOutputDirRules(): string {
        $rules  = "# BEGIN Jeex WebP\n";
        $rules .= "AddType image/webp .webp\n";
        $rules .= "AddType image/avif .avif\n";
        $rules .= "\n";
        $rules .= "<IfModule mod_expires.c>\n";
        $rules .= "  ExpiresActive On\n";
        $rules .= '  ExpiresByType image/webp "access plus 1 year"' . "\n";
        $rules .= '  ExpiresByType image/avif "access plus 1 year"' . "\n";
        $rules .= "</IfModule>\n";
        $rules .= "\n";
        $rules .= "<IfModule mod_headers.c>\n";
        $rules .= '  Header set Cache-Control "public, max-age=31536000"' . "\n";
        $rules .= "</IfModule>\n";
        $rules .= "# END Jeex WebP";

        return $rules;
    }

    /**
     * Generate Vary header rules for wp-content directory.
     */
    public function getVaryHeaderRules(): string {
        $rules  = "# BEGIN Jeex WebP\n";
        $rules .= "<IfModule mod_headers.c>\n";
        $rules .= '  <FilesMatch "\.(jpe?g|png|gif)$">' . "\n";
        $rules .= '    Header append Vary "Accept"' . "\n";
        $rules .= "  </FilesMatch>\n";
        $rules .= "</IfModule>\n";
        $rules .= "# END Jeex WebP";

        return $rules;
    }

    /**
     * Generate Nginx configuration instructions.
     */
    public function getNginxConfig(): string {
        $outputDir = $this->getRelativeOutputPath();

        $config  = "# Jeex WebP - Nginx Configuration\n";
        $config .= "# Add this to your server block:\n\n";
        $config .= 'map $http_accept $img_suffix {' . "\n";
        $config .= '    default "";' . "\n";
        $config .= '    "~*avif" ".avif";' . "\n";
        $config .= '    "~*webp" ".webp";' . "\n";
        $config .= "}\n\n";
        $config .= 'location ~* \.(jpe?g|png|gif)$ {' . "\n";
        $config .= '    set $img_file $document_root/' . $outputDir . '$uri$img_suffix;' . "\n";
        $config .= "\n";
        $config .= '    if (-f $img_file) {' . "\n";
        $config .= '        rewrite ^(.*)$ /' . $outputDir . '$uri$img_suffix break;' . "\n";
        $config .= "    }\n\n";
        $config .= '    add_header Vary "Accept";' . "\n";
        $config .= "    expires 1y;\n";
        $config .= '    add_header Cache-Control "public";' . "\n";
        $config .= "}";

        return $config;
    }

    /**
     * Get the output directory path relative to document root.
     */
    private function getRelativeOutputPath(): string {
        $outputDir = wp_normalize_path( $this->pathResolver->getOutputDir() );
        $docRoot   = wp_normalize_path( untrailingslashit( ABSPATH ) );

        if ( strpos( $outputDir, $docRoot ) === 0 ) {
            return ltrim( substr( $outputDir, strlen( $docRoot ) ), '/' );
        }

        return 'wp-content/uploads-webpc';
    }
}
