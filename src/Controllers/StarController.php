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

        // Supported Filters
        $filters = ["starName", "temperature", "fromRadius", "toRadius", "fromMass", "toMass", "fromGravity", "toGravity", "page", "pageSize"];

        // Set Param Rules
        $rules["starName"] = ["optional", ["lengthBetween", 1, 64]];
        $rules["temperature"] = ["optional", "numeric", ["min", 0], ["max", 999999]];
        $rules["fromRadius"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["toRadius"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["fromMass"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["toMass"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["fromGravity"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["toGravity"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["page"] = ["optional", "integer", ["min", 1], ["max", 99999]];
        $rules["pageSize"] = ["optional", "integer", ["min", 1], ["max", 99999]];

        $filters_check = $this->checkFilters($params, $filters, $rules, $request);

        if ($filters_check) {
            return $this->prepareErrorResponse($filters_check);
        }

        $results = $this->star_model->selectStars($params, $page, $page_size);
        $results = ["filters" => $filters, ...$results];

        return $this->prepareOkResponse($response, $results);
    }

    public function handleGetStar(Request $request, Response $response, array $uri_args)
    {
        $star_id = $uri_args['star_id'];

        $id_check = $this->checkId($star_id, $request);

        if ($id_check) {
            return $this->prepareErrorResponse($id_check);
        }
        
        $results = $this->star_model->selectStar($star_id);
        
        return $this->prepareOkResponse($response, $results ? $results : [], empty($results) ? 204 : 200);
    }

    public function handleGetStarPlanets(Request $request, Response $response, array $uri_args)
    {
        $star_id = $uri_args['star_id'];

        $id_check = $this->checkId($star_id, $request);

        if ($id_check) {
            return $this->prepareErrorResponse($id_check);
        }

        $params = $request->getQueryParams();

        // Get Page and Page Size from Parameters
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["pageSize"]) ? $params["pageSize"] : null;

        $filters = ["planetName", "planetColor", "fromMass","toMass", "fromDiameter", "toDiameter","fromLengthOfDay","toLengthOfDay" ,"fromSurfaceGravity", "toSurfaceGravity", "toTemperature", "fromTemperature", "page", "pageSize"];

        // Set Param Rules
        $rules["starId"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["planetName"] = ["optional", ["lengthBetween", 1, 64]];
        $rules["planetColor"] = ["optional", ["lengthBetween", 1, 64]];
        $rules["fromMass"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["toMass"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["fromDiameter"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["toDiameter"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["fromLengthOfDay"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["toLengthOfDay"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["fromSurfaceGravity"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["toSurfaceGravity"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["fromTemperature"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["toTemperature"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["page"] = ["optional", "integer", ["min", 1], ["max", 99999]];
        $rules["pageSize"] = ["optional", "integer", ["min", 1], ["max", 99999]];

        $filters_check = $this->checkFilters($params, $filters, $rules, $request);

        if ($filters_check) {
            return $this->prepareErrorResponse($filters_check);
        }

        // Add Star Id to Params
        $params["starId"] = $star_id;

        $planet_model = new PlanetModel();

        $results = $this->star_model->selectStar($star_id);
        $planets =  $planet_model->selectPlanets($params, $page, $page_size);
        $results['planets'] = ["filters" => $filters, ...$planets];

        return $this->prepareOkResponse($response, $results ? $results : [], !isset($results["star_id"]) ? 204 : 200);
    }

    public function handleGetStarExoPlanets(Request $request, Response $response, array $uri_args)
    {
        $star_id = $uri_args['star_id'];

        $id_check = $this->checkId($star_id, $request);

        if ($id_check) {
            return $this->prepareErrorResponse($id_check);
        }

        $params = $request->getQueryParams();

        // Get Page and Page Size from Parameters
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["pageSize"]) ? $params["pageSize"] : null;

        $filters = ["exoPlanetName", "discoveryMethod" , "fromDiscoveryYear", "toDiscoveryYear", "page", "pageSize"];

        // Set Param Rules
        $rules["starId"] = ["optional", "numeric", ["min", 1], ["max", 99999999]];
        $rules["exoPlanetName"] = ["optional", ["lengthBetween", 1, 64]];
        $rules["discoveryMethod"] = ["optional", ["in", ["Radial Velocity", "Imaging", "Pulsation Timing Variations", "Transit", "Eclipse Timing Variations", "Microlensing", "Transit Timing Variations", "Pulsation Timing", "Disk Kinematics", "Orbital Brightness Modulation"]]];
        $rules["fromDiscoveryYear"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["toDiscoveryYear"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["page"] = ["optional", "integer", ["min", 1], ["max", 99999]];
        $rules["pageSize"] = ["optional", "integer", ["min", 1], ["max", 99999]];

        $filters_check = $this->checkFilters($params, $filters, $rules, $request);

        if ($filters_check) {
            return $this->prepareErrorResponse($filters_check);
        }

        $params["starId"] = $star_id;
        
        $exoPlanet_model = new ExoPlanetModel();

        $results = $this->star_model->selectStar($star_id);
        $exoplanets =  $exoPlanet_model->selectExoPlanets($params, $page, $page_size);
        $results['exoPlanets'] = ["filters" => $filters, ...$exoplanets];

        return $this->prepareOkResponse($response, $results ? $results : [], !isset($results["star_id"]) ? 204 : 200);

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

            if (ArrayHelper::isAssociative($body)) {
                $body = [$body];
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

            if (ArrayHelper::isAssociative($body)) {
                $body = [$body];
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