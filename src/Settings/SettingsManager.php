<?php

namespace JeexWebp\Settings;

class SettingsManager {

    private const PREFIX = 'jeex_webp_';

    private const DEFAULTS = [
        'quality'          => 80,
        'method'           => 'auto',
        'output_format'    => 'webp',
        'auto_convert'     => true,
        'only_smaller'     => true,
        'keep_metadata'    => false,
        'serving_mode'     => 'auto',
        'directories'      => [ 'uploads' ],
        'exclude_dirs'     => '',
        'conversion_limit' => -1,
        'max_resolution'   => 0,
        'cron_enabled'     => false,
        'cron_batch_size'  => 10,
    ];

    private array $cache = [];

    /**
     * Get a setting value.
     */
    public function get( string $key, $default = null ) {
        if ( ! isset( self::DEFAULTS[ $key ] ) && null === $default ) {
            return null;
        }

        if ( isset( $this->cache[ $key ] ) ) {
            return $this->cache[ $key ];
        }

        $fallback           = $default ?? self::DEFAULTS[ $key ] ?? null;
        $value              = get_option( self::PREFIX . $key, $fallback );
        $this->cache[ $key ] = $value;

        return $value;
    }

    /**
     * Update a setting value.
     */
    public function set( string $key, $value ): bool {
        $this->cache[ $key ] = $value;
        return update_option( self::PREFIX . $key, $value );
    }

    /**
     * Delete a setting.
     */
    public function delete( string $key ): bool {
        unset( $this->cache[ $key ] );
        return delete_option( self::PREFIX . $key );
    }

    /**
     * Get all settings with their current values.
     */
    public function getAll(): array {
        $settings = [];
        foreach ( self::DEFAULTS as $key => $default ) {
            $settings[ $key ] = $this->get( $key );
        }
        return $settings;
    }

    /**
     * Save multiple settings at once.
     */
    public function saveAll( array $data ): void {
        $validator = new SettingsValidator();

        foreach ( $data as $key => $value ) {
            if ( ! array_key_exists( $key, self::DEFAULTS ) ) {
                continue;
            }
            $sanitized = $validator->sanitize( $key, $value );
            $this->set( $key, $sanitized );
        }
    }

    /**
     * Delete all plugin options (used on uninstall).
     */
    public function deleteAll(): void {
        foreach ( array_keys( self::DEFAULTS ) as $key ) {
            $this->delete( $key );
        }
    }

    /**
     * Get the option prefix.
     */
    public static function getPrefix(): string {
        return self::PREFIX;
    }

    /**
     * Get default values.
     */
    public static function getDefaults(): array {
        return self::DEFAULTS;
    }
}
