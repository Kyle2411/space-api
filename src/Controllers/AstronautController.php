<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Helpers\ArrayHelper;
use Vanier\Api\Models\AstronautModel;
use Vanier\Api\Models\MissionModel;

/**
 * Controller for Astronaut Control Logic
 */
class AstronautController extends BaseController
{
    // Model for Database Transactions
    private AstronautModel $astronaut_model;
    private MissionModel $mission_model;

    public function __construct()
    {
        $this->astronaut_model = new AstronautModel();
        $this->mission_model = new MissionModel();
    }

    /**
     * Handle Astronauts GET Request
     * @param Request $request Client Request
     * @param Response $response Server Response
     * @return Response Altered Server Response
     */
    public function handleGetAstronauts(Request $request, Response $response, array $uri_args)
    {
        // Get URI Parameters
        $params = $request->getQueryParams();

        // Get Page and Page Size from Parameters
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["pageSize"]) ? $params["pageSize"] : null;

        // Get Filters from Parameters
        $filters = ArrayHelper::filterKeys($params, ["name", "sex", "fromBirthYear", "toBirthYear", "militaryStatus"]);

        // Select Astronauts Based on Filters
        $results = $this->astronaut_model->selectAstronauts($filters, $page, $page_size);

        return $this->prepareOkResponse($response, $results, empty($results["data"]) ? 204 : 200);
    }

    /**
     * Handle Astronaut Missions GET Request
     * @param Request $request Client Request
     * @param Response $response Server Response
     * @return Response Altered Server Response
     */
    public function handleGetAstronaut(Request $request, Response $response, array $uri_args)
    {
        // Get URI Id Argument
        $asteroid_id = $uri_args["astronaut_id"];

        // Select Astronaut Based on Id
        $result = $this->astronaut_model->selectAstronaut($asteroid_id);

        return $this->prepareOkResponse($response, $result ? $result : [], empty($result) ? 204 : 200);
    }

    /**
     * Handle Astronaut GET Request
     * @param Request $request Client Request
     * @param Response $response Server Response
     * @return Response Altered Server Response
     */
    public function handleGetAstronautMissions(Request $request, Response $response, array $uri_args)
    {
        // Get URI Id Argument
        $astronaut_id = $uri_args["astronaut_id"];

        // Select Astronauts Based on Id
        $result = $this->astronaut_model->selectAstronaut($astronaut_id);

        // Get URI Parameters
        $params = $request->getQueryParams();

        // Get Page and Page Size from Parameters
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["pageSize"]) ? $params["pageSize"] : null;

        // Get Filters from Parameters
        $filters = ArrayHelper::filterKeys($params, ["missionName", "companyName", "fromMissionDate", "toMissionDate", "missionStatus"]);
        $filters["astronautId"] = $astronaut_id;
        
        $result["missions"] = $this->mission_model->selectMissions($filters, $page, $page_size);

        return $this->prepareOkResponse($response, $result ? $result : [], empty($result) ? 204 : 200);
    }
}