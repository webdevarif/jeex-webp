<?php

namespace JeexWebp\Serving;

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
        $exts      = [ 'jpe?g', 'png', 'gif' ];

        $rules = "# BEGIN Jeex WebP\n<IfModule mod_rewrite.c>\n  RewriteEngine On\n\n";
        $rules .= "  # Skip if ?original query string is present\n";
        $rules .= "  RewriteCond %{QUERY_STRING} original$\n";
        $rules .= "  RewriteCond %{REQUEST_FILENAME} -f\n";
        $rules .= "  RewriteRule . - [L]\n\n";

        // AVIF rules first (browser preference: AVIF > WebP)
        foreach ( $exts as $ext ) {
            $extClean = str_replace( '?', '', $ext ); // For file check: jpeg not jpe?g
            $rules .= "  # Serve AVIF for {$ext}\n";
            $rules .= "  RewriteCond %{HTTP_ACCEPT} image/avif\n";
            $rules .= "  RewriteCond %{DOCUMENT_ROOT}/{$outputDir}/\$1.\$2.avif -f\n";
            $rules .= "  RewriteRule ^(.*)\\.({$ext})\$ /{$outputDir}/\$1.\$2.avif [NC,T=image/avif,L]\n\n";
        }

        // WebP rules (fallback)
        foreach ( $exts as $ext ) {
            $rules .= "  # Serve WebP for {$ext}\n";
            $rules .= "  RewriteCond %{HTTP_ACCEPT} image/webp\n";
            $rules .= "  RewriteCond %{DOCUMENT_ROOT}/{$outputDir}/\$1.\$2.webp -f\n";
            $rules .= "  RewriteRule ^(.*)\\.({$ext})\$ /{$outputDir}/\$1.\$2.webp [NC,T=image/webp,L]\n\n";
        }

        $rules .= "</IfModule>\n# END Jeex WebP";

        return $rules;
    }

    /**
     * Generate rules for the output directory (MIME types + caching).
     */
    public function getOutputDirRules(): string {
        $rules = <<<HTACCESS
# BEGIN Jeex WebP
AddType image/webp .webp
AddType image/avif .avif

<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/webp "access plus 1 year"
  ExpiresByType image/avif "access plus 1 year"
</IfModule>

<IfModule mod_headers.c>
  Header set Cache-Control "public, max-age=31536000"
</IfModule>
# END Jeex WebP
HTACCESS;

        return $rules;
    }

    /**
     * Generate Vary header rules for wp-content directory.
     */
    public function getVaryHeaderRules(): string {
        $rules = <<<HTACCESS
# BEGIN Jeex WebP
<IfModule mod_headers.c>
  <FilesMatch "\.(jpe?g|png|gif)$">
    Header append Vary "Accept"
  </FilesMatch>
</IfModule>
# END Jeex WebP
HTACCESS;

        return $rules;
    }

    /**
     * Generate Nginx configuration instructions.
     */
    public function getNginxConfig(): string {
        $outputDir = $this->getRelativeOutputPath();

        return <<<NGINX
# Jeex WebP - Nginx Configuration
# Add this to your server block:

map \$http_accept \$img_suffix {
    default "";
    "~*avif" ".avif";
    "~*webp" ".webp";
}

location ~* \.(jpe?g|png|gif)$ {
    set \$img_file \$document_root/{$outputDir}\$uri\$img_suffix;

    if (-f \$img_file) {
        rewrite ^(.*)$ /{$outputDir}\$uri\$img_suffix break;
    }

    add_header Vary "Accept";
    expires 1y;
    add_header Cache-Control "public";
}
NGINX;
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

        // Fallback
        return 'wp-content/uploads-webpc';
    }
}
