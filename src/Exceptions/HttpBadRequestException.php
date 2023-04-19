<?php

namespace Vanier\Api\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpBadRequestException extends HttpSpecializedException {
    /**
     * @var int
     */
    protected $code = 400;

    /**
     * @var string
     */
    protected $title = "400 Bad Request";

    /**
     * @var string
     */
    protected $message = "The server cannot or will not process the request due to something that is perceived to be a client error.";
}