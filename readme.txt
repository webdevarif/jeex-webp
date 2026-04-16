=== Jeex WebP ===
Contributors: webdevarif
Tags: webp, image optimization, performance, compression, convert
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Convert images to WebP format for faster page loading. Automatic conversion on upload with bulk converter.

== Description ==

Jeex WebP automatically converts your JPEG, PNG, and GIF images to WebP format, reducing file sizes by 25-35% with no visible quality loss.

**Key Features:**

* **Automatic conversion** - New images are converted on upload
* **Bulk converter** - Convert all existing images with a progress bar
* **Smart serving** - Automatically serves WebP to supported browsers via .htaccess or PHP passthru
* **Imagick & GD support** - Works with either PHP image library
* **Zero visual change** - Original images are preserved, WebP served only when smaller
* **EXIF handling** - Fixes image orientation and optionally preserves metadata
* **Background conversion** - Optional WP Cron for hands-free conversion
* **Clean & lightweight** - No external API calls, everything runs on your server

**How It Works:**

1. Converts your images to WebP format (stored separately, originals untouched)
2. Serves the WebP version to browsers that support it
3. Falls back to original for browsers without WebP support
4. Only serves WebP if it's actually smaller than the original

**Server Requirements:**

* PHP 7.4 or higher
* Imagick or GD PHP extension with WebP support
* Apache with mod_rewrite (recommended) or any server with PHP

== Installation ==

1. Upload the `jeex-webp` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Tools > Jeex WebP to configure settings
4. Click "Start Bulk Conversion" to convert existing images

== Frequently Asked Questions ==

= Does this change my original images? =

No. Your original images are never modified. WebP versions are stored in a separate directory (`wp-content/uploads/jeex-webp/`).

= What happens if I deactivate the plugin? =

The .htaccess rules are removed and your site goes back to serving original images. The converted WebP files remain in the cache directory until you delete them.

= Does this work with Nginx? =

Yes! Use the "PHP Passthru" serving mode in settings, or add the provided Nginx configuration to your server block.

= What about browsers that don't support WebP? =

They automatically receive the original JPEG/PNG/GIF. The plugin checks the browser's Accept header before serving WebP.

= Can I control the conversion quality? =

Yes. The settings page has a quality slider (50-100%). We recommend 75-85% for the best balance.

== Screenshots ==

1. Dashboard with conversion stats and bulk converter
2. Settings page with quality slider and serving options
3. Server status and Nginx configuration

== Changelog ==

= 1.0.0 =
* Initial release
* WebP conversion with Imagick and GD support
* Bulk converter with AJAX progress bar
* Auto-convert on upload
* .htaccess and PHP passthru serving
* Background WP Cron conversion
* EXIF orientation correction
* PNG transparency support

== Upgrade Notice ==

= 1.0.0 =
Initial release of Jeex WebP image converter.
