<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Vanier\Api\Exceptions\HttpBadRequestException;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;
use Vanier\Api\Helpers\ArrayHelper;
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
        $params = $request->getQueryParams();

        // Get Page and Page Size from Parameters
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["pageSize"]) ? $params["pageSize"] : null;

        // Supported Filters
        $filters = ["asteroidName", "danger", "designation", "monitored", "fromMinDiameter", "toMaxDiameter", "fromMagnitude", "toMagnitude", "page", "pageSize"];

        // Set Param Rules
        $rules["asteroidName"] = ["optional", ["lengthBetween", 1, 64]];
        $rules["designation"] = ["optional", "numeric", ["min", 0], ["max", 999999]];
        $rules["monitored"] = ["optional", "integer", ["min", 0], ["max", 1]];
        $rules["danger"] = ["optional", "integer", ["min", 0], ["max", 1]];
        $rules["fromMagnitude"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["toMagnitude"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["fromMinDiameter"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["toMaxDiameter"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["page"] = ["optional", "integer", ["min", 1], ["max", 99999]];
        $rules["pageSize"] = ["optional", "integer", ["min", 1], ["max", 99999]];

        $filters_check = $this->checkFilters($params, $filters, $rules, $request);

        if ($filters_check) {
            return $this->prepareErrorResponse($filters_check);
        }

        $data = $this->asteroid_model->selectAsteroids($params, $page, $page_size);
        $data = ["filters" => $filters, ...$data];

        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetAsteroid(Request $request, Response $response, array $uri_args)
    {
        $asteroid_id = $uri_args['asteroid_id'];

        $results = $this->asteroid_model->selectAsteroid($asteroid_id);

        return $this->prepareOkResponse($response, $results ? $results : [], empty($results) ? 204 : 200);
    }

    public function handlePostAsteroids(Request $request, Response $response) {
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

            $results = $this->asteroid_model->insertAsteroids($body);

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

    public function handlePatchAsteroids(Request $request, Response $response) {
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

            $results = $this->asteroid_model->updateAsteroids($body);

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

    public function handleDeleteAsteroids(Request $request, Response $response) {
        // Get Request Body
        $body = $request->getParsedBody();

        try {
            if (!is_array($body) || empty($body)) {
               $exception = new HttpBadRequestException($request);
               $exception->setDescription("Request body is either empty or is not an array.");
               
               throw $exception;
            }

            $results = $this->asteroid_model->deleteAsteroids($body);

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

}