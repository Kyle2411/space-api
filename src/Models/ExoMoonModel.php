<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\BaseModel;
use Vanier\Api\Helpers\ArrayHelper;
use Vanier\Api\Helpers\Validator as HelpersValidator;


/**
 * Summary of ExoMoonModel
 */
class ExoMoonModel extends BaseModel  {
    private $table_name = "exomoon";

    /**
     * Summary of __construct
     */
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
    public function selectExoMoons(array $filters = [], $page = null, $page_size = null) {
        // Set Page and Page Size Default Values If Params Null
        if (!$page) $page = 1;
        if (!$page_size) $page_size = 10;

        $query_values = [];

        // Base Statement
        $select = "SELECT exM.*";
        $from = " FROM $this->table_name AS exM";
        $where = " WHERE 1 ";
        $group_by = "";

        if (isset($filters["exoMoonName"])) {
            $where .= "AND exM.exomoon_name LIKE CONCAT('%', :exomoon_name, '%') ";
            $query_values[":exomoon_name"] = $filters["exoMoonName"];
        }

        if (isset($filters["exoMass"])) {
            $where .= "AND exM.mass = :mass ";
            $query_values[":mass"] = $filters["exoMass"];
        }

        if (isset($filters["orbitalPeriodDays"])) {
            $where .= "AND exM.orbital_period_days = :orbital_period_days ";
            $query_values[":orbital_period_days"] = $filters["orbitalPeriodDays"];
        }

        if (isset($filters["discoveryMethod"])) {
            $where .= "AND exM.discovery_method >= :discovery_method ";
            $query_values[":discovery_method"] = $filters["discoveryMethod"];
        }

        $sql = $select . $from . $where . $group_by;

        // Return Paginated Results
        $this->setPaginationOptions($page, $page_size);
        return $this->paginate($sql, $query_values);
    }

    public function selectExoMoon(int $exomoon_id){
        
        // Base Statement
        $select = "SELECT exM.*";
        $from = " FROM $this->table_name AS exM";
        $where = " WHERE exM.exomoon_id =:exomoon_id AND 1 ";
        $group_by = "";

        $sql = $select . $from . $where . $group_by;

        return $this->run($sql, [":exomoon_id"=> $exomoon_id])->fetch();
    }

    public function selectExoMoonByExoPlanet(int $exoPlanet_id){
        
        // Base Statement
        $select = "SELECT m.*";
        $from = " FROM exomoon AS m";
        $where = " WHERE exoplanet_id =:exoplanet_id AND 1 ";
        $group_by = "";

        $sql = $select . $from . $where . $group_by;
        
        return $this->run($sql, [":exoplanet_id"=> $exoPlanet_id])->fetchAll();
    }

    public function selectExoMoonsSimple() {
        // Set Page and Page Size Default Values If Params Null

        // Base Statement
        $select = "SELECT exM.*";
        $from = " FROM $this->table_name AS exM";
        $where = " WHERE 1 ";
        $group_by = "";


        $sql = $select . $from . $where . $group_by;

        return $this->run($sql);
    }

    public function selectExoMoonsForName() {
        // Set Page and Page Size Default Values If Params Null

        // Base Statement
        $select = "SELECT exM.exomoon_name";
        $from = " FROM $this->table_name AS exM";
        $where = " WHERE 1 ";
        $group_by = "";


        $sql = $select . $from . $where . $group_by;

        return $this->run($sql);
    }

    /**
     * Delete Exomoons In Database
     * @param array $data Exomoons to Delete
     * @return array Rows Deleted, Failed, and/or Missing Feeback
     */
    public function deleteExomoons($data) {
        foreach ($data as $exomoon_id) {
            if (is_int($exomoon_id)) {
                // Update Customer in Database
                $row_count = $this->delete($this->table_name, ["exomoon_id" => $exomoon_id]);
                
                if ($row_count > 0) {
                    $result["rows_deleted"][] = ["exomoon_id" => $exomoon_id];
                } else
                    $result["rows_missing"][] = ["exomoon_id" => $exomoon_id, "errors" => "An error occured while deleting row or row doesn't exist."];
            } else {
                $result["rows_failed"][] = ["data" => $exomoon_id, "errors" => "Request body value must be an integer."];
            }
        }
    
        return $result;
    }

    /**
     * Delete Exoplanet Exomoons In Database
     * @param array $exoplanet_id Id of Exoplanet to Delete Exomoons
     * @return array Rows Deleted, Failed, and/or Missing Feeback
     */
    public function deleteExoplanetExomoons($exoplanet_id) {
        if (is_int($exoplanet_id)) {
            // Delete Exoplanet in Database
            $row_count = $this->delete($this->table_name, ["exoplanet_id" => $exoplanet_id]);
            
            if ($row_count > 0) {
                $result["rows_deleted"][] = ["exoplanet_id" => $exoplanet_id];
            } else
                $result["rows_missing"][] = ["exoplanet_id" => $exoplanet_id, "errors" => "An error occured while deleting row or row doesn't exist."];
        } else {
            $result["rows_failed"][] = ["data" => $exoplanet_id, "errors" => "Request body value must be an integer."];
        }
    
        return $result;
    }

