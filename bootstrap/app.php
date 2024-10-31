<?php

require_once __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../app/helpers.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
  |--------------------------------------------------------------------------
  | Create The Application
  |--------------------------------------------------------------------------
  |
  | Here we will load the environment and create the application instance
  | that serves as the central piece of this framework. We'll use this
  | application as an "IoC" container and router for this framework.
  |
 */

$app = new Laravel\Lumen\Application(
        dirname(__DIR__)
);

$app->withFacades();

$app->withEloquent();

/*
  |--------------------------------------------------------------------------
  | Register Container Bindings
  |--------------------------------------------------------------------------
  |
  | Now we will register a few bindings in the service container. We will
  | register the exception handler and the console kernel. You may add
  | your own bindings here if you like or you can make another file.
  |
 */

$app->singleton(
        Illuminate\Contracts\Debug\ExceptionHandler::class, App\Exceptions\Handler::class
);

$app->singleton(
        Illuminate\Contracts\Console\Kernel::class, App\Console\Kernel::class
);

/*
  |--------------------------------------------------------------------------
  | Register Config Files
  |--------------------------------------------------------------------------
  |
  | Now we will register the "app" configuration file. If the file exists in
  | your configuration directory it will be loaded; otherwise, we'll load
  | the default version. You may register other files below as needed.
  |
 */

$app->configure('app');
$app->configure('mail');
$app->configure('upload');
$app->configure('forget');
$app->configure('elasticsearch');
$app->configure('queue');
$app->configure('permission');
$app->configure('login');
/*
  |--------------------------------------------------------------------------
  | Register Middleware
  |--------------------------------------------------------------------------
  |
  | Next, we will register the middleware with the application. These can
  | be global middleware that run before and after each request into a
  | route or middleware that'll be assigned to some specific routes.
  |
 */


$app->middleware(
        [
            //后置的报文响应中间件
            //允许跨域中间件
            App\Middleware\CORSMiddleware::class,
            //重组报文中间件
            App\Middleware\Response::class,
            App\Middleware\Lang::class,
        ]
);
$app->alias('cache', \Illuminate\Cache\CacheManager::class);  // if you don't have this already
$app->register(Spatie\Permission\PermissionServiceProvider::class);
$app->routeMiddleware([
    'permission' => App\Middleware\Permission::class,
    'admin_log' => App\Modules\Admin\Middleware\AdminLog::class,
]);
/*
  |--------------------------------------------------------------------------
  | Register Service Providers
  |--------------------------------------------------------------------------
  |
  | Here we will register all of the application's service providers which
  | are used to bind services into the container. Service providers are
  | totally optional, so you are not required to uncomment this line.
  |
 */

$app->register(App\Providers\EventServiceProvider::class);

/*
  |--------------------------------------------------------------------------
  | Load The Application Routes
  |--------------------------------------------------------------------------
  |
  | Next we will include the routes file so that they can all be added to
  | the application. This will provide all of the URLs the application
  | can respond to, as well as the controllers that may handle them.
  |
 */

$app->register(App\Providers\AppServiceProvider::class);

$app->register(App\Providers\AuthServiceProvider::class);

$app->register(App\Providers\EventServiceProvider::class);
$app->register(\Illuminate\Redis\RedisServiceProvider::class);

$app->register(Illuminate\Mail\MailServiceProvider::class);

//通过路由服务来注册路由
$app->register(App\Providers\RoutesServiceProvider::class);
//版本控制服务
$app->register(App\Providers\VersionServiceProvider::class);

//注册admin服务
$app->register(\App\Modules\Admin\AdminProvider::class);
//注册jwt-auth服务
$app->register(\Tymon\JWTAuth\Providers\LumenServiceProvider::class);

return $app;
