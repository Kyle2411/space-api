<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Vanier\Api\Controllers\RootController;
use Vanier\Api\Controllers\PlanetController;

// Import the app instance into this file's scope.
global $app;

// NOTE: Add your app routes here.
// The callbacks must be implemented in a controller class.
// The Vanier\Api must be used as namespace prefix. 

// ROUTE: /
$app->get('/', [RootController::class, 'handleGetRoot']); 

$app->group('/planets', function (RouteCollectorProxy $group) {
    $group->get('', [PlanetController::class, 'handleGetPlanets']);
});

// ROUTE: /hello
$app->get('/hello', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    $response->getBody()->write("Reporting! Hello there!");    
    return $response;
}); 