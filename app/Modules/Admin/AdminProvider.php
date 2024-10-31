<?php

namespace App\Modules\Admin;

use Illuminate\Support\ServiceProvider;

class AdminProvider extends ServiceProvider {

    public function register() {
        app()->singleton('app-admin', function () {
            return app()->make('App\Modules\Admin\AdminModule');
        });
    }

    public function boot() {

        app()->configure('admin');
        $events = app('events');
        $listen = $this->getListen();
        $this->listen = is_array($listen) ? $listen : [];
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }
    }

    public function getListen() {
        // TODO: Implement getListen() method.
        return [
        ];
    }

}
