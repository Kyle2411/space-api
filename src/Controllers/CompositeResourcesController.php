<?php
namespace Vanier\Api\Controllers;

use Vanier\Api\Helpers\WebServiceInvoker;
use Vanier\Api\Models\PlanetModel;

class CompositeResourcesController extends WebServiceInvoker
{
    //Consume shows resource
    public function handleGetAllAstronautsInSpace() : array
    {
        $astronaut_uri = 'http://api.open-notify.org/astros.json';
        $data = $this->invokeUri($astronaut_uri);
        $astronauts = json_decode($data); 
        
        $retrieved_astronauts = [];

        foreach ($astronauts->people as $astronaut) {
            $retrieved_astronauts[] = $astronaut->name;
        }

        return $retrieved_astronauts;
    }

    public function handleGetAllPlanetImages() : array
    {
        $planet_model = new PlanetModel();
        $data = $planet_model->selectPlanets();
        
        $retrieved_planets = [];
        $wsInvoker = new WebServiceInvoker();

        foreach($data['data'] as $planet)
        {
            $planet_uri = 'http://images-api.nasa.gov/search?q=' . $planet['planet_name'];
            $planet_json = $wsInvoker->invokeUri($planet_uri);
            $planet_data = json_decode($planet_json);

            $planet_info = [
                'name' => $planet['planet_name'],
                'related_image' => $planet_data->collection->items[0]->links[0]->href,
            ];
        
            $retrieved_planets[] = $planet_info;
        }
        return $retrieved_planets;
    }
}