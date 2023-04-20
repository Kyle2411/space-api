<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RootController extends BaseController
{
    public function handleGetRoot(Request $request, Response $response, array $uri_args)
    {
        $host_name = $request->getUri()->getHost();

        $data = array(
            'about' => 'Welcome to Cosmic Cloud, this is a Web API that provides a detailed aspects about the solar system',
            'version' => 'Note to self ill add later',
            'resources' => ['Planet ' => ['Description' => 'A planet is a celestial object in a solar system that orbits a star and has sufficient mass to generate its own gravitational force.', 
                                            'Uri' => $host_name.'/space-api/planets'], 
                            'ExoPlanet' => ['Description' => 'An ExoPlanet, also known as an extrasolar planet, is a planet that orbits a star outside of our solar system.', 
                                            'Uri' => $host_name.'/space-api/exoPlanets'],
                            'Moon'  => ['Description' => 'A moon, also known as a natural satellite, is a celestial body that orbits a planet or other object in space, and is typically much smaller than the object it orbits.', 
                                            'Uri' => $host_name.'/space-api/moons']],
                            'ExoMoon'  => ['Description' => 'An ExoMoon is a natural satellite that orbits an exoplanet, which is a planet outside of our solar system.', 
                                            'Uri' => $host_name.'/space-api/exoMoons'],
                            'Star'  => ['Description' => 'A star is a luminous sphere of plasma held together by its own gravity, emitting light and heat, and powered by nuclear fusion reactions in its core.', 
                                            'Uri' => $host_name.'/space-api/stars'],
                            'Mission'  => ['Description' => 'A space mission is a planned and organized journey to space by a spacecraft or a group of spacecrafts, with a specific purpose or goal such as scientific research, exploration, technology development, or commercial activities.', 
                                            'Uri' => $host_name.'/space-api/missions'],
                            'Astronaut'  => ['Description' => 'An astronaut is a person who is trained and selected to pilot, command, or serve as a crew member of a spacecraft.', 
                                            'Uri' => $host_name.'/space-api/astronauts'],
                            'Rocket'  => ['Description' => 'A rocket is a vehicle that is designed to travel through space by expelling exhaust gases out of its back end at high speeds.', 
                                            'Uri' => $host_name.'/space-api/rockets'],
        );
        return $this->prepareOkResponse($response, $data);
    }
}
