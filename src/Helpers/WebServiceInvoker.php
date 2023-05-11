<?php
namespace Vanier\Api\Helpers;

use Exception;
use GuzzleHttp\Client as clientGux;

class WebServiceInvoker
{
    private $request_options = [];
    public function __construct(array $options = []){
        $this->request_options = $options;
    }

    public function invokeUri(string $resource_uri){
        $client = new clientGux;

        $response = $client->request('GET', $resource_uri, $this->request_options);

        if($response->getStatusCode() !== 200)
        {
            throw new Exception('Something went wrong!' . $response->getReasonPhrase());
        }

        if(!str_contains($response->getHeaderLine('Content-Type'), 'application/json'))
        {
            throw new Exception('Unprocessable data format' . $response->getReasonPhrase());
        }

        $body = $response->getBody()->getContents();
        return $body;
    }
}