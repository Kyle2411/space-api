<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\AsteroidModel;

class AsteroidController extends BaseController
{
    // Model for Database Transactions
    private AsteroidModel $asteroid_model;

    public function __construct()
    {
        $this->asteroid_model = new AsteroidModel();
    }

    public function handleGetAsteroids(Request $request, Response $response, array $uri_args)
    {
        $data = $this->asteroid_model->selectAsteroids();

        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetAsteroid(Request $request, Response $response, array $uri_args)
    {
        $asteroid_id = $uri_args['asteroid_id'];

        $data = $this->asteroid_model->selectAsteroid($asteroid_id);

        return $this->prepareOkResponse($response, $data);
    }
}