<?php
namespace Vanier\Api\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Vanier\Api\Exceptions\HttpNotAcceptableException;

class ContentNegotiationMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {   
        $accept = $request->getHeaderLine("Accept");

        if(!str_contains(APP_MEDIA_TYPE_JSON, $accept))
        {   
            throw new HttpNotAcceptableException($request, 'Content type is not acceptable, must be json.');
        }
        $response = $handler->handle($request);
        return $response;
    }
}