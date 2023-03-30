<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Helpers\ArrayHelper;
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
        $params = $request->getQueryParams();
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["page_size"]) ? $params["page_size"] : null;

        $filters = ArrayHelper::filterKeys($params, ["exoPlanetName", "discoveryMethod" , "fromDiscoveryYear", "toDiscoveryYear"]);

        $data = $this->exoPlanet_model->selectExoPlanets($filters, $page, $page_size);

        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetExoPlanet(Request $request, Response $response, array $uri_args)
    {
        $exoplanet_id = $uri_args['exoPlanet_id'];

        $data = $this->exoPlanet_model->selectExoPlanet($exoplanet_id);
        return $this->prepareOkResponse($response, $data);
    }
}
