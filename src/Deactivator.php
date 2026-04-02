<?php

namespace JeexWebp;

use JeexWebp\Conversion\PathResolver;
use JeexWebp\Hooks\CronHook;
use JeexWebp\Serving\HtaccessStrategy;
use JeexWebp\Serving\PassthruStrategy;
use JeexWebp\Settings\SettingsManager;

class Deactivator {

    public static function deactivate(): void {
        $settings     = new SettingsManager();
        $pathResolver = new PathResolver( $settings );

        // Remove .htaccess rules
        $htaccess = new HtaccessStrategy( $pathResolver );
        $htaccess->deactivate();

        // Remove passthru file
        $passthru = new PassthruStrategy( $pathResolver );
        $passthru->deactivate();

        // Unschedule cron
        CronHook::unschedule();

        // Clean up transients
        delete_transient( 'jeex_webp_monthly_count' );
    }
}
