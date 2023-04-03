<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Helpers\ArrayHelper;
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
        $params = $request->getQueryParams();
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["page_size"]) ? $params["page_size"] : null;

        $filters = ArrayHelper::filterKeys($params, ["planetName", "color"]);

        $data = $this->planet_model->selectPlanets($filters, $page, $page_size);

        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetPlanet(Request $request, Response $response, array $uri_args)
    {
        $planet_id = $uri_args['planet_id'];

        $data = $this->planet_model->selectPlanet($planet_id);      
        return $this->prepareOkResponse($response, $data);
    }
}
