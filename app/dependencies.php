<?php
// DIC configuration

$container = $app->getContainer();


// CSRF guard
$container['csrf'] = function ($c) {
    $guard = new \Slim\Csrf\Guard();
    $guard->setFailureCallable(function ($request, $response, $next) {
        $request = $request->withAttribute("csrf_status", false);
        return $next($request, $response);
    });
    return $guard;
};

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// $container['authService'] = function ($container) {
//     return new controllers\UserController($container->get('settings'));
// };
// controller
$container['AuthController'] = function ($c) {
    return new \App\Controllers\AuthController;
};

$container['HomeController'] = function ($container) {
    return new Controllers\HomeController;
};