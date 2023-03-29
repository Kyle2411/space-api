<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\StarModel;

class StarController extends BaseController
{
    // Model for Database Transactions
    private StarModel $star_model;

    public function __construct()
    {
        $this->star_model = new StarModel();
    }

    public function handleGetStars(Request $request, Response $response, array $uri_args)
    {
        $data = $this->star_model->selectStars();        

        return $this->prepareOkResponse($response, $data);
    }
}