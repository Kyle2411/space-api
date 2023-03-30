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
use Vanier\Api\Controllers\AsteroidController;
use Vanier\Api\Controllers\AstronautController;
use Vanier\Api\Controllers\MoonController;
use Vanier\Api\Controllers\MissionController;
use Vanier\Api\Controllers\RocketController;

// Import the app instance into this file's scope.
global $app;

// NOTE: Add your app routes here.
// The callbacks must be implemented in a controller class.
// The Vanier\Api must be used as namespace prefix.

// ROUTE: /
$app->get('/', [RootController::class, 'handleGetRoot']);

$app->group('/stars', function (RouteCollectorProxy $group) {
    $group->get('', [StarController::class, 'handleGetStars']);
    $group->get('/{star_id}', [StarController::class, 'handleGetStar']);
    $group->get('/{star_id}/planets', [StarController::class, 'handleGetStarPlanets']);
    $group->get('/{star_id}/exoPlanets', [StarController::class, 'handleGetStarExoPlanets']);
});

$app->group('/planets', function (RouteCollectorProxy $group) {
    $group->get('', [PlanetController::class, 'handleGetPlanets']);
    $group->get('/{planet_id}', [PlanetController::class, 'handleGetPlanet']);
});

$app->group('/exoPlanets', function (RouteCollectorProxy $group) {
    $group->get('', [ExoPlanetController::class, 'handleGetExoPlanets']);
    $group->get('/{exoPlanet_id}', [ExoPlanetController::class, 'handleGetExoPLanet']);
    $group->get('/{exoPlanet_id}/exoMoons', [ExoPlanetController::class, 'handleGetExoPlanetExoMoons']);
});

$app->group('/exoMoons', function (RouteCollectorProxy $group) {
    $group->get('', [ExoMoonController::class, 'handleGetExoMoons']);
    $group->get('/{exoMoon_id}', [ExoMoonController::class, 'handleGetExoMoon']);
});

$app->group('/moons', function (RouteCollectorProxy $group){
    $group->get('', [MoonController::class, 'handleGetMoons']);
    $group->get('/{moon_id}', [MoonController::class, 'handleGetMoon']);
});

$app->group('/asteroids', function (RouteCollectorProxy $group) {
    $group->get('', [AsteroidController::class, 'handleGetAsteroids']);
    $group->get('/{asteroid_id}', [AsteroidController::class, 'handleGetAsteroid']);
});

$app->group('/missions', function (RouteCollectorProxy $group) {
    $group->get('', [MissionController::class, 'handleGetMissions']);
    $group->get('/{mission_id}', [MissionController::class, 'handleGetMission']);
});

$app->group('/astronauts', function (RouteCollectorProxy $group) {
    $group->get('', [AstronautController::class, 'handleGetAstronauts']);
    $group->get('/{astronaut_id}', [AstronautController::class, 'handleGetAstronaut']);
});

$app->group('/rockets', function (RouteCollectorProxy $group) {
    $group->get('', [RocketController::class, 'handleGetRockets']);
    $group->get('/{rocket_id}', [RocketController::class, 'handleGetRocket']);
});