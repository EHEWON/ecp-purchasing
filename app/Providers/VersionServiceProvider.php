<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class VersionServiceProvider extends ServiceProvider {

    public function boot() {
        
    }

    public function register() {
        $request = Request::capture();
        $version = $request->header("version") ?
                $request->header("version") :
                $request->input('version');
        if ($version) {

            $folders = __DIR__ . '/../Routes/';




            $file = __DIR__ . '/../Routes/' . $version . '/' . 'router.php';
            if (file_exists($file)) {
                app()->router->group([], function ($router) use ($file) {
                    require $file;
                });
            }
        }
    }

}
