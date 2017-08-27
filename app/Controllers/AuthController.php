<?php
namespace App\Controllers;

use Slim\Views\Twig;
use Slim\Router;

class AuthController
{
    protected $view;
    private $router;
    protected $container;
    
    public function __construct(Twig $view, Router $router) {
        $this->view = $view;
        $this->router = $router;
    }

    public function login($request, $response, $args) {
        die('test');
        return $this->view->render($response, 'find_password.html');
        //   $this->view->render($response, 'find_password.html');
        // }
    }