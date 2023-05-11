<?php
namespace Vanier\Api\Models;

use DateTime;
use DateTimeZone;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;

/**
 * A model for managing the Web service users.
 *
 * @author Sleiman Rabah
 */
class WeightModel extends BaseModel {

    public function weightByPlanet($planetName)
    {
        $sql = "SELECT surface_gravity, planet_name
                FROM planet
                WHERE planet_name = :planet_name";

        return $this->run($sql, [":planet_name"=>$planetName])->fetch();
    }
}