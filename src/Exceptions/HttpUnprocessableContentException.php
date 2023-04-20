<?php

namespace Vanier\Api\Exceptions;

use Slim\Exception\HttpException;
use Slim\Exception\HttpSpecializedException;

class HttpUnprocessableContentException extends HttpSpecializedException {
    /**
     * @var int
     */
    protected $code = 422;

    /**
     * @var string
     */
    protected $title = "422 Unprocessable Content";

    /**
     * @var string
     */
    protected $message = "The server understands the content type of the request entity, and the syntax of the request entity is correct, but it was unable to process the contained instructions.";
}