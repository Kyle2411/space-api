<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Vanier\Api\Exceptions\HttpBadRequestException;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;
use Vanier\Api\Helpers\ArrayHelper;
use Vanier\Api\Models\PlanetModel;
use Vanier\Api\Models\ExoPlanetModel;
use Vanier\Api\Models\StarModel;

class StarController extends BaseController
{
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

        $results = $this->star_model->selectStars($filters, $page, $page_size);

        return $this->prepareOkResponse($response, $results);
    }

    public function handleGetStar(Request $request, Response $response, array $uri_args)
    {
        $star_id = $uri_args['star_id'];
        
        $results = $this->star_model->selectStar($star_id);
        
        return $this->prepareOkResponse($response, $results ? $results : [], empty($results) ? 204 : 200);
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

        $results = $this->star_model->selectStar($star_id);
        $results['planets'] =  $planet_model->selectPlanets($filters, $page, $page_size);

        return $this->prepareOkResponse($response, $results ? $results : [], empty($results) ? 204 : 200);
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

        $results = $this->star_model->selectStar($star_id);
        $results['exoPlanets'] =  $exoPlanet_model->selectExoPlanets($filters, $page, $page_size);

        return $this->prepareOkResponse($response, $results ? $results : [], empty($results["data"]) ? 204 : 200);

    }

    public function handlePostStars(Request $request, Response $response) {
        // Get Request Body
        $body = $request->getParsedBody();

        try {
            if (!is_array($body) || empty($body)) {
               $exception = new HttpBadRequestException($request);
               $exception->setDescription("Request body is either empty or is not an array.");
               throw $exception;
            }

            $results = $this->star_model->insertStars($body);

            // If Result Contains Missing or Failed Rows...
            if (isset($results["rows_missing"])) {
                $exception = new HttpBadRequestException($request);
                $exception->setDescription(json_encode($results));
                throw $exception;
            } else if (isset($results["rows_failed"])) {
                $exception = new HttpUnprocessableContentException($request);
                $exception->setDescription(json_encode($results));
                throw $exception;
            }

        } catch (HttpException $e) {
            return $this->prepareErrorResponse($e);
        }
        return $this->prepareSuccessResponse(201, $results);
    }

    public function handlePatchStars(Request $request, Response $response) {
        // Get Request Body
        $body = $request->getParsedBody();

        try {
            if (!is_array($body) || empty($body)) {
               $exception = new HttpBadRequestException($request);
               $exception->setDescription("Request body is either empty or is not an array.");
               
               throw $exception;
            }

            $results = $this->star_model->updateStars($body);

            // If Result Contains Missing or Failed Rows...
            if (isset($results["rows_missing"])) {
                $exception = new HttpBadRequestException($request);
                $exception->setDescription(json_encode($results));
               
                throw $exception;
            } else if (isset($results["rows_failed"])) {
                $exception = new HttpUnprocessableContentException($request);
                $exception->setDescription(json_encode($results));
               
                throw $exception;
            }

        } catch (HttpException $e) {
            return $this->prepareErrorResponse($e);
        }

        return $this->prepareSuccessResponse(201, $results);
    }
}