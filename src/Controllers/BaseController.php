<?php

namespace Vanier\Api\Controllers;

use Slim\Exception\HttpException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as ResponseObj;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;
use Vanier\Api\Helpers\ArrayHelper;
use Vanier\Api\Helpers\Validator;

class BaseController
{

    protected function checkColumns(array $data, array $desiredKeys, array $optional_keys, Request $request, Response $response)
    {
   
            //Checks if the actor has all of the keys and that their values are not empty
            foreach($desiredKeys as $key)
            {
                if (!array_key_exists($key, $data)) {

                    return $this->prepareResponse($response,
                    ['error' => true, 'message' => "Missing Required Key: '$key'"], 422);
                }

                else if (empty($data[$key])) {
                    return $this->prepareResponse($response,
                    ['error' => true, 'message' => "Column '$key' value cannot be empty"], 422);

                }
            }

            //Checks if the actor has no values empty
            foreach($desiredKeys as $key)
            {
                if (empty($data[$key])) {
                    return $this->prepareResponse($response,
                    ['error' => true, 'message' => "Column '$key' value cannot be empty"], 422);
                }
            }
           
            //Gets all of the invalid columns by checking if the column belongs in the allKeys array
            $allKeys = array_merge($desiredKeys, $optional_keys);
            $invalidKeys = array_diff(array_keys($data), $allKeys);
            if (!empty($invalidKeys)) {
                return $this->prepareResponse($response,
                    ['error' => true, 'message' => "Invalid Key(s): " . implode(',', $invalidKeys)], 422);
            }
            return $response;
        
    }

    protected function prepareOkResponse(Response $response, array $data, int $status_code = 200)
    {
        // var_dump($data);
        $json_data = json_encode($data);
        //-- Write data into the response's body.
        $response->getBody()->write($json_data);
        return $response->withStatus($status_code)->withAddedHeader(HEADERS_CONTENT_TYPE, APP_MEDIA_TYPE_JSON);
    }

    /**
     * Prepare an HTTP Success Response
     * @param $status_code Status Code to Send with Response
     * @param $data Feedback Data to Send with Response
     * @return Prepared HTTP Success Response
     */
    protected function prepareSuccessResponse(int $status_code, $data) : Response {
        $json = json_encode($data);

        $response = new ResponseObj();
        $response->getBody()->write($json);    
        return $response->withStatus($status_code)->withHeader("Content-Type", "application/json");
    }

    /**
     * Prepare an HTTP Error Response
     * @param HttpException $exception HTTP Exception to Generate Error Response
     * @return Response Prepared HTTP Error Response
     */
    protected function prepareErrorResponse(HttpException $exception) : Response {
        $description = json_decode($exception->getDescription());

            if (!$description) {
                $description = $exception->getDescription();
            }

            // Create Error Associative Array Using Exception Values
            $error = ["error" => ["code" => $exception->getCode(), "title" => $exception->getTitle(), "message" => $exception->getMessage(), "description" => $description]];

            // Generate Error Response in JSON Format
            $json_error = json_encode($error);
            $response = new ResponseObj();
            $response->getBody()->write($json_error);

            // Set Status Code to 406 Not Acceptable
            return $response->withStatus($exception->getCode())->withAddedHeader("Content-Type", APP_MEDIA_TYPE_JSON);
    }

    protected function LogInfo(){
        // $logger = ;
        // $db_logger = ;
    }

    public function prepareResponse(Response $response, $in_payload, $status_code) {
        $payload = json_encode($in_payload);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', APP_MEDIA_TYPE_JSON)
                        ->withStatus($status_code);
    }

    /**
     * Check If Filters Are Supported Or Not
     */
    protected function checkFilters(array $check_filters, array $supported_filters, array $rules, Request $request) {
        // Get Unsupported Keys from Parameters, If Any
        $missingKeys = ArrayHelper::keepMissingKeys($check_filters, $supported_filters);

        if (count($missingKeys) > 0) {
            $description = ["error" => "Parameters sent with query are unsupported.", "supported_params" => $supported_filters, "unsupported_params" => array_keys($missingKeys)]; 

            $exception = new HttpUnprocessableContentException($request);
            return $exception->setDescription(json_encode($description));
        }

        $validator = new Validator($check_filters);
        $validator->mapFieldsRules($rules);

        if (!$validator->validate()) {
            $description = ["error" => "Parameters sent with query are not properly formatted.", "errors" => $validator->errors()]; 

            $exception = new HttpUnprocessableContentException($request);
            return $exception->setDescription(json_encode($description));
        }

        return null;
    }

    /** 
     * Check If Id Is In A Valid Format
     */
    protected function checkId($id, Request $request) {
        if (!is_int($id)) {
            $description = "Id must be an integer.";

            $exception = new HttpUnprocessableContentException($request);
            return $exception->setDescription(json_encode($description));
        }

        return null;
    }
}
