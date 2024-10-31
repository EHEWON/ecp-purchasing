<?php

/**
 * php artisan queue:work  --tries=1
 */

namespace App\Modules\Admin\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;

class ExampleListener implements ShouldQueue {

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
        
    }

}
