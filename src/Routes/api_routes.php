<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Vanier\Api\Controllers\ExoMoonController;
use Vanier\Api\Controllers\RootController;
use Vanier\Api\Controllers\PlanetController;
use Vanier\Api\Controllers\ExoPlanetController;
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

$app->group('/exoMoon', function (RouteCollectorProxy $group) {
    $group->get('', [ExoMoonController::class, 'handleGetExoMoons']);
});

$app->group('/stars', function (RouteCollectorProxy $group) {
    $group->get('', [StarController::class, 'handleGetStars']);
    $group->get('/{star_id}', [StarController::class, 'handleGetStar']);
    $group->get('/{star_id}/planets', [StarController::class, 'handleGetStarPlanets']);
});

$app->group('/exoPlanet', function (RouteCollectorProxy $group) {
    $group->get('', [ExoPlanetController::class, 'handleGetExoPlanet']);
    $group->get('/{exoPlanet_id}', [ExoPlanetController::class, 'handleGetExoPLanet']);
    $group->get('/{exoPlanet_id}/exoMoons', [ExoPlanetController::class, 'handleGetExoPlanetExoMoons']);
});