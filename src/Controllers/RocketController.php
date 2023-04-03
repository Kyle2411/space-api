<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
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
        $filters = ArrayHelper::filterKeys($params, []);
        
        // $result["missions"] = $this->mission_model->selectMissions([""])

        return $this->prepareOkResponse($response, $result ? $result : [], empty($result) ? 204 : 200);
    }
}