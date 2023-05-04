<?php
namespace Vanier\Api\Middlewares;

use DateTimeZone;

use DateTimeInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Vanier\Api\Exceptions\HttpNotAcceptableException;

class LoggingMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {   
        $logger = new Logger("access_logs");
        $logger->setTimeZone(new DateTimeZone('America/Toronto'));
        $log_handler = new StreamHandler(APP_LOG_DIR.'access.log', Logger::DEBUG);
        $logger->pushHandler($log_handler);
    
        //<-----------------Logging Types----------------->//
    
        //Error Log
        $logger->error("This Failed");
    
        //Alert Log
        $logger->alert("Hey this is an alert");
    
        //Warning Log
        $logger->warning("This is a warning");
    
        //Debug Log
        $logger->debug("This is a debug");
    
        //WNotice Log
        $logger->notice("This is a notice");
    
        //Critical Log
        $logger->critical("This is critical");
    
        //Emergency Log
        $logger->emergency("This is an emergency");
    
        //<-----------------Logging Types----------------->//
    

        $db_logger = new Logger('database_logs');
    
        $db_logger->setTimeZone(new DateTimeZone('America/Toronto'));
        $db_logger->pushHandler($log_handler);
    
        //Resource Log (Database)
        $db_logger->info("This query failed...");
    
        $params = $request->getQueryParams();
        $logger->info("Access: ".$request->getMethod().
        ''.$request->getUri()->getPath(), $params);
    
        $ip_address = $_SERVER["REMOTE_ADDR"];
        $logger->info("IP: ". $ip_address . $request->getMethod().
        ''.$request->getUri()->getPath(), $params);

        // $data = $request->getParsedBody();
        // $logger->info("Body Data: ".$data. $request->getMethod().
        // ''.$request->getUri()->getPath(), $params);

        $response = $handler->handle($request);
        return $response;
    }
}