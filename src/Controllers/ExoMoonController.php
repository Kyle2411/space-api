<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\ExoMoonModel;

class ExoMoonController extends BaseController
{
    // Model for Database Transactions
    private ExoMoonModel $exoMoon_model;

    public function __construct()
    {
        $this->exoMoon_model = new ExoMoonModel();
    }

    public function handleGetExoMoons(Request $request, Response $response, array $uri_args)
    {
        $data = $this->exoMoon_model->selectExoMoons();
        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetExMoon(Request $request, Response $response, array $uri_args)
    {
        $exomoon_id = $uri_args['exoMoon_id'];

        $data = $this->exoMoon_model->selectExoMoon($exomoon_id);

        return $this->prepareOkResponse($response, $data);
    }
}