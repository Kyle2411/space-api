<?php

namespace Vanier\Api\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Vanier\Api\Helpers\ArrayHelper;
use Slim\Exception\HttpException;
use Vanier\Api\Exceptions\HttpBadRequestException;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;
use Vanier\Api\Models\ExoPlanetModel;
use Vanier\Api\Models\ExoMoonModel;

class ExoPlanetController extends BaseController
{
    // Models for Database Transactions
    private ExoPlanetModel $exoPlanet_model;
    private ExoMoonModel $exoMoon_model;

    public function __construct()
    {
        $this->exoPlanet_model = new ExoPlanetModel();
    }

    public function handleGetExoPlanets(Request $request, Response $response, array $uri_args)
    {
        $params = $request->getQueryParams();
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["page_size"]) ? $params["page_size"] : null;

        // Supported Filters
        $filters = ["starId","exoPlanetName", "discoveryMethod" , "fromDiscoveryYear", "toDiscoveryYear", "page", "pageSize"];

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

        $data = $this->exoPlanet_model->selectExoPlanets($params, $page, $page_size);
        $data = ["filters" => $filters, ...$data];

        return $this->prepareOkResponse($response, $data);
    }

    public function handlePostExoPlanets(Request $request, Response $response) {
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

            $results = $this->exoPlanet_model->insertExoPlanets($body);

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

    public function handleGetExoPlanet(Request $request, Response $response, array $uri_args)
    {
        $exoplanet_id = $uri_args['exoPlanet_id'];

        $id_check = $this->checkId($exoplanet_id, $request);

        if ($id_check) {
            return $this->prepareErrorResponse($id_check);
        }

        $data = $this->exoPlanet_model->selectExoPlanet($exoplanet_id);
        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetExoPlanetExoMoons(Request $request, Response $response, array $uri_args)
    {
        $exoPlanet_id = $uri_args['exoPlanet_id'];

        $id_check = $this->checkId($exoPlanet_id, $request);

        if ($id_check) {
            return $this->prepareErrorResponse($id_check);
        }

        $filters = ['exoPlanet_id' => $exoPlanet_id];
        
        $exoMoon_model = new exoMoonModel();

        $data = $this->exoPlanet_model->selectExoPlanet($exoPlanet_id);
    
        $data['exoMoon'] = $exoMoon_model->selectExoMoonByExoPlanet($exoPlanet_id);
  
        return $this->prepareOkResponse($response, $data);
    }

    /**
     * Handle Exomoons DELETE Request
     * @param Request $request Client Request
     * @param Response $response Server Response
     * @return Response Altered Server Response
     */
    public function handleDeleteExoplanets(RequestInterface $request, ResponseInterface $response) {
        // Get Request Body
        $body = $request->getParsedBody();

        try {
            if (!is_array($body) || empty($body)) {
               $exception = new HttpBadRequestException($request);
               $exception->setDescription("Request body is either empty or is not an array.");
               
               throw $exception;
            }

            $results = $this->exoPlanet_model->deleteExoplanets($body);

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

        return $this->prepareSuccessResponse(200, $results);
    }

    public function handlePatchExoPlanets(Request $request, Response $response) {
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

            $results = $this->exoPlanet_model->updateExoPlanets($body);

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
