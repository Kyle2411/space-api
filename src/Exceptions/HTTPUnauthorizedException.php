<?php
namespace Vanier\Api\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HTTPUnauthorizedException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 401;

    /**
     * @var string
     */
    protected $message = 'The token is expired';

    protected $title = "401 - Unauthorized";
}