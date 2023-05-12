<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use Vanier\Api\Exceptions\HttpBadRequestException;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Helpers\ArrayHelper;
use Vanier\Api\Models\RocketModel;
use Vanier\Api\Models\MissionModel;

/**
 * Controller for Rocket Control Logic
 */
class RocketController extends BaseController
{
    // Model for Database Transactions
    private RocketModel $rocket_model;
    private MissionModel $mission_model;

    public function __construct()
    {
        $this->rocket_model = new RocketModel();
        $this->mission_model = new MissionModel();
    }

    /**
     * Handle Rockets GET Request
     * @param Request $request Client Request
     * @param Response $response Server Response
     * @return Response Altered Server Response
     */
    public function handleGetRockets(Request $request, Response $response, array $uri_args)
    {
        // Get URI Parameters
        $params = $request->getQueryParams();

        // Get Page and Page Size from Parameters
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["pageSize"]) ? $params["pageSize"] : null;

        // Supported Filters
        $filters = ["name", "company", "status", "fromThrust", "toThrust", "fromHeight", "toHeight", "fromPrice", "toPrice", "page", "pageSize"];

        // Set Param Rules
        $rules["name"] = ["optional", ["lengthBetween", 1, 64]];
        $rules["company"] = ["optional", ["lengthBetween", 1, 64]];
        $rules["status"] = ["optional", ["in", ["Active", "Retired", "Planned"]]];
        $rules["fromThrust"] = ["optional", "integer", ["min", 0], ["max", 9999999]];
        $rules["toThrust"] = ["optional", "integer", ["min", 0], ["max", 9999999]];
        $rules["fromHeight"] = ["optional", "numeric", ["min", 0], ["max", 999999]];
        $rules["toHeight"] = ["optional", "numeric", ["min", 0], ["max", 999999]];
        $rules["fromPrice"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["toPrice"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["page"] = ["optional", "integer", ["min", 1], ["max", 99999]];
        $rules["pageSize"] = ["optional", "integer", ["min", 1], ["max", 99999]];

        $filters_check = $this->checkFilters($params, $filters, $rules, $request);

        if ($filters_check) {
            return $this->prepareErrorResponse($filters_check);
        }

        // Select Rockets Based on Filters
        $results = $this->rocket_model->selectRockets($params, $page, $page_size);
        $results = ["filters" => $filters, ...$results];

        return $this->prepareOkResponse($response, $results, empty($results["data"]) ? 204 : 200);
    }

    /**
     * Handle Rocket GET Request
     * @param Request $request Client Request
     * @param Response $response Server Response
     * @return Response Altered Server Response
     */
    public function handleGetRocket(Request $request, Response $response, array $uri_args)
    {
        // Get URI Id Argument
        $rocket_id = $uri_args["rocket_id"];

        // Select Rocket Based on Id
        $result = $this->rocket_model->selectRocket($rocket_id);

        return $this->prepareOkResponse($response, $result ? $result : [], empty($result) ? 204 : 200);
    }

    /**
     * Handle Rocket Missions GET Request
     * @param Request $request Client Request
     * @param Response $response Server Response
     * @return Response Altered Server Response
     */
    public function handleGetRocketMissions(Request $request, Response $response, array $uri_args)
    {
        // Get URI Id Argument
        $rocket_id = $uri_args["rocket_id"];

        $id_check = $this->checkId($rocket_id, $request);

        if ($id_check) {
            return $this->prepareErrorResponse($id_check);
        }

        // Select Astronauts Based on Id
        $result = $this->rocket_model->selectRocket($rocket_id);

        // Get URI Parameters
        $params = $request->getQueryParams();

        // Get Page and Page Size from Parameters
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["pageSize"]) ? $params["pageSize"] : null;

        // Get Filters from Parameters
        $filters = ["missionName", "companyName", "fromMissionDate", "toMissionDate", "missionStatus", "page", "pageSize"];

        // Set Param Rules
        $rules["missionName"] = ["optional", ["lengthBetween", 1, 128]];
        $rules["companyName"] = ["optional", ["lengthBetween", 1, 64]];
        $rules["fromMissionDate"] = ["optional", ["dateFormat", "Y-m-d" ]];
        $rules["toMissionDate"] = ["optional", ["dateFormat", "Y-m-d" ]];
        $rules["missionStatus"] = ["optional", ["in", ["Success", "Failure", "Partial Failure", "Prelaunch Failure"]]];
        $rules["page"] = ["optional", "integer", ["min", 1], ["max", 99999]];
        $rules["pageSize"] = ["optional", "integer", ["min", 1], ["max", 99999]];

        $filters_check = $this->checkFilters($params, $filters, $rules, $request);

        if ($filters_check) {
            return $this->prepareErrorResponse($filters_check);
        }

        $params["rocketId"] = $rocket_id;

        $missions = $this->mission_model->selectMissions($params, $page, $page_size);
        $result["missions"] = ["filters" => $filters, ...$missions];

        return $this->prepareOkResponse($response, $result ? $result : [], empty($result) ? 204 : 200);
    }

    /**
     * Handle Rockets POST Request
     * @param Request $request Client Request
     * @param Response $response Server Response
     * @return Response Altered Server Response
     */
    public function handlePostRockets(RequestInterface $request, ResponseInterface $response) {
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

            $results = $this->rocket_model->insertRockets($body);

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

    /**
     * Handle Rockets PATCH Request
     * @param Request $request Client Request
     * @param Response $response Server Response
     * @return Response Altered Server Response
     */
    public function handlePatchRockets(RequestInterface $request, ResponseInterface $response) {
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

            $results = $this->rocket_model->updateRockets($body);

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