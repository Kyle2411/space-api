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

        $errorLogger = new Logger("error_logs");
        $errorLogger->setTimeZone(new DateTimeZone('America/Toronto'));
        $log_handler = new StreamHandler(APP_LOG_DIR.'error.log', Logger::DEBUG);
        $errorLogger->pushHandler($log_handler);

        $response = $handler->handle($request);
        $body = (string) $response->getBody();
        $data = json_decode($body);
        
        $status = $response->getStatusCode();
    
        //Retrieving User Info via Token
        //Checking if token is in request Bearer body
        
        $uriPartial =explode("/", $request->getUri()->getPath());
        
        if($uriPartial[2] != "account" && $uriPartial[2] != "token"){
            
        $request_info = $_SERVER["REMOTE_ADDR"].' '.$request->getUri()->getPath();
        $token_payload = $request->getAttribute(APP_JWT_TOKEN_KEY);

        $logging_model = new WSLoggingModel();
        
        $logging_model->logUserAction($token_payload, $request_info, $status);

        //Retrieving email from token payload
        $emailRequest = $token_payload["email"];
        

        //Logging user request info
        if($status == 200 || $status == 201 || $status == 204 || $status == 202){
        $logger->info($emailRequest. " made a ". $request->getUri()->getPath()." ". $request->getMethod() ." Request at: " . $this->getCurrentDateAndTime());
        
        $response = $handler->handle($request);
        return $response;
        }

        else{
           
            if($request->getUri()->getPath() == "/space-api/planets/weight"){
                
                $message = $data->message;
                $errorLogger->error($emailRequest. " Failed to make a ". $request->getUri()->getPath()." ". $request->getMethod() ." Request because '". $message . "' at: " . $this->getCurrentDateAndTime());
            
                $response = $handler->handle($request);
                return $response;
                
            }

            if($request->getMethod() == "GET"){
                $message = $data->error->description->error;
                $errorLogger->error($emailRequest. " Failed to make a ". $request->getUri()->getPath()." ". $request->getMethod() ." Request because '". $message . "' at: " . $this->getCurrentDateAndTime());
            
                $response = $handler->handle($request);
                return $response;
            }
            else{

                
            $message = $data->error->message;
            
            $errorLogger->error($emailRequest. " Failed to make a ". $request->getUri()->getPath()." ". $request->getMethod() ." Request because '". $message . "' at: " . $this->getCurrentDateAndTime());
        
            $response = $handler->handle($request);
            return $response;
            }
        }
    }

        //if no token is in request Bearer body
        else{
            

            $message = $data->message;
            
            $uriPartial =explode("/", $request->getUri()->getPath());

            //Checking if user is logging in successfully
            if($uriPartial[2] == "token"){
                if($status == 200){

                $emailLogin = $request->getParsedBody()['email'];
                $logger->info($emailLogin ." Logged in at: " . $this->getCurrentDateAndTime());
            }
            else{

                $emailLogin = $request->getParsedBody()['email'];
                $errorLogger->error($emailLogin ." Failed to log in because $message at:" . $this->getCurrentDateAndTime());
            }
        }

            //Checking if user is creating account in successfully
            if($uriPartial[2] == "account"){
                if($status == 200){
                $emailLogin = $request->getParsedBody()['email'];
                $logger->info($emailLogin ." Registered new account at: " . $this->getCurrentDateAndTime());
            }
            else{
                $emailLogin = $request->getParsedBody()['email'];
                $errorLogger->error($emailLogin ." Failed to create account because $message at: " . $this->getCurrentDateAndTime());
            
        }
            
        }

        $response = $handler->handle($request);
        return $response;
    }
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