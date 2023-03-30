<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\MissionModel;

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
        $data = $this->mission_model->selectMissions();

        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetMission(Request $request, Response $response, array $uri_args)
    {
        $mission_id = $uri_args['mission_id'];

        $data = $this->mission_model->selectMission($mission_id);
        return $this->prepareOkResponse($response, $data);
    }
}
