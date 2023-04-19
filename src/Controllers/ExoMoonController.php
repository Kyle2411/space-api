<?php

namespace Vanier\Api\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\ExoMoonModel;
use Vanier\Api\Helpers\ArrayHelper;

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
        $params = $request->getQueryParams();

        // Get Page and Page Size from Parameters
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["pageSize"]) ? $params["pageSize"] : null;

        $filters = ArrayHelper::filterKeys($params, ["exoMoonName", "discoveryMethod", "orbitalPeriodDays", "exoMass"]);
        
        $data = $this->exoMoon_model->selectExoMoons($filters, $page, $page_size);
        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetExoMoon(Request $request, Response $response, array $uri_args)
    {
        $exomoon_id = $uri_args['exoMoon_id'];

        $data = $this->exoMoon_model->selectExoMoon($exomoon_id);

        return $this->prepareOkResponse($response, $data);
    }

    public function handleCreateExoMoon(Request $request, Response $response, array $uri_args)
    {
        $exomoon_id_data = $request->getParsedBody();

        foreach ($exomoon_id_data as $exoMoon)
        {
            $this->exoMoon_model->createExoMoon($exoMoon);
           
        }
        return $this->prepareOkResponse($response, $exomoon_id_data);
    }
}