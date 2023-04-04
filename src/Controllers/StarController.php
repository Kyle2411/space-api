<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Helpers\ArrayHelper;
use Vanier\Api\Models\PlanetModel;
use Vanier\Api\Models\ExoPlanetModel;
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
        $params = $request->getQueryParams();

        // Get Page and Page Size from Parameters
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["pageSize"]) ? $params["pageSize"] : null;

        $filters = ArrayHelper::filterKeys($params, ["starName", "temperature", "fromRadius", "toRadius", "fromMass", "toMass", "fromGravity", "toGravity"]);

        $data = $this->star_model->selectStars($filters, $page, $page_size);

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
        $params = $request->getQueryParams();

        // Get Page and Page Size from Parameters
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["pageSize"]) ? $params["pageSize"] : null;

        $filters = ArrayHelper::filterKeys($params, ["planetName", "planetColor", "star_id", "fromMass","toMass", "fromDiameter", "toDiameter","fromLengthOfDay","toLengthOfDay" ,"fromSurfaceGravity", "toSurfaceGravity", "toTemperature", "fromTemperature"]);
        $filters["star_id"] = $star_id;

        $planet_model = new PlanetModel();

        $data = $this->star_model->selectStar($star_id);
        $data['planets'] =  $planet_model->selectPlanets($filters, $page, $page_size);

        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetStarExoPlanets(Request $request, Response $response, array $uri_args)
    {
        $star_id = $uri_args['star_id'];
        $params = $request->getQueryParams();

        // Get Page and Page Size from Parameters
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["pageSize"]) ? $params["pageSize"] : null;

        $filters = ArrayHelper::filterKeys($params, ["exoPlanetName", "discoveryMethod" , "fromDiscoveryYear", "toDiscoveryYear"]);
        $filters["star_id"] = $star_id;

        $exoPlanet_model = new ExoPlanetModel();

        $data = $this->star_model->selectStar($star_id);
        $data['exoPlanets'] =  $exoPlanet_model->selectExoPlanets($filters, $page, $page_size);

        return $this->prepareOkResponse($response, $data);
    }
}