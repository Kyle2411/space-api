<?php
namespace Vanier\Api\Middlewares;

use DateTime;
use DateTimeZone;

use DateTimeInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Responses;
use Vanier\Api\Exceptions\HttpNotAcceptableException;
use Vanier\Api\Models\WSLoggingModel;

class LoggingMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Responses
    {   
        // //<-----------------Logging Types----------------->//
    
        // //Error Log
        // $logger->error("This Failed");
    
        // //Alert Log
        // $logger->alert("Hey this is an alert");
    
        // //Warning Log
        // $logger->warning("This is a warning");
    
        // //Debug Log
        // $logger->debug("This is a debug");
    
        // //WNotice Log
        // $logger->notice("This is a notice");
    
        // //Critical Log
        // $logger->critical("This is critical");
    
        // //Emergency Log
        // $logger->emergency("This is an emergency");

        //Assigning Log defaults
        $logger = new Logger("access_logs");
        $logger->setTimeZone(new DateTimeZone('America/Toronto'));
        $log_handler = new StreamHandler(APP_LOG_DIR.'access.log', Logger::DEBUG);
        $logger->pushHandler($log_handler);
    
        //Retrieving User Info via Token
        $token_payload = $request->getAttribute(APP_JWT_TOKEN_KEY);

        //Checking if token is in request Bearer body
        if($token_payload != NULL){

        $logging_model = new WSLoggingModel();
        $request_info = $_SERVER["REMOTE_ADDR"].' '.$request->getUri()->getPath();
        $logging_model->logUserAction($token_payload, $request_info);

        //Retrieving email from token payload
        $emailRequest = $token_payload["email"];

        //Logging user request info
        $logger->info($emailRequest. " made a ". $request->getUri()->getPath()." ". $request->getMethod() ." Request at: " . $this->getCurrentDateAndTime());
        }

        //if no token is in request Bearer body
        else{
            
            $response = $handler->handle($request);
            $body = (string) $response->getBody();
            $data = json_decode($body);
            $message = $data->message;

            //Checking if user is logging in successfully
            if($message == "User logged in successfully!"){

                $emailLogin = $request->getParsedBody()['email'];
                $logger->info($emailLogin ." Logged in at: " . $this->getCurrentDateAndTime());
            }

            //Checking if user is creating account in successfully
            if($message == "The new user account has been created successfully!"){

                $emailLogin = $request->getParsedBody()['email'];
                $logger->info($emailLogin ." Registered new account at: " . $this->getCurrentDateAndTime());
            }
           
            return $response;
        }

        $response = $handler->handle($request);
        return $response;
    }

    private function getCurrentDateAndTime() {
        // By setting the time zone, we ensure that the produced time 
        // is accurate.
        $tz_object = new DateTimeZone('America/Toronto');
        $datetime = new DateTime();
        $datetime->setTimezone($tz_object);
        return $datetime->format('Y\-m\-d\ h:i:s');
    }
}