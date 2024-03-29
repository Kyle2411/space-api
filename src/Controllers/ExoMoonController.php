<?php

namespace Vanier\Api\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
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

        // Supported Filters
        $filters = ["exoMoonName", "discoveryMethod", "orbitalPeriodDays", "exoMass", "page", "pageSize"];

        // Set Param Rules
        $rules["exoMoonName"] = ["optional", ["lengthBetween", 1, 64]];
        $rules["discoveryMethod"] =  ["optional", ["in", ["Radial Velocity","Imaging","Pulsation Timing Variations","Transit","Eclipse Timing Variations","Microlensing","Transit Timing Variations","Pulsation Timing","Disk Kinematics","Orbital Brightness Modulation"]]];
        $rules["orbitalPeriodDays"] = ["optional", "numeric", ["min", 0], ["max", 999999]];
        $rules["exoMass"] = ["optional", "numeric", ["min", 0], ["max", 999999]];
        $rules["page"] = ["optional", "integer", ["min", 1], ["max", 99999]];
        $rules["pageSize"] = ["optional", "integer", ["min", 1], ["max", 99999]];

        $filters_check = $this->checkFilters($params, $filters, $rules, $request);

        if ($filters_check) {
            return $this->prepareErrorResponse($filters_check);
        }
        
        $data = $this->exoMoon_model->selectExoMoons($params, $page, $page_size);
        $data = ["filters" => $filters, ...$data];

        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetExoMoon(Request $request, Response $response, array $uri_args)
    {
        $exomoon_id = $uri_args['exoMoon_id'];

        $id_check = $this->checkId($exomoon_id, $request);

        if ($id_check) {
            return $this->prepareErrorResponse($id_check);
        }

        $data = $this->exoMoon_model->selectExoMoon($exomoon_id);

        return $this->prepareOkResponse($response, $data);
    }

    public function handleCreateExoMoon(Request $request, Response $response, array $uri_args)
    {
        $body = $request->getParsedBody();

        try {
            if (!is_array($body) || empty($body)) {
                $exception = new HttpBadRequestException($request);
                $exception->setDescription("The body is either empty or not in the proper format");

                throw $exception;
            }

            if (ArrayHelper::isAssociative($body)) {
                $body = [$body];
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

    /**
     * Handle Exomoons DELETE Request
     * @param Request $request Client Request
     * @param Response $response Server Response
     * @return Response Altered Server Response
     */
    public function handleDeleteExomoons(RequestInterface $request, ResponseInterface $response) {
        // Get Request Body
        $body = $request->getParsedBody();

        try {
            if (!is_array($body) || empty($body)) {
               $exception = new HttpBadRequestException($request);
               $exception->setDescription("Request body is either empty or is not an array.");
               
               throw $exception;
            }

            $results = $this->exoMoon_model->deleteExomoons($body);

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

        return $this->prepareSuccessResponse(200, $results);
    }

    public function handlePatchExoMoons(Request $request, Response $response) {
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

            $results = $this->exoMoon_model->updateExoMoons($body);

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