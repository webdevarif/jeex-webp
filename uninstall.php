<?php
/**
 * Uninstall Jeex WebP
 *
 * Removes all plugin data when uninstalled via WordPress admin.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

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
    @unlink( $passthruFile );
}

// Remove .htaccess rules
$htaccessFiles = [
    WP_CONTENT_DIR . '/uploads/.htaccess',
    WP_CONTENT_DIR . '/uploads-webpc/.htaccess',
    WP_CONTENT_DIR . '/.htaccess',
];

foreach ( $htaccessFiles as $file ) {
    if ( ! file_exists( $file ) ) {
        continue;
    }

    $content = @file_get_contents( $file );
    if ( false === $content ) {
        continue;
    }

    $cleaned = preg_replace( '/[\r\n]*# BEGIN Jeex WebP.*?# END Jeex WebP[\r\n]*/s', "\n", $content );
    $cleaned = trim( $cleaned );

    if ( empty( $cleaned ) ) {
        @unlink( $file );
    } else {
        @file_put_contents( $file, $cleaned . "\n" );
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
//             @unlink( $item->getPathname() );
//         } elseif ( $item->isDir() ) {
//             @rmdir( $item->getPathname() );
//         }
//     }
//     @rmdir( $outputDir );
// }
