<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RoutesServiceProvider extends ServiceProvider {

    public function register() {
        $this->registerAdminRoutes();
        $this->registerFrontendRoutes();
    }

    public function boot() {
        
    }

    public function registerAdminRoutes() {
        app()->router->group([
            'namespace' => 'App\Modules\Admin\Controller',
                ], function ($router) {
            require __DIR__ . '/../Routes/admin.php';
            require __DIR__ . '/../Routes/project.php';
        });
    }

    public function registerFrontendRoutes() {
        app()->router->group([
            'namespace' => 'App\Modules\Frontend\Controller',
                ], function ($router) {
            require __DIR__ . '/../Routes/frontend.php';
        });
    }

}
