<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\BaseModel;
use Vanier\Api\Helpers\ArrayHelper;

class ExoPlanetModel extends BaseModel  {
    private $table_name = "exoplanet";

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
    public function selectExoPlanets(array $filters = [], $page = null, $page_size = null) {
        // Set Page and Page Size Default Values If Params Null
        if (!$page) $page = 1;
        if (!$page_size) $page_size = 10;

        $query_values = [];

        // Base Statement
        $select = "SELECT p.*";
        $from = " FROM $this->table_name AS p";
        $where = " WHERE 1 ";
        $group_by = "";

        if (isset($filters["exoPlanetName"])) {
            $where .= " AND p.exoplanet_name LIKE CONCAT('%', :exoplanet_name, '%')";
            $query_values[":exoplanet_name"] = $filters["exoPlanetName"];
        }

        if (isset($filters["discoveryMethod"])) {
            $where .= " AND p.discovery_method LIKE CONCAT('%', :discovery_method, '%')";
            $query_values[":discovery_method"] = $filters["discoveryMethod"];
        }

        if (isset($filters["fromDiscoveryYear"])) {
            $where .= " AND p.discovery_year >= :fromDiscoveryYear";
            $query_values[":fromDiscoveryYear"] = $filters["fromDiscoveryYear"];
        }

        if (isset($filters["toDiscoveryYear"])) {
            $where .= " AND p.discovery_year <= :toDiscoveryYear";
            $query_values[":toDiscoveryYear"] = $filters["toDiscoveryYear"];
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

    public function selectExoPlanetsSimple() {
        // Set Page and Page Size Default Values If Params Null

        // Base Statement
        $select = "SELECT p.*";
        $from = " FROM $this->table_name AS p";
        $where = " WHERE 1 ";
        $group_by = "";


        $sql = $select . $from . $where . $group_by;

        return $this->run($sql);
    }

    public function selectExoPlanet(int $exoPlanet_id){
        
        // Base Statement
        $select = "SELECT s.*";
        $from = " FROM $this->table_name AS s";
        $where = " WHERE exoplanet_id =:exoplanet_id AND 1 ";
        $group_by = "";

        $sql = $select . $from . $where . $group_by;

        return $this->run($sql, [":exoplanet_id"=> $exoPlanet_id])->fetch();
    }

    /**
     * Insert ExoPlanets Into Database
     * @param array $data ExoPlanets to Insert
     * @return array Rows Inserted, Failed and/or Missing
     */
    public function insertExoPlanets(array $data) {

        $this->createValidators(false);

        $rules["star_id"] = ["required", "numeric", ["min", 0], ["max", 99999999], ["starExists"]];
        $rules["exoplanet_name"] = ["required", ["lengthBetween", 1, 64], ["exoplanet_Name_Exists"]];
        $rules["discovery_method"] = ["optional", ["in", ["Radial Velocity", "Imaging", "Pulsation Timing Variations", "Transit", "Eclipse Timing Variations", "Microlensing", "Transit Timing Variations", "Pulsation Timing", "Disk Kinematics", "Orbital Brightness Modulation"]]];
        $rules["discovery_year"] = ["required", "numeric", ["min", 0], ["max", 9999]];
        $rules["orbital_period_days"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["mass"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];

        // For Each Rocket...
        foreach($data as $exoPlanet) {


            $validator = new Validator($exoPlanet);
            $validator->mapFieldsRules($rules);

            // If Data Is valid...
            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($exoPlanet, ["star_id", "exoplanet_name", "discovery_method", "discovery_year", "orbital_period_days", "mass"]);
                
                // Insert Star Into Database
                $last_id = $this->insert($this->table_name, $fields);

                if ($last_id != 0) {
                    $results["row_inserted"][] = $this->selectExoPlanet($last_id);
                } else {
                    $results["rows_missing"][] = [...$exoPlanet, "errors" => "An error occured while inserting row."];
                }
            } else {
                $results["rows_failed"][] = [...$exoPlanet, "errors" => $validator->errors()];
            }
        }

        return $results;
    }

    /**
     * Update ExoPlanets Into Database
     * @param array $data ExoPlanets to Update
     * @return array Rows Deleted, Failed, and/or Missing Feedback
     */
    public function updateExoPlanets($data) {

        $this->createValidators(true);


        $rules["exoplanet_id"] = ["required"];
        $rules["star_id"] = ["required", "numeric", ["min", 0], ["max", 99999999], ["starExists"]];
        $rules["exoplanet_name"] = ["required", ["lengthBetween", 1, 64], ["exoplanet_Name_Exists"]];
        $rules["discovery_method"] = ["optional", ["in", ["Radial Velocity", "Imaging", "Pulsation Timing Variations", "Transit", "Eclipse Timing Variations", "Microlensing", "Transit Timing Variations", "Pulsation Timing", "Disk Kinematics", "Orbital Brightness Modulation"]]];
        $rules["discovery_year"] = ["required", "numeric", ["min", 0], ["max", 9999]];
        $rules["orbital_period_days"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["mass"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];

        foreach ($data as $exoPlanet) {

            $validator = new Validator($exoPlanet);
            $validator->mapFieldsRules($rules);

            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($exoPlanet, ["star_id", "exoplanet_name", "discovery_method", "discovery_year", "orbital_period_days", "mass"]);
               
                // Update Astronaut Into Database
                if (count($fields) != 0) {
                    $row_count = $this->update($this->table_name, $fields, ["exoplanet_id" => $exoPlanet["exoplanet_id"]]);
                    if ($row_count != 0) {
                        $result["rows_affected"][] = $this->selectExoPlanet($exoPlanet["exoplanet_id"]);
                    } else
                        $result["rows_missing"][] = [...$exoPlanet, "errors" => "An error occurred while updating row or specified keys do not exist."];
                }
                else
                    $result["rows_failed"][] = [...$exoPlanet, "errors" => "There must be at least one field to update a row."];
            } else {
                $result["rows_failed"][] = [...$exoPlanet, "errors" => $validator->errors()];
            }
        }

        return $result;
    }

    /**
     * Delete Exoplanets In Database
     * @param array $data Exoplanets to Delete
     * @return array Rows Deleted, Failed, and/or Missing Feeback
     */
    public function deleteExoplanets($data) {
        foreach ($data as $exoplanet_id) {
            if (is_int($exoplanet_id)) {
                // Delete Exoplanet Exomoons in Database
                $row_count = $this->delete("exomoon", ["exoplanet_id" => $exoplanet_id]);

                // Delete Exoplanet in Database
                $row_count = $this->delete($this->table_name, ["exoplanet_id" => $exoplanet_id]);
                
                if ($row_count > 0) {
                    $result["rows_deleted"][] = ["exoplanet_id" => $exoplanet_id];
                } else
                    $result["rows_missing"][] = ["exoplanet_id" => $exoplanet_id, "errors" => "An error occured while deleting row or row doesn't exist."];
            } else {
                $result["rows_failed"][] = ["data" => $exoplanet_id, "errors" => "Request body value must be an integer."];
            }
        }
    
        return $result;
    }


    private function createValidators($checkUpdate) {
        //Creating Custom star_id validator
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

        //Creating Custom planet_name validator 
        Validator::addRule('exoplanet_Name_Exists', function($field, $value, array $params, array $fields) use ($checkUpdate)  {
         
            $methodName = "selectExoPlanetsSimple";
            
            $namerChecker = $this->checkExistingName($value, $methodName, $this,'exoplanet_id', $field, $checkUpdate);
           
            if($checkUpdate){
                
                if($fields['exoplanet_id'] != $namerChecker){
                    
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