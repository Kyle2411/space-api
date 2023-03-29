<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\PlanetModel;
use Vanier\Api\Models\StarModel;

class StarController extends BaseController
{
    // Model for Database Transactions
    private StarModel $star_model;

    public function __construct()
    {
        $this->star_model = new StarModel();
    }

    public function handleGetStars(Request $request, Response $response, array $uri_args)
    {
        $data = $this->star_model->selectStars();        

        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetStar(Request $request, Response $response, array $uri_args)
    {
        $star_id = $uri_args['star_id'];

        $data = $this->star_model->selectStar($star_id);      

        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetStarPlanets(Request $request, Response $response, array $uri_args)
    {
        $star_id = $uri_args['star_id'];
        $planet_model = new PlanetModel();

        $data = $this->star_model->selectStar($star_id);      
        $data['planets'] =  $planet_model->selectPlanets($star_id);      

        return $this->prepareOkResponse($response, $data);
    }
}