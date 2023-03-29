<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Helpers\Validator;

class RootController extends BaseController
{
    public function handleGetRoot(Request $request, Response $response, array $uri_args)
    {
        $data = array(
            'about' => 'Welcome, this is a Web services that provides this and that...',
            'resources' => 'Blah'
        );
        return $this->prepareOkResponse($response, $data);
    }
}
