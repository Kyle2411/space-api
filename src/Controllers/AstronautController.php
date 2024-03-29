<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpException;
use Vanier\Api\Exceptions\HttpBadRequestException;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;
use Vanier\Api\Helpers\ArrayHelper;
use Vanier\Api\Models\AstronautModel;
use Vanier\Api\Models\MissionModel;

/**
 * Controller for Astronaut Control Logic
 */
class AstronautController extends BaseController
{
    // Model for Database Transactions
    /**
     * Summary of astronaut_model
     * @var AstronautModel
     */
    private AstronautModel $astronaut_model;
    /**
     * Summary of mission_model
     * @var MissionModel
     */
    private MissionModel $mission_model;

    /**
     * Summary of __construct
     */
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
    public function handleGetAstronauts(RequestInterface $request, ResponseInterface $response, array $uri_args)
    {
        // Get URI Parameters
        $params = $request->getQueryParams();

        // Get Page and Page Size from Parameters
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["pageSize"]) ? $params["pageSize"] : null;

        // Supported Filters
        $filters = ["name", "sex", "fromBirthYear", "toBirthYear", "militaryStatus", "page", "pageSize"];

        // Set Param Rules
        $rules["name"] = ["optional", ["lengthBetween", 1, 128]];
        $rules["sex"] =  ["optional", ["in", ["male", "female"]]];
        $rules["fromBirthYear"] = ["optional", "integer", ["min", 0], ["max", 99999]];
        $rules["toBirthYear"] = ["optional", "integer", ["min", 0], ["max", 99999]];
        $rules["militaryStatus"] = ["optional", "integer", ["min", 0], ["max", 1]];
        $rules["page"] = ["optional", "integer", ["min", 1], ["max", 99999]];
        $rules["pageSize"] = ["optional", "integer", ["min", 1], ["max", 99999]];

        $filters_check = $this->checkFilters($params, $filters, $rules, $request);

        if ($filters_check) {
            return $this->prepareErrorResponse($filters_check);
        }

        // Composite Resource
        $controller = new CompositeResourcesController();
        $astronautsInSpace = $controller->handleGetAllAstronautsInSpace();

        // Select Astronauts Based on Filters
        $results = $this->astronaut_model->selectAstronauts($params, $page, $page_size);
        $results = ["filters" => $filters, ...$results];
        
        if ($results)
            foreach ($results["data"] as &$astronaut) {
                foreach ($astronautsInSpace as $astronautInSpace) {
                    if ($astronaut["astronaut_name"] === $astronautInSpace) {
                        $astronaut["in_space"] = true;
                        break;
                    } else {
                        $astronaut["in_space"] = false;
                        continue;
                    }
                }
            }

        return $this->prepareOkResponse($response, $results ? $results : [], empty($results["data"]) ? 204 : 200);
    }

    /**
     * Handle Astronaut Missions GET Request
     * @param Request $request Client Request
     * @param Response $response Server Response
     * @return Response Altered Server Response
     */
    public function handleGetAstronaut(RequestInterface $request, ResponseInterface $response, array $uri_args)
    {
        // Get URI Id Argument
        $astronaut_id = $uri_args["astronaut_id"];

        $id_check = $this->checkId($astronaut_id, $request);

        if ($id_check) {
            return $this->prepareErrorResponse($id_check);
        }

        $controller = new CompositeResourcesController();
        $astronautsInSpace = $controller->handleGetAllAstronautsInSpace();

        // Select Astronaut Based on Id
        $result = $this->astronaut_model->selectAstronaut($astronaut_id);
        
        if ($result) 
            foreach ($astronautsInSpace as $astronautInSpace) {
                if ($result["astronaut_name"] == $astronautInSpace) {
                    $result["in_space"] = true;
                    break;
                } else {
                    $result["in_space"] = false;
                    continue;
                }
            }

        return $this->prepareOkResponse($response, $result ? $result : [], empty($result) ? 204 : 200);
    }

    /**
     * Handle Astronaut GET Request
     * @param Request $request Client Request
     * @param Response $response Server Response
     * @return Response Altered Server Response
     */
    public function handleGetAstronautMissions(RequestInterface $request, ResponseInterface $response, array $uri_args)
    {
        // Get URI Id Argument
        $astronaut_id = $uri_args["astronaut_id"];

        $id_check = $this->checkId($astronaut_id, $request);

        if ($id_check) {
            return $this->prepareErrorResponse($id_check);
        }

        // Select Astronauts Based on Id
        $result = $this->astronaut_model->selectAstronaut($astronaut_id);

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

        // Add Astronaut Id to Params
        $params["astronautId"] = $astronaut_id;
        
        $missions = $this->mission_model->selectMissions($params, $page, $page_size);
        $result["missions"] = ["filters" => $filters, ...$missions];

        return $this->prepareOkResponse($response, $result ? $result : [], empty($result) ? 204 : 200);
    }

    public function handlePostAstronauts(Request $request, ResponseInterface $response) {
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

            $results = $this->astronaut_model->insertAstronauts($body);

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
     * Handle Astronauts PATCH Request
     * @param Request $request Client Request
     * @param Response $response Server Response
     * @return Response Altered Server Response
     */
    public function handlePatchAstronauts(RequestInterface $request, ResponseInterface $response) {
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

            $results = $this->astronaut_model->updateAstronauts($body);

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