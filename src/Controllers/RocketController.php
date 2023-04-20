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

        // Get Filters from Parameters
        $filters = ArrayHelper::filterKeys($params, ["name", "company", "status", "fromThrust", "toThrust", "fromHeight", "toHeight", "fromPrice", "toPrice"]);

        // Select Rockets Based on Filters
        $results = $this->rocket_model->selectRockets($filters, $page, $page_size);

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

        // Select Astronauts Based on Id
        $result = $this->rocket_model->selectRocket($rocket_id);

        // Get URI Parameters
        $params = $request->getQueryParams();

        // Get Page and Page Size from Parameters
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["pageSize"]) ? $params["pageSize"] : null;

        // Get Filters from Parameters
        $filters = ArrayHelper::filterKeys($params, ["name", "company", "status", "fromThrust", "toThrust", "fromHeight", "toHeight", "fromPrice", "toPrice"]);
        $filters["rocketId"] = $rocket_id;

        $result["missions"] = $this->mission_model->selectMissions($filters, $page, $page_size);

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
}