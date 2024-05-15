<?php

namespace App\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Container\ContainerInterface;

class IndexController
{
    public function __construct(ContainerInterface $ci) {
        //$this->msg = $ci->get('Oi');
        //$this->msg = "oi";
    }

    public function index(Request $request, Response $response, $args)
    {
        //$response->getBody()->write($this->msg);
        $response = $response->withStatus(302);
        $response = $response->withHeader('Location', 'docs/index.html');

        return $response;
    }
}