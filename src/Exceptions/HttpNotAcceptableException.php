<?php
namespace Vanier\Api\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpNotAcceptableException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 406;

    /**
     * @var string
     */
    protected $message = 'The content type is not accepted by this server';

    protected $title = "406 - Not Acceptable";
}