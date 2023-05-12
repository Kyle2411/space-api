<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Vanier\Api\Exceptions\HttpBadRequestException;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;
use Vanier\Api\Models\MissionModel;
use Vanier\Api\Models\AstronautModel;
use Vanier\Api\Models\rocketModel;
use Vanier\Api\Helpers\ArrayHelper;

class MissionController extends BaseController
{
    // Model for Database Transactions
    private MissionModel $mission_model;

    public function __construct()
    {
        $this->mission_model = new MissionModel();
    }

    public function handleGetMissions(Request $request, Response $response, array $uri_args)
    {

        $params = $request->getQueryParams();
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["page_size"]) ? $params["page_size"] : null;

        $filters = ["rocketId", "missionName", "companyName", "fromMissionDate", "toMissionDate", "missionStatus"];

        $data = $this->mission_model->selectMissions($params, $page, $page_size);
        $data = ["filters" => $filters, ...$data];

        return $this->prepareOkResponse($response, $data);
    }

    public function handlePostMissions(Request $request, Response $response) {
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

            $results = $this->mission_model->insertMissions($body);

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

    public function handleGetMission(Request $request, Response $response, array $uri_args)
    {
        $mission_id = $uri_args['mission_id'];

        $data = $this->mission_model->selectMission($mission_id);
        return $this->prepareOkResponse($response, $data);
    }


    public function handleGetMissionAstronauts(Request $request, Response $response, array $uri_args)
    {
        $mission_id = $uri_args['mission_id'];

        
        $astronaut_model = new astronautModel();

        $data = $this->mission_model->selectMission($mission_id);
        
        $data['astronaut'] =  $astronaut_model->selectAstronautByMission($mission_id);

        
        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetMissionRockets(Request $request, Response $response, array $uri_args)
    {
        $mission_id = $uri_args['mission_id'];

        
        $rocket_model = new rocketModel();

        $data = $this->mission_model->selectMission($mission_id);
        $newData = $rocket_model->selectRocket($data['rocket_id']);
        $data['rocket_name'] =  $newData[0]['rocket_name'];

        
        return $this->prepareOkResponse($response, $data);
    }

    public function handlePatchMissions(Request $request, Response $response) {
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

            $results = $this->mission_model->updateMissions($body);

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
