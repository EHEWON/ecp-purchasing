<?php

/*
  |--------------------------------------------------------------------------
  | Create The Application
  |--------------------------------------------------------------------------
  |
  | First we need to get an application instance. This creates an instance
  | of the application / container and bootstraps the application so it
  | is ready to receive HTTP / Console requests from the environment.
  |
 */

define('INSTALL_PATH', str_replace('\\', '/', dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR);
if (!is_file(INSTALL_PATH . '/install.lock')) {
    header('HTTP/1.1 302 Moved Permanently');
    header('Location: /install/');
    exit;
}
$app = require __DIR__ . '/../bootstrap/app.php';

/*
  |--------------------------------------------------------------------------
  | Run The Application
  |--------------------------------------------------------------------------
  |
  | Once we have the application, we can handle the incoming request
  | through the kernel, and send the associated response back to
  | the client's browser allowing them to enjoy the creative
  | and wonderful application we have prepared for them.
  |
 */

$app->run();
