<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Helpers\ArrayHelper;
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
        $params = $request->getQueryParams();

        // Get Page and Page Size from Parameters
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["pageSize"]) ? $params["pageSize"] : null;

        $filters = ArrayHelper::filterKeys($params, ["moonName", "moonMass", "fromMoonRadius", "toMoonRadius", "moonDensity"]);
        $data = $this->moon_model->selectMoons($filters, $page, $page_size);
        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetMoon(Request $request, Response $response, array $uri_args)
    {
        $moon_id = $uri_args['moon_id'];

        $data = $this->moon_model->selectMoon($moon_id);

        return $this->prepareOkResponse($response, $data);
    }
}