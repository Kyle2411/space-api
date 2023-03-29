<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Vanier\Api\Controllers\RootController;
use Vanier\Api\Controllers\PlanetController;
use Vanier\Api\Controllers\StarController;

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

$app->group('/stars', function (RouteCollectorProxy $group) {
    $group->get('', [StarController::class, 'handleGetStars']);
});