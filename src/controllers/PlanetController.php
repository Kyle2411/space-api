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

        $filters = ArrayHelper::filterKeys($params, ["planetName", "planetColor", "starId", "fromMass","toMass", "fromDiameter", "toDiameter","fromLengthOfDay","toLengthOfDay" ,"fromSurfaceGravity", "toSurfaceGravity", "fromTemperature", "toTemperature"]);

        $results = $this->planet_model->selectPlanets($filters, $page, $page_size);

        //Composite Resource
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

        // foreach($data['data'] as &$planet)
        // {
        //     $planet_uri = 'http://images-api.nasa.gov/search?q=' . $planet['planet_name'];
        //     $wsInvoker = new WebServiceInvoker();
        //     $planet_json = $wsInvoker->invokeUri($planet_uri);
        //     $planet_data = json_decode($planet_json);

        //     $planet["related_image"] = $planet_data->collection->items[0]->links[0]->href;
        // }

        return $this->prepareOkResponse($response, $results);
    }


    public function handleGetPlanet(Request $request, Response $response, array $uri_args)
    {
        $planet_id = $uri_args['planet_id'];

        $data = $this->planet_model->selectPlanet($planet_id);      
        return $this->prepareOkResponse($response, $data);
    }

    public function handleGetPlanetMoons(Request $request, Response $response, array $uri_args)
    {
        $planet_id = $uri_args['planet_id'];

        
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
            
        $json_data = json_encode($totalWeight);
        $response->getBody()->write("Your weight on planet: " . $planetName . "\n" . "is " . $json_data . "\n");


        return $response->withStatus(StatusCodeInterface::STATUS_ACCEPTED)->withHeader("Content-Type", "application/json");
    }
    
}
