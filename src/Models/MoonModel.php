<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\BaseModel;
use Vanier\Api\Helpers\ArrayHelper;

class MoonModel extends BaseModel  {
    private $table_name = "moon";

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
    public function selectMoons(array $filters = [], $page = null, $page_size = null) {
        // Set Page and Page Size Default Values If Params Null
        if (!$page) $page = 1;
        if (!$page_size) $page_size = 10;

        $query_values = [];

        // Base Statement
        $select = "SELECT planet.planet_name, m.*";
        $from = " FROM $this->table_name AS m";
        $join = " JOIN planet ON m.planet_id = planet.planet_id  ";
        $where = " WHERE 1 ";
        $group_by = "";

        if (isset($filters["moonName"])) {
            $where .= "AND m.moon_name LIKE CONCAT('%', :moon_name, '%') ";
            $query_values[":moon_name"] = $filters["moonName"];
        }

        if (isset($filters["moonMass"])) {
            $where .= "AND m.moon_mass = :moon_mass ";
            $query_values[":moon_mass"] = $filters["moonMass"];
        }

        if (isset($filters["fromMoonRadius"])) {
            $where .= "AND m.moon_radius >= :from_moon_radius ";
            $query_values[":from_moon_radius"] = $filters["fromMoonRadius"];
        }

        if (isset($filters["toMoonRadius"])) {
            $where .= "AND m.moon_radius <= :to_moon_radius ";
            $query_values[":to_moon_radius"] = $filters["toMoonRadius"];
        }

        if (isset($filters["moonDensity"])) {
            $where .= "AND m.moon_density = :moon_density ";
            $query_values[":moon_density"] = $filters["moonDensity"];
        }

        $sql = $select . $from . $join . $where . $group_by;

        // Return Paginated Results
        $this->setPaginationOptions($page, $page_size);
        return $this->paginate($sql, $query_values);
    }

    public function selectMoon(int $moon_id){
        
        // Base Statement
        $select = "SELECT planet.planet_name, m.*";
        $from = " FROM $this->table_name AS m";
        $join = " JOIN planet ON m.planet_id = planet.planet_id  ";
        $where = " WHERE moon_id =:moon_id AND 1 ";
        $group_by = "";

        $sql = $select . $from . $join . $where . $group_by;

        return $this->run($sql, [":moon_id"=> $moon_id])->fetchAll();
    }

    public function selectMoonByPlanet(int $planet_id){
        
        // Base Statement
        $select = "SELECT m.*";
        $from = " FROM moon AS m";
        $where = " WHERE planet_id =:planet_id AND 1 ";
        $group_by = "";

        $sql = $select . $from . $where . $group_by;
        
        return $this->run($sql, [":planet_id"=> $planet_id])->fetchAll();
    }

    public function selectMoonsSimple() {
        // Set Page and Page Size Default Values If Params Null

        // Base Statement
        $select = "SELECT m.*";
        $from = " FROM $this->table_name AS m";
        $where = " WHERE 1 ";
        $group_by = "";


        $sql = $select . $from . $where . $group_by;

        return $this->run($sql);
    }

     public function updateMoons($data) {

        $this->createValidators(true);


        $rules["moon_id"] = ["required"];
        $rules["planet_id"] = ["required", "numeric", ["min", 0], ["max", 99999999], ["planetExists"]];
        $rules["moon_name"] = ["required", ["lengthBetween", 1, 128], ["moon_Name_Exists"]];
        $rules["moon_mass"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["moon_radius"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["moon_density"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        foreach ($data as $moon) {

            $validator = new Validator($moon);
            $validator->mapFieldsRules($rules);

            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($moon, ["planet_id", "moon_name", "moon_mass", "moon_radius", "moon_density"]);
               
                // Update Astronaut Into Database
                if (count($fields) != 0) {
                    $row_count = $this->update($this->table_name, $fields, ["moon_id" => $moon["moon_id"]]);
                    if ($row_count != 0) {
                        $result["rows_affected"][] = $this->selectMoon($moon["moon_id"]);
                    } else
                        $result["rows_missing"][] = [...$moon, "errors" => "An error occurred while updating row or specified keys do not exist."];
                }
                else
                    $result["rows_failed"][] = [...$moon, "errors" => "There must be at least one field to update a row."];
            } else {
                $result["rows_failed"][] = [...$moon, "errors" => $validator->errors()];
            }
        }

        return $result;
    }


    private function createValidators($checkUpdate) {
        //Creating Custom planet_id validator
        Validator::addRule('planetExists', function($field, $value, array $params, array $fields) {
            $min = $params[0] ?? null;
            $max = $params[1] ?? null;
        
            if (!is_numeric($value) || ($min !== null && $value < $min) || ($max !== null && $value > $max)) {
                return false;
            }
        
            $planet = new PlanetModel();
            // Might cause a problem becasue it is fetch and not fetchAll like in rocketModel
            $planetData = $planet->selectPlanet($value);
            if(!$planetData) {
                return false;
            }
        
            return true;
        }, 'does not exist');

        //Creating Custom moon_name validator 
        Validator::addRule('moon_Name_Exists', function($field, $value, array $params, array $fields) use ($checkUpdate)  {
         
            if($value == NULL){
                return false;
            }
            $methodName = "selectMoonsSimple";
            
            $namerChecker = $this->checkExistingName($value, $methodName, $this,'moon_id', $field, $checkUpdate);
           
            if($checkUpdate){
                
                if($fields['moon_id'] != $namerChecker){
                    
                    return false;
                }
            }
            else{
                if(!$namerChecker){
                    return false;
                }
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

    }
    
}