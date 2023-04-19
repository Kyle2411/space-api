<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Helpers\Validator;
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

        $filters = ArrayHelper::filterKeys($params, ["missionName", "companyName", "fromMissionDate", "toMissionDate", "missionStatus"]);

        $data = $this->mission_model->selectMissions($filters, $page, $page_size);

        return $this->prepareOkResponse($response, $data);
    }

    public function handleCreateMissions(Request $request, Response $response, array $uri_args)
    {
        $missions_data = $request->getParsedBody();

        foreach ($missions_data as $key => $mission) {

                $missions_model = new MissionModel();
                $missions_model->createMissions($mission);
        }

        return $this->prepareOkResponse($response, $missions_data);
        
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

    
}
