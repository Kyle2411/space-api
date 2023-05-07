<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\BaseModel;
use Vanier\Api\Helpers\ArrayHelper;
use Vanier\Api\Models\StarModel;

class PlanetModel extends BaseModel  {
    private $table_name = "planet";

    public function __construct() {
        parent::__construct();
       
    }

    /**
     * Select Actors from Database Based on Filters
     * @param array $filters Filters for Query
     * @param int $page Number of Current Page
     * @param int $page_size Size of Current Page
     * @return array Paginated Actor Result Set
     */
    public function selectPlanets(array $filters = [], $page = null, $page_size = null) {
        // Set Page and Page Size Default Values If Params Null
        if (!$page) $page = 1;
        if (!$page_size) $page_size = 10;

        $query_values = [];

        // Base Statement
        $select = "SELECT p.*";
        $from = " FROM $this->table_name AS p";
        $where = " WHERE 1 ";
        $group_by = "";

        if (isset($filters["planetName"])) {
            $where .= " AND p.planet_name LIKE CONCAT('%', :planet_name, '%')";
            $query_values[":planet_name"] = $filters["planetName"];
        }

        if (isset($filters["color"])) {
            
            $parts = explode(',', $filters["color"]);
            foreach ($parts as $key => $values) {
                $where .= " AND p.color LIKE CONCAT('%', :color, '%')";
                $query_values[":color"] = $values;
            }
           
        }   

        if(isset($filters["fromMass"])) {
            $where .= " AND p.mass >= :fromMass";
            $query_values[":fromMass"] = $filters["fromMass"];
        }

        if(isset($filters["toMass"])) {
            $where .= " AND p.mass <= :toMass";
            $query_values[":toMass"] = $filters["toMass"];
        }



        if(isset($filters["fromDiameter"])) {
            $where .= " AND p.diameter >= :fromDiameter";
            $query_values[":fromDiameter"] = $filters["fromDiameter"];
        }

        if(isset($filters["toDiameter"])) {
            $where .= " AND p.diameter <= :toDiameter";
            $query_values[":toDiameter"] = $filters["toDiameter"];
        
        } 

        if(isset($filters["fromLengthOfDay"])) {
            $where .= " AND p.length_of_day <= :fromLengthOfDay";
            $query_values[":fromLengthOfDay"] = $filters["fromLengthOfDay"];
        }

        if(isset($filters["toLengthOfDay"])) {
            $where .= " AND p.length_of_day <= :toLengthOfDay";
            $query_values[":toLengthOfDay"] = $filters["toLengthOfDay"];
        }

        if(isset($filters["fromSurfaceGravity"])) {
            $where .= " AND p.surface_gravity <= :fromSurfaceGravity";
            $query_values[":fromSurfaceGravity"] = $filters["fromSurfaceGravity"];
        }

        if(isset($filters["toSurfaceGravity"])) {
            $where .= " AND p.surface_gravity <= :toSurfaceGravity";
            $query_values[":toSurfaceGravity"] = $filters["toSurfaceGravity"];
        }

        if(isset($filters["fromTemperature"])) {
            $where .= " AND p.temperature <= :fromTemperature";
            $query_values[":fromTemperature"] = $filters["fromTemperature"];
        }

        if(isset($filters["toTemperature"])) {
            $where .= " AND p.temperature <= :toTemperature";
            $query_values[":toTemperature"] = $filters["toTemperature"];
        }

        if (isset($filters["star_id"])) {
            $where .= " AND p.star_id = :star_id";
            $query_values[":star_id"] = $filters["star_id"];
        }

        $sql = $select . $from . $where . $group_by;

        // Return Paginated Results
        $this->setPaginationOptions($page, $page_size);
        return $this->paginate($sql, $query_values);
    }

    public function selectPlanetsSimple() {
        // Set Page and Page Size Default Values If Params Null

        // Base Statement
        $select = "SELECT p.*";
        $from = " FROM $this->table_name AS p";
        $where = " WHERE 1 ";
        $group_by = "";


        $sql = $select . $from . $where . $group_by;

        return $this->run($sql);
    }



