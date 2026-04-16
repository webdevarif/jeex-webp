<?php

namespace JeexWebp;

defined( 'ABSPATH' ) || exit;

use JeexWebp\Conversion\PathResolver;
use JeexWebp\Serving\HtaccessStrategy;
use JeexWebp\Settings\SettingsManager;

class Activator {

    public static function activate(): void {
        $settings     = new SettingsManager();
        $pathResolver = new PathResolver( $settings );

        // Create output directory
        $pathResolver->ensureOutputDir();

        // Add index.php to output directory for security
        $indexFile = $pathResolver->getOutputDir() . 'index.php';
        if ( ! file_exists( $indexFile ) ) {
            global $wp_filesystem;

            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();

            if ( $wp_filesystem instanceof \WP_Filesystem_Base ) {
                $wp_filesystem->put_contents( $indexFile, '<?php // Silence is golden.', FS_CHMOD_FILE );
            }
        }

        // Set up .htaccess rules
        $htaccess = new HtaccessStrategy( $pathResolver );
        $htaccess->activate();

        // Set default options if not already set
        $defaults = SettingsManager::getDefaults();
        foreach ( $defaults as $key => $value ) {
            if ( false === get_option( SettingsManager::getPrefix() . $key ) ) {
                update_option( SettingsManager::getPrefix() . $key, $value );
            }
        }
    }
}
