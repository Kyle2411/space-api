<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\PlanetModel;

class PlanetController extends BaseController
{
    // Model for Database Transactions
    private PlanetModel $planet_model;

    public function __construct()
    {
        $this->planet_model = new PlanetModel();
    }

    public function handleGetPlanets(Request $request, Response $response, array $uri_args)
    {
        $data = $this->planet_model->selectPlanets();        

        return $this->prepareOkResponse($response, $data);
    }
}
