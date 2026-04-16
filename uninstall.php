<?php
/**
 * Uninstall Jeex WebP
 *
 * Removes all plugin data when uninstalled via WordPress admin.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Initialize WP_Filesystem.
require_once ABSPATH . 'wp-admin/includes/file.php';
WP_Filesystem();
global $wp_filesystem;

// Delete all plugin options
$options = [
    'jeex_webp_quality',
    'jeex_webp_method',
    'jeex_webp_output_format',
    'jeex_webp_auto_convert',
    'jeex_webp_only_smaller',
    'jeex_webp_keep_metadata',
    'jeex_webp_serving_mode',
    'jeex_webp_directories',
    'jeex_webp_exclude_dirs',
    'jeex_webp_conversion_limit',
    'jeex_webp_max_resolution',
    'jeex_webp_cron_enabled',
    'jeex_webp_cron_batch_size',
];

foreach ( $options as $option ) {
    delete_option( $option );
}

// Delete transients
delete_transient( 'jeex_webp_monthly_count' );

// Unschedule cron
wp_clear_scheduled_hook( 'jeex_webp_cron_convert' );

// Remove passthru file
$passthruFile = WP_CONTENT_DIR . '/jeex-webp-passthru.php';
if ( file_exists( $passthruFile ) ) {
    wp_delete_file( $passthruFile );
}

// Remove .htaccess rules.
$htaccessFiles = array(
    WP_CONTENT_DIR . '/uploads/.htaccess',
    WP_CONTENT_DIR . '/uploads-webpc/.htaccess',
    WP_CONTENT_DIR . '/.htaccess',
);

foreach ( $htaccessFiles as $file ) {
    if ( ! $wp_filesystem instanceof \WP_Filesystem_Base || ! $wp_filesystem->exists( $file ) ) {
        continue;
    }

    $content = $wp_filesystem->get_contents( $file );
    if ( false === $content ) {
        continue;
    }

    $cleaned = preg_replace( '/[\r\n]*# BEGIN Jeex WebP.*?# END Jeex WebP[\r\n]*/s', "\n", $content );
    $cleaned = trim( $cleaned );

    if ( empty( $cleaned ) ) {
        wp_delete_file( $file );
    } else {
        $wp_filesystem->put_contents( $file, $cleaned . "\n", FS_CHMOD_FILE );
    }
}

// Optionally remove converted files directory
// Uncomment if you want to delete all WebP files on uninstall:
// $outputDir = WP_CONTENT_DIR . '/uploads-webpc';
// if ( is_dir( $outputDir ) ) {
//     $iterator = new RecursiveIteratorIterator(
//         new RecursiveDirectoryIterator( $outputDir, RecursiveDirectoryIterator::SKIP_DOTS ),
//         RecursiveIteratorIterator::CHILD_FIRST
//     );
//     foreach ( $iterator as $item ) {
//         if ( $item->isFile() ) {
//             wp_delete_file( $item->getPathname() );
//         } elseif ( $item->isDir() ) {
//             $wp_filesystem->rmdir( $item->getPathname() );
//         }
//     }
//     $wp_filesystem->rmdir( $outputDir );
// }
