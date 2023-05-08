<?php

use Monolog\Handler\StreamHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
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
use Monolog\Logger;
use Vanier\Api\Controllers\AuthenticationController;

// Import the app instance into this file's scope.
global $app;


// NOTE: Add your app routes here.
// The callbacks must be implemented in a controller class.
// The Vanier\Api must be used as namespace prefix.

// ROUTE: /

//--Authentication Routes

//Account
$app->group('/account', function (RouteCollectorProxy $group) {
    $group->post('', [AuthenticationController::class, 'handleCreateUserAccount']);
});

//Token
$app->group('/token', function (RouteCollectorProxy $group) {
        $group->post('', [AuthenticationController::class, 'handleGetToken']);
});


// Stars
$app->group('/stars', function (RouteCollectorProxy $group) {
    $group->get('', [StarController::class, 'handleGetStars']);
    $group->get('/{star_id}', [StarController::class, 'handleGetStar']);
    $group->get('/{star_id}/planets', [StarController::class, 'handleGetStarPlanets']);
    $group->get('/{star_id}/exoPlanets', [StarController::class, 'handleGetStarExoPlanets']);
    $group->post('', [StarController::class, 'handlePostStars']);
    $group->patch('', [StarController::class, 'handlePatchStars']);
});

// Planet
$app->group('/planets', function (RouteCollectorProxy $group) {
    $group->get('', [PlanetController::class, 'handleGetPlanets']);
    $group->get('/{planet_id}', [PlanetController::class, 'handleGetPlanet']);
    $group->post('', [PlanetController::class, 'handlePostPlanets']);
    $group->get('/{planet_id}/moons', [PlanetController::class, 'handleGetPlanetMoons']);
    $group->patch('', [PlanetController::class, 'handlePatchPlanets']);
});

// ExoPlanets
$app->group('/exoplanets', function (RouteCollectorProxy $group) {
    $group->get('', [ExoPlanetController::class, 'handleGetExoPlanets']);
    $group->post('', [ExoPlanetController::class, 'handlePostExoPlanets']);
    $group->get('/{exoPlanet_id}', [ExoPlanetController::class, 'handleGetExoPlanet']);
    $group->get('/{exoPlanet_id}/exoMoons', [ExoPlanetController::class, 'handleGetExoPlanetExoMoons']);
    $group->delete('', [ExoPlanetController::class, 'handleDeleteExoPlanets']);
    $group->patch('', [ExoPlanetController::class, 'handlePatchExoPlanets']);
});

//ExoMoons
$app->group('/exomoons', function (RouteCollectorProxy $group) {
    $group->get('', [ExoMoonController::class, 'handleGetExoMoons']);
    $group->get('/{exoMoon_id}', [ExoMoonController::class, 'handleGetExoMoon']);
    $group->patch('', [ExoMoonController::class, 'handlePatchExoMoons']);
    $group->post('', [ExoMoonController::class, 'handleCreateExoMoon']);
    $group->delete('', [ExoMoonController::class, 'handleDeleteExoMoons']);
});

//Moons
$app->group('/moons', function (RouteCollectorProxy $group){
    $group->get('', [MoonController::class, 'handleGetMoons']);
    $group->patch('',[MoonController::class, 'handlePatchMoons']);
    $group->get('/{moon_id}', [MoonController::class, 'handleGetMoon']);
});

//Asteroids
$app->group('/asteroids', function (RouteCollectorProxy $group) {
    $group->get('', [AsteroidController::class, 'handleGetAsteroids']);
    $group->get('/{asteroid_id}', [AsteroidController::class, 'handleGetAsteroid']);
    $group->post('', [AsteroidController::class, 'handlePostAsteroids']);
    $group->patch('', [AsteroidController::class, 'handlePatchAsteroids']);
    $group->delete('', [AsteroidController::class, 'handleDeleteAsteroids']);
});

// Missions
$app->group('/missions', function (RouteCollectorProxy $group) {
    $group->get('', [MissionController::class, 'handleGetMissions']);
    $group->post('', [MissionController::class, 'handlePostMissions']);
    $group->get('/{mission_id}', [MissionController::class, 'handleGetMission']);
    $group->get('/{mission_id}/astronauts', [MissionController::class, 'handleGetMissionAstronauts']);
    $group->get('/{mission_id}/rocket', [MissionController::class, 'handleGetMissionRockets']);
    $group->patch('', [MissionController::class, 'handlePatchMissions']);
});

// Astronauts
$app->group('/astronauts', function (RouteCollectorProxy $group) {
    $group->get('', [AstronautController::class, 'handleGetAstronauts']);
    $group->get('/{astronaut_id}', [AstronautController::class, 'handleGetAstronaut']);
    $group->get('/{astronaut_id}/missions', [AstronautController::class, 'handleGetAstronautMissions']);
    $group->post('', [AstronautController::class, 'handlePostAstronauts']);
    $group->patch('', [AstronautController::class, 'handlePatchAstronauts']);
});

// Rockets
$app->group('/rockets', function (RouteCollectorProxy $group) {
    $group->get('', [RocketController::class, 'handleGetRockets']);
    $group->post("", [RocketController::class, 'handlePostRockets']);
    $group->patch("", [RocketController::class, 'handlePatchRockets']);
    $group->get('/{rocket_id}', [RocketController::class, 'handleGetRocket']);
    $group->get('/{rocket_id}/missions', [RocketController::class, 'handleGetRocketMissions']);
});