    public function selectPlanet(int $planet_id){
        
        // Base Statement
        $select = "SELECT s.*";
        $from = " FROM $this->table_name AS s";
        $where = " WHERE planet_id =:planet_id AND 1 ";
        $group_by = "";

        $sql = $select . $from . $where . $group_by;

        return $this->run($sql, [":planet_id"=> $planet_id])->fetch();
    }

     /**
     * Insert Planets Into Database
     * @param array $data Planets to Insert
     * @return array Rows Inserted, Failed and/or Missing
     */
    public function insertPlanets(array $data) {

        //Custom Star ID validator
        Validator::addRule('starExists', function($field, $value, array $params, array $fields) {
            $min = $params[0] ?? null;
            $max = $params[1] ?? null;
        
            if (!is_numeric($value) || ($min !== null && $value < $min) || ($max !== null && $value > $max)) {
                return false;
            }
        
            $star = new StarModel();
            $starData = $star->selectStar($value);
            if(!$starData) {
                return false;
            }
        
            return true;
        }, 'does not exist');
        



        //Custom Planet Name validator
        Validator::addRule('planet_Name_Exists', function($field, $value, array $params, array $fields) {
            $planetModel = new PlanetModel();
            $methodName = "selectPlanetsSimple";
        
            $namerChecker = $this->checkExistingName($value, $methodName, $planetModel, 'planet_name');
            if(!$namerChecker) {
                return false;
            }
        
            $minLength = isset($params[0]) ? intval($params[0]) : null;
            $maxLength = isset($params[1]) ? intval($params[1]) : null;
        
            if (!is_null($minLength) && strlen($value) < $minLength) {
                return false;
            }
        
            if (!is_null($maxLength) && strlen($value) > $maxLength) {
                return false;
            }
        
            return true;
        }, 'already exists');


        //$rules["star_id"] = ["required", "numeric", ["min", 0], ["max", 99999999]];
        //$rules["planet_name"] = ["required", ["lengthBetween", 1, 64]];
        $rules["color"] = ["required", ["lengthBetween", 1, 64]];
        $rules["mass"] = ["required", "numeric", ["min", 0], ["max", 99999999]];
        $rules["diameter"] = ["required", "numeric", ["min", 0], ["max", 9999]];
        $rules["length_of_day"] = ["required", "numeric", ["min", 0], ["max", 9999]];
        $rules["orbital_period"] = ["required", "numeric", ["min", 0], ["max", 99999999]];
        $rules["surface_gravity"] = ["required", "numeric", ["min", 0], ["max", 9999]];
        $rules["temperature"] = ["required", "numeric", ["min", 0], ["max", 9999]];
        
        
        // For Each Rocket...
        foreach($data as $planet) {

            $extra_rules = [
                "star_id" => [
                    "required",
                    ["numeric", "min:0", "max:99999999"],
                    ["starExists"]
                ],
                "planet_name" => ["required", ["lengthBetween", 1, 64],["planet_Name_Exists", $planet]]
            ];
        
            $rules = array_merge($rules, $extra_rules);

            $validator = new Validator($planet);
            $validator->mapFieldsRules($rules);
  

            // If Data Is valid...
            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($planet, ["star_id", "planet_name", "color", "mass", "diameter", "length_of_day", "orbital_period","surface_gravity", "temperature", ]);
                
                // Insert Star Into Database
                $last_id = $this->insert($this->table_name, $fields);

                if ($last_id != 0) {
                    $results["row_inserted"][] = $this->selectPlanet($last_id);
                } else {
                    $results["rows_missing"][] = [...$planet, "errors" => "An error occured while inserting row."];
                }
            } else {
                $results["rows_failed"][] = [...$planet, "errors" => $validator->errors()];
            }
        }

        return $results;
    }

    
    
}
