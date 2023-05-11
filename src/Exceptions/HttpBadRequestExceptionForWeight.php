<?php

namespace Vanier\Api\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpBadRequestExceptionForWeight extends HttpSpecializedException {
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
    protected $message = "The requested planet wasn't possible: planet is either invalid or unsupported.";
}