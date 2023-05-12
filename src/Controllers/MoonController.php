<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Vanier\Api\Exceptions\HttpBadRequestException;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;
use Vanier\Api\Helpers\ArrayHelper;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\MoonModel;

class MoonController extends BaseController
{
    // Model for Database Transactions
    private MoonModel $moon_model;

    public function __construct()
    {
        $this->moon_model = new MoonModel();
    }

    public function handleGetMoons(Request $request, Response $response, array $uri_args)
    {
        $params = $request->getQueryParams();

        // Get Page and Page Size from Parameters
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["pageSize"]) ? $params["pageSize"] : null;

        // Supported Filters
        $filters = ["moonName", "moonMass", "fromMoonRadius", "toMoonRadius", "moonDensity", "page", "pageSize"];

        // Set Param Rules
        $rules["moonName"] = ["optional", ["lengthBetween", 1, 128]];
        $rules["moonMass"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["fromMoonRadius"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["toMoonRadius"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["moonDensity"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["page"] = ["optional", "integer", ["min", 1], ["max", 99999]];
        $rules["pageSize"] = ["optional", "integer", ["min", 1], ["max", 99999]];

        $filters_check = $this->checkFilters($params, $filters, $rules, $request);

        if ($filters_check) {
            return $this->prepareErrorResponse($filters_check);
        }

        $data = $this->moon_model->selectMoons($params, $page, $page_size);
        $data = ["filters" => $filters, ...$data];

        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetMoon(Request $request, Response $response, array $uri_args)
    {
        $moon_id = $uri_args['moon_id'];

        $id_check = $this->checkId($moon_id, $request);

        if ($id_check) {
            return $this->prepareErrorResponse($id_check);
        }

        $data = $this->moon_model->selectMoon($moon_id);

        return $this->prepareOkResponse($response, $data);
    }

    public function handlePatchMoons(Request $request, Response $response) {
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

            $results = $this->moon_model->updateMoons($body);

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