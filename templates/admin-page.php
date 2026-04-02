<?php
/**
 * Admin page template for Jeex WebP — Premium Redesign.
 *
 * @var array $settings Current settings.
 * @var array $methods  Available conversion methods.
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="jw-wrap" id="jeex-webp-app">

    <!-- Header -->
    <div class="jw-header">
        <div class="jw-header-left">
            <div class="jw-header-icon">
                <svg viewBox="0 0 24 24"><path d="M21 5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5zm-4.37 10.37l-2.38-2.38L12 15.25 8.62 11.87l-2.37 2.38V5h11.5v10.37z"/></svg>
            </div>
            <div>
                <h1 class="jw-title"><?php esc_html_e( 'Jeex WebP', 'jeex-webp' ); ?></h1>
                <div class="jw-breadcrumb">
                    <a href="<?php echo esc_url( admin_url( 'tools.php' ) ); ?>">Tools</a> &rsaquo; <span>Jeex WebP</span>
                </div>
            </div>
        </div>
        <div class="jw-header-right">
            <span class="jw-version">v<?php echo esc_html( JEEX_WEBP_VERSION ); ?></span>
        </div>
    </div>

    <?php settings_errors( 'jeex_webp' ); ?>

    <!-- Tabs -->
    <div class="jw-tabs">
        <button class="jw-tab jw-tab--active" data-tab="dashboard">
            <span class="jw-tab-icon"><svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg></span>
            <?php esc_html_e( 'Dashboard', 'jeex-webp' ); ?>
        </button>
        <button class="jw-tab" data-tab="settings">
            <span class="jw-tab-icon"><svg viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 00.12-.61l-1.92-3.32a.488.488 0 00-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58a.49.49 0 00-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg></span>
            <?php esc_html_e( 'Settings', 'jeex-webp' ); ?>
        </button>
        <button class="jw-tab" data-tab="advanced">
            <span class="jw-tab-icon"><svg viewBox="0 0 24 24"><path d="M7 14c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm0-4c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm12.71-1.71L17.83 6.4l1.42-1.42-1.42-1.42L16.41 5 14.5 3.09l1.42-1.42-1.42-1.42L13.09 1.67 11.67.25 10.25 1.67l1.42 1.42-7.5 7.5-1.42-1.42L1.34 10.59l1.42 1.42-1.42 1.42 1.42 1.42 1.42-1.42L5.59 14.84l-.71.71 1.41 1.41.71-.71 1.41 1.41-1.41 1.41 1.41 1.41 1.41-1.41.71.71 1.41-1.41-.71-.71 1.41-1.41 1.41 1.41 1.41-1.41-1.41-1.41h.01l7.5-7.5 1.41 1.41 1.41-1.41-1.41-1.41z"/></svg></span>
            <?php esc_html_e( 'Advanced', 'jeex-webp' ); ?>
        </button>
    </div>

    <!-- ==================== Dashboard Tab ==================== -->
    <div class="jw-panel jw-panel--active" data-panel="dashboard">

        <!-- Server Status -->
        <div class="jw-card">
            <h2 class="jw-card__title">
                <span class="jw-card__title-icon jw-card__title-icon--success">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                </span>
                <?php esc_html_e( 'Server Status', 'jeex-webp' ); ?>
            </h2>
            <div class="jw-status-grid">
                <?php foreach ( $methods as $name => $method ) : ?>
                    <div class="jw-status-item">
                        <span class="jw-status-icon <?php echo $method['available'] ? 'jw-status-icon--ok' : 'jw-status-icon--no'; ?>">
                            <?php echo $method['available'] ? '&#10003;' : '&#10005;'; ?>
                        </span>
                        <span class="jw-status-label">
                            <?php echo esc_html( ucfirst( $name ) ); ?>
                            <?php if ( $method['available'] && ! empty( $method['formats'] ) ) : ?>
                                <small><?php echo esc_html( implode( ', ', array_map( 'strtoupper', $method['formats'] ) ) ); ?></small>
                            <?php elseif ( ! $method['available'] ) : ?>
                                <small><?php esc_html_e( 'Not installed', 'jeex-webp' ); ?></small>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endforeach; ?>

                <?php
                $avifFmt = new \JeexWebp\Conversion\Format\AvifFormat();
                $avifOk  = $avifFmt->isAvailable();
                ?>
                <div class="jw-status-item">
                    <span class="jw-status-icon <?php echo $avifOk ? 'jw-status-icon--ok' : 'jw-status-icon--no'; ?>">
                        <?php echo $avifOk ? '&#10003;' : '&#10005;'; ?>
                    </span>
                    <span class="jw-status-label">
                        <?php esc_html_e( 'AVIF Support', 'jeex-webp' ); ?>
                        <small><?php echo $avifOk ? esc_html__( 'Ready', 'jeex-webp' ) : esc_html__( 'Requires PHP 8.1+ with GD AVIF', 'jeex-webp' ); ?></small>
                    </span>
                </div>

                <?php
                $htaccess       = new \JeexWebp\Serving\HtaccessStrategy( new \JeexWebp\Conversion\PathResolver( \JeexWebp\Plugin::getInstance()->getSettings() ) );
                $htaccessActive = $htaccess->isActive();
                ?>
                <div class="jw-status-item">
                    <span class="jw-status-icon <?php echo $htaccessActive ? 'jw-status-icon--ok' : 'jw-status-icon--no'; ?>">
                        <?php echo $htaccessActive ? '&#10003;' : '&#10005;'; ?>
                    </span>
                    <span class="jw-status-label">
                        <?php esc_html_e( '.htaccess Rewrites', 'jeex-webp' ); ?>
                        <small><?php echo $htaccessActive ? esc_html__( 'Active', 'jeex-webp' ) : esc_html__( 'Not configured', 'jeex-webp' ); ?></small>
                    </span>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="jw-stats-row" id="jeex-stats">
            <div class="jw-stat-card" data-tooltip="<?php esc_attr_e( 'Total images in uploads folder', 'jeex-webp' ); ?>">
                <div class="jw-stat-icon jw-stat-icon--total">
                    <svg viewBox="0 0 24 24"><path d="M21 5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5zm-4.37 10.37l-2.38-2.38L12 15.25 8.62 11.87l-2.37 2.38V5h11.5v10.37z"/></svg>
                </div>
                <div class="jw-stat-number" id="stat-total">--</div>
                <div class="jw-stat-label"><?php esc_html_e( 'Total Images', 'jeex-webp' ); ?></div>
            </div>
            <div class="jw-stat-card" data-tooltip="<?php esc_attr_e( 'Successfully converted images', 'jeex-webp' ); ?>">
                <div class="jw-stat-icon jw-stat-icon--converted">
                    <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                </div>
                <div class="jw-stat-number" id="stat-converted">--</div>
                <div class="jw-stat-label"><?php esc_html_e( 'Converted', 'jeex-webp' ); ?></div>
            </div>
            <div class="jw-stat-card" data-tooltip="<?php esc_attr_e( 'Images waiting for conversion', 'jeex-webp' ); ?>">
                <div class="jw-stat-icon jw-stat-icon--remaining">
                    <svg viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                </div>
                <div class="jw-stat-number" id="stat-unconverted">--</div>
                <div class="jw-stat-label"><?php esc_html_e( 'Remaining', 'jeex-webp' ); ?></div>
            </div>
            <div class="jw-stat-card jw-stat-card--highlight" data-tooltip="<?php esc_attr_e( 'Total disk space saved', 'jeex-webp' ); ?>">
                <div class="jw-stat-icon jw-stat-icon--saved">
                    <svg viewBox="0 0 24 24"><path d="M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6 10H6v-2h8v2zm4-4H6V10h12v2z"/></svg>
                </div>
                <div class="jw-stat-number" id="stat-saved">--</div>
                <div class="jw-stat-label"><?php esc_html_e( 'Space Saved', 'jeex-webp' ); ?></div>
            </div>
        </div>

        <!-- Bulk Converter -->
        <div class="jw-card">
            <h2 class="jw-card__title">
                <span class="jw-card__title-icon jw-card__title-icon--primary">
                    <svg viewBox="0 0 24 24"><path d="M19 8l-4 4h3c0 3.31-2.69 6-6 6-1.01 0-1.97-.25-2.8-.7l-1.46 1.46C8.97 19.54 10.43 20 12 20c4.42 0 8-3.58 8-8h3l-4-4zM6 12c0-3.31 2.69-6 6-6 1.01 0 1.97.25 2.8.7l1.46-1.46C15.03 4.46 13.57 4 12 4c-4.42 0-8 3.58-8 8H1l4 4 4-4H6z"/></svg>
                </span>
                <?php esc_html_e( 'Bulk Conversion', 'jeex-webp' ); ?>
            </h2>

            <div class="jw-progress-wrap" id="jeex-progress" style="display:none;">
                <div class="jw-progress-header">
                    <span class="jw-progress-pct" id="jeex-progress-pct">0%</span>
                    <span class="jw-progress-text" id="jeex-progress-text"></span>
                </div>
                <div class="jw-progress-bar">
                    <div class="jw-progress-fill" id="jeex-progress-fill" style="width:0%"></div>
                </div>
                <div class="jw-progress-badges" id="jeex-badges" style="display:none;">
                    <span class="jw-badge jw-badge--success"><span class="jw-badge-dot"></span> <span id="badge-converted">0</span> <?php esc_html_e( 'converted', 'jeex-webp' ); ?></span>
                    <span class="jw-badge jw-badge--warning"><span class="jw-badge-dot"></span> <span id="badge-skipped">0</span> <?php esc_html_e( 'skipped', 'jeex-webp' ); ?></span>
                    <span class="jw-badge jw-badge--danger"><span class="jw-badge-dot"></span> <span id="badge-failed">0</span> <?php esc_html_e( 'failed', 'jeex-webp' ); ?></span>
                </div>
            </div>

            <div class="jw-log" id="jeex-log" style="display:none;"></div>

            <div class="jw-actions">
                <button class="jw-btn jw-btn--primary jw-btn--hero" id="jeex-btn-convert">
                    <svg viewBox="0 0 24 24"><path d="M19 8l-4 4h3c0 3.31-2.69 6-6 6-1.01 0-1.97-.25-2.8-.7l-1.46 1.46C8.97 19.54 10.43 20 12 20c4.42 0 8-3.58 8-8h3l-4-4zM6 12c0-3.31 2.69-6 6-6 1.01 0 1.97.25 2.8.7l1.46-1.46C15.03 4.46 13.57 4 12 4c-4.42 0-8 3.58-8 8H1l4 4 4-4H6z" fill="#fff"/></svg>
                    <span class="jw-btn-text"><?php esc_html_e( 'Start Bulk Conversion', 'jeex-webp' ); ?></span>
                    <span class="jw-spinner"></span>
                </button>
                <button class="jw-btn jw-btn--danger" id="jeex-btn-stop" style="display:none;">
                    <svg viewBox="0 0 24 24"><path d="M6 6h12v12H6z" fill="currentColor"/></svg>
                    <?php esc_html_e( 'Stop', 'jeex-webp' ); ?>
                </button>
            </div>
        </div>

        <!-- Actions -->
        <div class="jw-card">
            <h2 class="jw-card__title">
                <span class="jw-card__title-icon jw-card__title-icon--warning">
                    <svg viewBox="0 0 24 24"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
                </span>
                <?php esc_html_e( 'Quick Actions', 'jeex-webp' ); ?>
            </h2>
            <div class="jw-actions">
                <button class="jw-btn jw-btn--danger" id="jeex-btn-clear-cache">
                    <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" fill="currentColor"/></svg>
                    <?php esc_html_e( 'Clear WebP Cache', 'jeex-webp' ); ?>
                </button>
                <button class="jw-btn jw-btn--secondary" id="jeex-btn-regen-htaccess">
                    <svg viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z" fill="currentColor"/></svg>
                    <?php esc_html_e( 'Regenerate .htaccess', 'jeex-webp' ); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== Settings Tab ==================== -->
    <div class="jw-panel" data-panel="settings">
        <form method="post" class="jw-settings-form">
            <?php wp_nonce_field( 'jeex_webp_settings' ); ?>

            <!-- Output Format -->
            <div class="jw-card">
                <h2 class="jw-card__title">
                    <span class="jw-card__title-icon jw-card__title-icon--primary">
                        <svg viewBox="0 0 24 24"><path d="M21 5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5zm-4.37 10.37l-2.38-2.38L12 15.25 8.62 11.87l-2.37 2.38V5h11.5v10.37z"/></svg>
                    </span>
                    <?php esc_html_e( 'Output Format', 'jeex-webp' ); ?>
                </h2>

                <?php
                $avifFormat    = new \JeexWebp\Conversion\Format\AvifFormat();
                $avifAvailable = $avifFormat->isAvailable();
                ?>
                <div class="jw-format-cards">
                    <label class="jw-format-card <?php echo 'webp' === $settings['output_format'] ? 'jw-format-card--active' : ''; ?>">
                        <input type="radio" name="output_format" value="webp" <?php checked( $settings['output_format'], 'webp' ); ?>>
                        <span class="jw-format-radio"></span>
                        <span class="jw-format-card__name">WebP</span>
                        <span class="jw-format-card__desc"><?php esc_html_e( 'Smaller than JPEG/PNG. All modern browsers supported.', 'jeex-webp' ); ?></span>
                    </label>
                    <label class="jw-format-card <?php echo 'avif' === $settings['output_format'] ? 'jw-format-card--active' : ''; ?> <?php echo ! $avifAvailable ? 'jw-format-card--disabled' : ''; ?>">
                        <input type="radio" name="output_format" value="avif" <?php checked( $settings['output_format'], 'avif' ); ?> <?php disabled( ! $avifAvailable ); ?>>
                        <span class="jw-format-radio"></span>
                        <span class="jw-format-card__name">AVIF</span>
                        <span class="jw-format-card__desc"><?php esc_html_e( '~50% smaller than JPEG. Best compression available.', 'jeex-webp' ); ?></span>
                        <?php if ( ! $avifAvailable ) : ?>
                            <span class="jw-format-card__badge"><?php esc_html_e( 'Not Available', 'jeex-webp' ); ?></span>
                        <?php endif; ?>
                    </label>
                    <label class="jw-format-card <?php echo 'both' === $settings['output_format'] ? 'jw-format-card--active' : ''; ?> <?php echo ! $avifAvailable ? 'jw-format-card--disabled' : ''; ?>">
                        <input type="radio" name="output_format" value="both" <?php checked( $settings['output_format'], 'both' ); ?> <?php disabled( ! $avifAvailable ); ?>>
                        <span class="jw-format-radio"></span>
                        <span class="jw-format-card__name">AVIF + WebP</span>
                        <span class="jw-format-card__desc"><?php esc_html_e( 'AVIF for modern browsers, WebP fallback for older ones.', 'jeex-webp' ); ?></span>
                        <?php if ( ! $avifAvailable ) : ?>
                            <span class="jw-format-card__badge"><?php esc_html_e( 'Not Available', 'jeex-webp' ); ?></span>
                        <?php endif; ?>
                    </label>
                </div>
            </div>

            <!-- Quality & Method -->
            <div class="jw-card">
                <h2 class="jw-card__title">
                    <span class="jw-card__title-icon jw-card__title-icon--info">
                        <svg viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
                    </span>
                    <?php esc_html_e( 'Quality & Method', 'jeex-webp' ); ?>
                </h2>

                <div class="jw-field">
                    <label class="jw-field__label"><?php esc_html_e( 'Conversion Quality', 'jeex-webp' ); ?></label>
                    <div class="jw-quality-slider">
                        <div class="jw-range-wrap">
                            <input type="range" name="quality" min="50" max="100" step="5"
                                   value="<?php echo esc_attr( $settings['quality'] ); ?>"
                                   id="quality-slider" class="jw-range">
                        </div>
                        <div class="jw-quality-labels">
                            <span><?php esc_html_e( 'Smaller File', 'jeex-webp' ); ?></span>
                            <span class="jw-quality-value" id="quality-value"><?php echo esc_html( $settings['quality'] ); ?>%</span>
                            <span><?php esc_html_e( 'Better Quality', 'jeex-webp' ); ?></span>
                        </div>
                    </div>
                    <p class="jw-field__desc"><?php esc_html_e( 'Recommended: 75-85% for best balance of quality and file size.', 'jeex-webp' ); ?></p>
                </div>

                <hr class="jw-divider">

                <div class="jw-field">
                    <label class="jw-field__label"><?php esc_html_e( 'Conversion Method', 'jeex-webp' ); ?></label>
                    <select name="method" class="jw-select">
                        <option value="auto" <?php selected( $settings['method'], 'auto' ); ?>>
                            <?php esc_html_e( 'Auto (Recommended)', 'jeex-webp' ); ?>
                        </option>
                        <?php foreach ( $methods as $name => $method ) : ?>
                            <option value="<?php echo esc_attr( $name ); ?>"
                                    <?php selected( $settings['method'], $name ); ?>
                                    <?php disabled( ! $method['available'] ); ?>>
                                <?php
                                echo esc_html( ucfirst( $name ) );
                                if ( ! $method['available'] ) {
                                    echo ' (' . esc_html__( 'Not Available', 'jeex-webp' ) . ')';
                                }
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Behavior -->
            <div class="jw-card">
                <h2 class="jw-card__title">
                    <span class="jw-card__title-icon jw-card__title-icon--success">
                        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    </span>
                    <?php esc_html_e( 'Behavior', 'jeex-webp' ); ?>
                </h2>

                <label class="jw-toggle">
                    <input type="checkbox" name="auto_convert" value="1" <?php checked( $settings['auto_convert'] ); ?>>
                    <span class="jw-toggle__switch"></span>
                    <span class="jw-toggle__label">
                        <?php esc_html_e( 'Auto-convert on upload', 'jeex-webp' ); ?>
                        <small><?php esc_html_e( 'Automatically convert new images when uploaded to Media Library', 'jeex-webp' ); ?></small>
                    </span>
                </label>

                <label class="jw-toggle">
                    <input type="checkbox" name="only_smaller" value="1" <?php checked( $settings['only_smaller'] ); ?>>
                    <span class="jw-toggle__switch"></span>
                    <span class="jw-toggle__label">
                        <?php esc_html_e( 'Only serve if smaller', 'jeex-webp' ); ?>
                        <small><?php esc_html_e( 'Skip serving converted file if it is larger than the original', 'jeex-webp' ); ?></small>
                    </span>
                </label>

                <label class="jw-toggle">
                    <input type="checkbox" name="keep_metadata" value="1" <?php checked( $settings['keep_metadata'] ); ?>>
                    <span class="jw-toggle__switch"></span>
                    <span class="jw-toggle__label">
                        <?php esc_html_e( 'Keep image metadata', 'jeex-webp' ); ?>
                        <small><?php esc_html_e( 'Preserve EXIF, IPTC and other metadata in converted images', 'jeex-webp' ); ?></small>
                    </span>
                </label>
            </div>

            <!-- Serving -->
            <div class="jw-card">
                <h2 class="jw-card__title">
                    <span class="jw-card__title-icon jw-card__title-icon--warning">
                        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                    </span>
                    <?php esc_html_e( 'Serving', 'jeex-webp' ); ?>
                </h2>

                <div class="jw-field">
                    <label class="jw-field__label"><?php esc_html_e( 'Image Serving Mode', 'jeex-webp' ); ?></label>
                    <select name="serving_mode" class="jw-select">
                        <option value="auto" <?php selected( $settings['serving_mode'], 'auto' ); ?>>
                            <?php esc_html_e( 'Auto (Recommended)', 'jeex-webp' ); ?>
                        </option>
                        <option value="htaccess" <?php selected( $settings['serving_mode'], 'htaccess' ); ?>>
                            <?php esc_html_e( '.htaccess (Apache)', 'jeex-webp' ); ?>
                        </option>
                        <option value="passthru" <?php selected( $settings['serving_mode'], 'passthru' ); ?>>
                            <?php esc_html_e( 'PHP Passthru (Nginx / Fallback)', 'jeex-webp' ); ?>
                        </option>
                    </select>
                </div>

                <hr class="jw-divider">

                <div class="jw-field">
                    <label class="jw-field__label"><?php esc_html_e( 'Supported Directories', 'jeex-webp' ); ?></label>
                    <label class="jw-toggle">
                        <input type="checkbox" name="directories[]" value="uploads"
                               <?php checked( in_array( 'uploads', $settings['directories'] ?? [], true ) ); ?>>
                        <span class="jw-toggle__switch"></span>
                        <span class="jw-toggle__label">/uploads</span>
                    </label>
                    <label class="jw-toggle">
                        <input type="checkbox" name="directories[]" value="themes"
                               <?php checked( in_array( 'themes', $settings['directories'] ?? [], true ) ); ?>>
                        <span class="jw-toggle__switch"></span>
                        <span class="jw-toggle__label">/themes</span>
                    </label>
                </div>

                <div class="jw-field">
                    <label class="jw-field__label"><?php esc_html_e( 'Excluded Directories', 'jeex-webp' ); ?></label>
                    <input type="text" name="exclude_dirs" class="jw-input"
                           value="<?php echo esc_attr( $settings['exclude_dirs'] ); ?>"
                           placeholder="cache, backup, temp">
                    <p class="jw-field__desc"><?php esc_html_e( 'Comma-separated directory names to skip during conversion.', 'jeex-webp' ); ?></p>
                </div>
            </div>

            <button type="submit" name="jeex_webp_save_settings" class="jw-btn jw-btn--primary jw-btn--hero">
                <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" fill="#fff"/></svg>
                <span class="jw-btn-text"><?php esc_html_e( 'Save Settings', 'jeex-webp' ); ?></span>
            </button>
        </form>
    </div>

    <!-- ==================== Advanced Tab ==================== -->
    <div class="jw-panel" data-panel="advanced">
        <div class="jw-card">
            <h2 class="jw-card__title">
                <span class="jw-card__title-icon jw-card__title-icon--info">
                    <svg viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                </span>
                <?php esc_html_e( 'Background Conversion', 'jeex-webp' ); ?>
            </h2>

            <form method="post">
                <?php wp_nonce_field( 'jeex_webp_settings' ); ?>

                <label class="jw-toggle">
                    <input type="checkbox" name="cron_enabled" value="1" <?php checked( $settings['cron_enabled'] ); ?>>
                    <span class="jw-toggle__switch"></span>
                    <span class="jw-toggle__label">
                        <?php esc_html_e( 'Enable background conversion', 'jeex-webp' ); ?>
                        <small><?php esc_html_e( 'Automatically converts images in the background every 5 minutes via WP Cron', 'jeex-webp' ); ?></small>
                    </span>
                </label>

                <hr class="jw-divider">

                <div class="jw-field">
                    <label class="jw-field__label"><?php esc_html_e( 'Batch Size per Cron Run', 'jeex-webp' ); ?></label>
                    <input type="number" name="cron_batch_size" class="jw-input jw-input--small"
                           value="<?php echo esc_attr( $settings['cron_batch_size'] ); ?>"
                           min="1" max="50">
                    <p class="jw-field__desc"><?php esc_html_e( 'Number of images to convert per cron execution (1-50).', 'jeex-webp' ); ?></p>
                </div>

                <button type="submit" name="jeex_webp_save_settings" class="jw-btn jw-btn--primary">
                    <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" fill="#fff"/></svg>
                    <span class="jw-btn-text"><?php esc_html_e( 'Save Settings', 'jeex-webp' ); ?></span>
                </button>
            </form>
        </div>

        <!-- Nginx Config -->
        <div class="jw-card">
            <h2 class="jw-card__title">
                <span class="jw-card__title-icon jw-card__title-icon--warning">
                    <svg viewBox="0 0 24 24"><path d="M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4z"/></svg>
                </span>
                <?php esc_html_e( 'Nginx Configuration', 'jeex-webp' ); ?>
            </h2>
            <p class="jw-field__desc" style="margin-top:0;margin-bottom:14px;">
                <?php esc_html_e( 'If your server uses Nginx, add this configuration to your server block:', 'jeex-webp' ); ?>
            </p>
            <?php $generator = new \JeexWebp\Serving\RewriteRulesGenerator( \JeexWebp\Plugin::getInstance()->getPathResolver() ); ?>
            <pre class="jw-code"><?php echo esc_html( $generator->getNginxConfig() ); ?></pre>
        </div>
    </div>

</div>
