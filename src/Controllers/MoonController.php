<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
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
        $data = $this->moon_model->selectMoons();
        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetMoon(Request $request, Response $response, array $uri_args)
    {
        $moon_id = $uri_args['moon_id'];

        $data = $this->moon_model->selectMoon($moon_id);

        return $this->prepareOkResponse($response, $data);
    }
}