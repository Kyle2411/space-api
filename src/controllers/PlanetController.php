<?php

namespace Vanier\Api\Controllers;

use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Vanier\Api\Exceptions\HttpBadRequestException;
use Vanier\Api\Exceptions\HttpBadRequestExceptionForWeight;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;
use Vanier\Api\Helpers\ArrayHelper;
use Vanier\Api\Helpers\Calculator;
use Vanier\Api\Helpers\WebServiceInvoker;
use Vanier\Api\Models\PlanetModel;
use Vanier\Api\Models\MoonModel;
use Vanier\Api\Models\WeightModel;

class PlanetController extends BaseController
{
    // Model for Database Transactions
    private PlanetModel $planet_model;
    private WeightModel $weight_model;

    public function __construct()
    {
        $this->planet_model = new PlanetModel();
        $this->weight_model = new WeightModel();
    }
    

    public function handleGetPlanets(Request $request, Response $response, array $uri_args)
    {
        $params = $request->getQueryParams();
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["page_size"]) ? $params["page_size"] : null;

        // Supported Filters
        $filters = ["planetName", "planetColor", "starId", "fromMass","toMass", "fromDiameter", "toDiameter","fromLengthOfDay","toLengthOfDay" ,"fromSurfaceGravity", "toSurfaceGravity", "fromTemperature", "toTemperature", "page", "pageSize"];

        // Set Param Rules
        $rules["starId"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["planetName"] = ["optional", ["lengthBetween", 1, 64]];
        $rules["planetColor"] = ["optional", ["lengthBetween", 1, 64]];
        $rules["fromMass"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["toMass"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["fromDiameter"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["toDiameter"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["fromLengthOfDay"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["toLengthOfDay"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["fromSurfaceGravity"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["toSurfaceGravity"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["fromTemperature"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["toTemperature"] = ["optional", "numeric", ["min", 0], ["max", 9999]];
        $rules["page"] = ["optional", "integer", ["min", 1], ["max", 99999]];
        $rules["pageSize"] = ["optional", "integer", ["min", 1], ["max", 99999]];

        $filters_check = $this->checkFilters($params, $filters, $rules, $request);

        if ($filters_check) {
            return $this->prepareErrorResponse($filters_check);
        }

        $results = $this->planet_model->selectPlanets($params, $page, $page_size);
        $results = ["filters" => $filters, ...$results];

        // Composite Resource
        $controller = new CompositeResourcesController();
        $planetsImages = $controller->handleGetAllPlanetImages();
        
        foreach ($results["data"] as &$planet) {
            foreach ($planetsImages as $planetImages) {
                if ($planet["planet_name"] === $planetImages['name']) {
                    $planet["images"] = $planetImages['related_image'];
                    break;
                } else {
                    $planet["images"] = null;
                    continue;
                }
            }
        }

        return $this->prepareOkResponse($response, $results);
    }


    public function handleGetPlanet(Request $request, Response $response, array $uri_args)
    {
        $planet_id = $uri_args['planet_id'];

        $id_check = $this->checkId($planet_id, $request);

        if ($id_check) {
            return $this->prepareErrorResponse($id_check);
        }

        $data = $this->planet_model->selectPlanet($planet_id);      
        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetPlanetMoons(Request $request, Response $response, array $uri_args)
    {
        $planet_id = $uri_args['planet_id'];

        $id_check = $this->checkId($planet_id, $request);

        if ($id_check) {
            return $this->prepareErrorResponse($id_check);
        }
        
        $moon_model = new moonModel();

        $data = $this->planet_model->selectplanet($planet_id);
        $data['moon'] =  $moon_model->selectMoonByPlanet($planet_id);

        return $this->prepareOkResponse($response, $data);
    }

    public function handlePostPlanets(Request $request, Response $response) {
        // Get Request Body
        $body = $request->getParsedBody();

        try {
            if (!is_array($body) || empty($body)) {
               $exception = new HttpBadRequestException($request);
               $exception->setDescription("Request body is either empty or is not an array.");
               throw $exception;
            }

            $results = $this->planet_model->insertPlanets($body);

            // If Result Contains Missing or Failed Rows...
            if (isset($results["rows_missing"])) {
                $exception = new HttpBadRequestException($request);
                $exception->setDescription(json_encode($results));
                throw $exception;
            } else if (isset($results["rows_failed"])) {
                $exception = new HttpUnprocessableContentException($request);
                $exception->setDescription(json_encode($results));
                throw $exception;
            }

        } catch (HttpException $e) {
            return $this->prepareErrorResponse($e);
        }
        return $this->prepareSuccessResponse(201, $results);
    }

    public function handlePatchPlanets(Request $request, Response $response) {
        // Get Request Body
        $body = $request->getParsedBody();

        try {
            if (!is_array($body) || empty($body)) {
               $exception = new HttpBadRequestException($request);
               $exception->setDescription("Request body is either empty or is not an array.");
               
               throw $exception;
            }

            $results = $this->planet_model->updatePlanets($body);

            // If Result Contains Missing or Failed Rows...
            if (isset($results["rows_missing"])) {
                $exception = new HttpBadRequestException($request);
                $exception->setDescription(json_encode($results));
               
                throw $exception;
            } else if (isset($results["rows_failed"])) {
                $exception = new HttpUnprocessableContentException($request);
                $exception->setDescription(json_encode($results));
               
                throw $exception;
            }

        } catch (HttpException $e) {
            return $this->prepareErrorResponse($e);
        }

        return $this->prepareSuccessResponse(201, $results);
    }


    public function handlePostWeight(Request $request, Response $response)
    {
        $weight_data = $request->getParsedBody();

        $data = $this->planet_model->selectPlanets();
        $allPlanets = $data['data'];
        $planet_data = [];

        $desired_columns = ['planet_name','weight', 'unit'];
        $checkColumn = $this->checkColumns($weight_data, $desired_columns, [], $request, $response);
        
        if($checkColumn->getStatusCode() == 422){

            return $checkColumn;
        }
        

        foreach($allPlanets as &$planet)
        {
            array_push($planet_data, $planet['planet_name']);
        }

        $unit_data = $weight_data['unit'];

        $weight = $weight_data['weight'];
        $planetName = $weight_data['planet_name'];

        
        $arrayGravity = $this->weight_model->weightByPlanet($planetName);

        if ($arrayGravity != true) {
            throw new HttpBadRequestExceptionForWeight($request);
        }
        $gravity = $arrayGravity['surface_gravity'];
       
            
        $calculator = new Calculator();

        $totalWeight = $calculator->calculate( 
            $planet_data, $planetName, $weight, $gravity  
            )->toMany($unit_data, 2 , true);

        //$totalWeight = round($totalWeight, 2);

        $result["message"] = "Your weight on planet: " . $planetName;
        $result["weights"] = $totalWeight;
            
        $json_data = json_encode($result);
        $response->getBody()->write($json_data);


        return $response->withStatus(StatusCodeInterface::STATUS_ACCEPTED)->withHeader("Content-Type", "application/json");
    }
    
}
