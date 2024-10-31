<?php

/**
 * php artisan queue:work  --tries=1
 */

namespace App\Modules\Admin\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminLoginLogListener implements ShouldQueue{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Handle the event.
     *
     * @param  ExampleEvent $event
     * @return void
     */
    public function handle($event) {

        $obj = $event->object;
        $token = $obj['access_token'];
        Log::info($token);

    }

}
