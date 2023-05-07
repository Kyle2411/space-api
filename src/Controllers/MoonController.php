<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Vanier\Api\Exceptions\HttpBadRequestException;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;
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

    public function handlePatchMoons(Request $request, Response $response) {
        // Get Request Body
        $body = $request->getParsedBody();

        try {
            if (!is_array($body) || empty($body)) {
               $exception = new HttpBadRequestException($request);
               $exception->setDescription("Request body is either empty or is not an array.");
               
               throw $exception;
            }

            $results = $this->moon_model->updateMoons($body);

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