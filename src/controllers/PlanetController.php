<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Vanier\Api\Exceptions\HttpBadRequestException;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;
use Vanier\Api\Helpers\ArrayHelper;
use Vanier\Api\Helpers\WebServiceInvoker;
use Vanier\Api\Models\PlanetModel;
use Vanier\Api\Models\MoonModel;

class PlanetController extends BaseController
{
    // Model for Database Transactions
    private PlanetModel $planet_model;

    public function __construct()
    {
        $this->planet_model = new PlanetModel();
    }

    public function handleGetPlanets(Request $request, Response $response, array $uri_args)
    {
        $params = $request->getQueryParams();
        $page = isset($params["page"]) ? $params["page"] : null;
        $page_size = isset($params["page_size"]) ? $params["page_size"] : null;

        $filters = ArrayHelper::filterKeys($params, ["planetName", "planetColor", "starId", "fromMass","toMass", "fromDiameter", "toDiameter","fromLengthOfDay","toLengthOfDay" ,"fromSurfaceGravity", "toSurfaceGravity", "fromTemperature", "toTemperature"]);

        $data = $this->planet_model->selectPlanets($filters, $page, $page_size);

        foreach($data['data'] as &$planet)
        {
            $planet_uri = 'http://images-api.nasa.gov/search?q=' . $planet['planet_name'];
            $wsInvoker = new WebServiceInvoker();
            $planet_json = $wsInvoker->invokeUri($planet_uri);
            $planet_data = json_decode($planet_json);

            $planet["related_image"] = $planet_data->collection->items[0]->links[0]->href;
        }

        return $this->prepareOkResponse($response, $data);
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
    
}
