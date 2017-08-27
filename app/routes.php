<?php
// Routes

// Render Twig template in route
$app->get('/', function ($request, $response, $args) {
    // return $this->view->render($response, 'index.html', [
    //     'name' => $args['name']
    // ]);
    return $this->view->render($response, 'index.html');
})->setName('index');
$app->get('/home', 'HomeController:index');
$app->get('/login', 'AuthController:login');
// $app->group('/auth', function () {
//     $this->map(['GET', 'POST'], '/login', 'Controllers\AuthController:login');
//     $this->map(['GET', 'POST'], '/logout', 'Controllers\AuthController:logout');
//     $this->map(['GET', 'POST'], '/signup', 'Controllers\AuthController:signup');
// });

$app->get('/find_password', function ($request, $response, $args) {
    return $this->view->render($response, 'find_password.html');
})->setName('find_password');

$app->get('/userinfo', function ($request, $response, $args) {
    return $this->view->render($response, 'userinfo.html');
})->setName('userinfo');