<?php

namespace App\Modules\Frontend;

use Illuminate\Support\ServiceProvider;

class FrontendProvider extends ServiceProvider {

    public function register() {
        app()->singleton('app-api', function () {
            return app()->make('App\Modules\Frontend\FrontendModule');
        });
    }

    public function boot() {
        
    }

    public function getListen() {
        return [
        ];
    }

}
