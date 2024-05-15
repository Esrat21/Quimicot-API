<?php

date_default_timezone_set('America/Sao_Paulo');

use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

AppFactory::setContainer(new \DI\Container());

$app = AppFactory::create();

// Parse json, form data and xml
$app->addBodyParsingMiddleware();

// If you are adding the pre-packaged ErrorMiddleware set `displayErrorDetails` to `false`
#$app->addErrorMiddleware(false, true, true);
$app->addErrorMiddleware(true, true, true);

//Data Acess Objects
foreach (glob(__DIR__ . '/Models/Daos' . "/*.php") as $file) {
    require_once $file;
}
//require_once __DIR__ . "./Models/Daos/Dao_Professor.php";

///Controladoras e variáveis utilizadas via slim framework
require_once __DIR__ . '/Config/container.php';

//Rotas
require_once __DIR__ . '/Config/routes.php';

//Cors fix
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

/**
 * Catch-all route to serve a 404 Not Found page if none of the routes match
 * NOTE: make sure this route is defined last
 */
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    //throw new Slim\Exception\HttpNotFoundException($request);
    $response->getBody()->write('404 Not found');
    return $response->withStatus(404);
});