     /**
     * Summary of createExoMoon
     * @param array $actor
     * @return bool|string
     */
    public function createExoMoon(array $data)
    {
        $this->createValidators(false);

        $rules["exomoon_name"] = ["required", ["lengthBetween", 1, 64], ["exomoon_Name_Exists"]];
        $rules["exoplanet_id"] = ["required", "numeric", ["min", 0], ["max", 999999], ["exoplanetExists"]];
        $rules["discovery_method"] =  ["required", ["in", ["Radial Velocity","Imaging","Pulsation Timing Variations","Transit","Eclipse Timing Variations","Microlensing","Transit Timing Variations","Pulsation Timing","Disk Kinematics","Orbital Brightness Modulation"]]];
        $rules["orbital_period_days"] = ["optional", "numeric", ["min", 0], ["max", 999999]];
        $rules["mass"] = ["optional", "numeric", ["min", 0], ["max", 999999]];

        // for each exoMoon
        foreach ($data as $exoMoon) {
            $validator = new Validator($exoMoon);
            $validator->mapFieldsRules($rules);

            // If Data Is valid...
            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($exoMoon, ["exomoon_name", "exoplanet_id", "discovery_method", "orbital_period_days", "mass"]);
                
                // Insert Rocket Into Database
                $last_id = $this->insert($this->table_name, $fields);

                if ($last_id != 0) {
                    $results["row_inserted"][] = $this->selectExoMoon($last_id);
                } else {
                    $results["rows_missing"][] = [...$exoMoon, "errors" => "An error occured while inserting row."];
                }
            } else {
                $results["rows_failed"][] = [...$exoMoon, "errors" => $validator->errors()];
            }
        }
        return $results;
    }


    /**
     * Summary of updateExoMoons
     * @param mixed $data
     * @return array
     */
    public function updateExoMoons($data) {

        $this->createValidators(true);


        $rules["exomoon_id"] = ["required"];
        $rules["exoplanet_id"] = ["required", "numeric", ["min", 0], ["max", 99999999], ["exoplanetExists"]];
        $rules["exomoon_name"] = ["required", ["lengthBetween", 1, 128], ["exomoon_Name_Exists"]];
        $rules["discovery_method"] =  ["required", ["in", ["Radial Velocity","Imaging","Pulsation Timing Variations","Transit","Eclipse Timing Variations","Microlensing","Transit Timing Variations","Pulsation Timing","Disk Kinematics","Orbital Brightness Modulation"]]];
        $rules["orbital_period_days"] = ["optional", "numeric", ["min", 0], ["max", 999999]];
        $rules["mass"] = ["optional", "numeric", ["min", 0], ["max", 999999]];
        foreach ($data as $exomoon) {

            $validator = new Validator($exomoon);
            $validator->mapFieldsRules($rules);

            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($exomoon, ["exoplanet_id", "exomoon_name", "discovery_method", "orbital_period_days", "moon_density"]);
               
                // Update Astronaut Into Database
                if (count($fields) != 0) {
                    $row_count = $this->update($this->table_name, $fields, ["exomoon_id" => $exomoon["exomoon_id"]]);
                    if ($row_count != 0) {
                        $result["rows_affected"][] = $this->selectExoMoon($exomoon["exomoon_id"]);
                    } else
                        $result["rows_missing"][] = [...$exomoon, "errors" => "An error occurred while updating row or specified keys do not exist."];
                }
                else
                    $result["rows_failed"][] = [...$exomoon, "errors" => "There must be at least one field to update a row."];
            } else {
                $result["rows_failed"][] = [...$exomoon, "errors" => $validator->errors()];
            }
        }

        return $result;
    }


    /**
     * Summary of createValidators
     * @param mixed $checkUpdate
     * @return void
     */
    private function createValidators($checkUpdate) {
        //Creating Custom planet_id validator
        Validator::addRule('exoplanetExists', function($field, $value, array $params, array $fields) {
            $min = $params[0] ?? null;
            $max = $params[1] ?? null;
        
            if (!is_numeric($value) || ($min !== null && $value < $min) || ($max !== null && $value > $max)) {
                return false;
            }
        
            $exoPlanet = new ExoPlanetModel();
            // Might cause a problem becasue it is fetch and not fetchAll like in rocketModel
            $exoPlanetData = $exoPlanet->selectexoPlanet($value);
            if(!$exoPlanetData) {
                return false;
            }
        
            return true;
        }, 'does not exist');

        //Creating Custom moon_name validator 
        Validator::addRule('exomoon_Name_Exists', function($field, $value, array $params, array $fields) use ($checkUpdate)  {
         
            $methodName = "selectExoMoonsSimple";
            
            $namerChecker = $this->checkExistingName($value, $methodName, $this,'exomoon_name', $field, $checkUpdate);
           
            if($checkUpdate){
                
                if($fields['exomoon_name'] != $namerChecker){
                    
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