<?php

namespace Vanier\Api\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Vanier\Api\Exceptions\HttpBadRequestException;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;
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
        $body = $request->getParsedBody();

        try {
            if (!is_array($body) || empty($body)) {
                $exception = new HttpBadRequestException($body);
                $exception->setDescription("The body is either empty or not in the proper format");

                throw $exception;
            }

            $results = $this->exoMoon_model->createExoMoon($body);

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