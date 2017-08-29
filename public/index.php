<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

// all files path related to root
chdir(dirname(__DIR__));

require 'vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require 'app/settings.php';

$app = new \Slim\App($settings);

// twig tmp
// https://www.slimframework.com/docs/features/templates.html
// Get container
$container = $app->getContainer();
// Register component on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('templates', [
        // 'cache' => 'cache'
    ]);
    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
    return $view;
};


// Set up dependencies
require 'app/dependencies.php';

// Register middleware
require 'app/middleware.php';

// Register routes
require 'app/routes.php';

// SMS
require 'app/SMS/ChuanglanSmsHelper/ChuanglanSmsApi.php';


// Run app
$app->run();
