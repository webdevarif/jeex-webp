<?php

namespace JeexWebp\Serving;

defined( 'ABSPATH' ) || exit;

interface ServeStrategy {

    /**
     * Activate this serving strategy (write config files, etc.).
     */
    public function activate(): bool;

    /**
     * Deactivate this serving strategy (remove config files, etc.).
     */
    public function deactivate(): bool;

    /**
     * Check if this strategy is currently working.
     */
    public function isActive(): bool;

    /**
     * Get the strategy name.
     */
    public function getName(): string;
}
