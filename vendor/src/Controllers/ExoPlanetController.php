<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\ExoPlanetModel;

class ExoPlanetController extends BaseController
{
    // Model for Database Transactions
    private ExoPlanetModel $exoPlanet_model;

    public function __construct()
    {
        $this->exoPlanet_model = new ExoPlanetModel();
    }

    public function handleGetExoPlanets(Request $request, Response $response, array $uri_args)
    {
        $data = $this->exoPlanet_model->selectExoPlanets();        

        return $this->prepareOkResponse($response, $data);
    }
}
