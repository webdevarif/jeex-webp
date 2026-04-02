<?php
/**
 * Plugin Name: Jeex WebP
 * Plugin URI:  https://jeex.dev/webp
 * Description: Convert images to WebP for faster loading. Automatic conversion on upload + bulk converter with Imagick & GD support.
 * Version:     1.0.0
 * Author:      Jeex
 * Author URI:  https://jeex.dev
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: jeex-webp
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.7
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'JEEX_WEBP_VERSION', '1.0.0' );
define( 'JEEX_WEBP_FILE', __FILE__ );
define( 'JEEX_WEBP_DIR', plugin_dir_path( __FILE__ ) );
define( 'JEEX_WEBP_URL', plugin_dir_url( __FILE__ ) );
define( 'JEEX_WEBP_BASENAME', plugin_basename( __FILE__ ) );

require_once __DIR__ . '/vendor/autoload.php';

JeexWebp\Plugin::init( __FILE__ );
