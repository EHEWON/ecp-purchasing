<?php
/**
 * php artisan queue:work --daemon --quiet --queue=default --delay=3 --sleep=3 --tries=1
 * php artisan queue:work  --tries=1
 */
namespace App\Modules\Admin\Events;

use App\Events\Event;


class ChangeSuccessEvent extends Event {


    public $response;
    public $request;

    public function __construct($response, $request){
        $this->response = $response;
        $this->request = $request;
    }
}