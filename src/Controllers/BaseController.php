<?php

namespace Vanier\Api\Controllers;

use Slim\Exception\HttpException;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BaseController
{
    protected function prepareOkResponse(ResponseInterface $response, array $data, int $status_code = 200)
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

        $response = new Response();
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
            $response = new Response();
            $response->getBody()->write($json_error);

            // Set Status Code to 406 Not Acceptable
            return $response->withStatus($exception->getCode())->withAddedHeader("Content-Type", APP_MEDIA_TYPE_JSON);
    }
}
