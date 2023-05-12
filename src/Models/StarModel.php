<?php

namespace Vanier\Api\Models;

use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\BaseModel;
use Vanier\Api\Helpers\ArrayHelper;

class StarModel extends BaseModel  {
    private $table_name = "star";

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
    public function selectStars(array $filters = [], $page = null, $page_size = null) {
        
        // Set Page and Page Size Default Values If Params Null
        if (!$page) $page = 1;
        if (!$page_size) $page_size = 10;

        $query_values = [];

        // Base Statement
        $select = "SELECT s.*";
        $from = " FROM $this->table_name AS s";
        $where = " WHERE 1 ";
        $group_by = "";

        if (isset($filters["starName"])) {
            $where .= " AND s.star_name LIKE CONCAT('%', :starName, '%') ";
            $query_values[":starName"] = $filters["starName"];
        }

        if (isset($filters["temperature"])) {
            $where .= " AND s.effective_temperature = :temperature ";
            $query_values[":temperature"] = $filters["temperature"];
        }

        if (isset($filters["fromRadius"])) {
            $where .= " AND s.radius >= :fromRadius ";
            $query_values[":fromRadius"] = $filters["fromRadius"];
        }
        
        if (isset($filters["toRadius"])) {
            $where .= " AND s.radius <= :toRadius ";
            $query_values[":toRadius"] = $filters["toRadius"];
        }

        if (isset($filters["fromMass"])) {
            $where .= " AND s.mass >= :fromMass ";
            $query_values[":fromMass"] = $filters["fromMass"];
        }
        
        if (isset($filters["toMass"])) {
            $where .= " AND s.mass <= :toMass ";
            $query_values[":toMass"] = $filters["toMass"];
        }

        if (isset($filters["fromGravity"])) {
            $where .= " AND s.surface_gravity >= :fromGravity ";
            $query_values[":fromGravity"] = $filters["fromGravity"];
        }
        
        if (isset($filters["toGravity"])) {
            $where .= " AND s.surface_gravity <= :toGravity ";
            $query_values[":toGravity"] = $filters["toGravity"];
        }

        $sql = $select . $from . $where . $group_by;

        // Return Paginated Results
        $this->setPaginationOptions($page, $page_size);
        return $this->paginate($sql, $query_values);
    }

    public function selectStar(int $star_id){
        
        // Base Statement
        $select = "SELECT s.*";
        $from = " FROM $this->table_name AS s";
        $where = " WHERE star_id =:star_id AND 1 ";
        $group_by = "";

        $sql = $select . $from . $where . $group_by;

        return $this->run($sql, [":star_id"=> $star_id])->fetch();
    }

    public function selectStarsSimple() {
        // Set Page and Page Size Default Values If Params Null

        // Base Statement
        $select = "SELECT p.*";
        $from = " FROM $this->table_name AS p";
        $where = " WHERE 1 ";
        $group_by = "";


        $sql = $select . $from . $where . $group_by;

        return $this->run($sql);
    }

    /**
     * Insert Stars Into Database
     * @param array $data Stars to Insert
     * @return array Rows Inserted, Failed and/or Missing
     */
    public function insertStars(array $data) {

        $this->createValidators(false);

        $rules["star_name"] = ["required", ["lengthBetween", 1, 64], ["star_Name_Exists"]];
        $rules["effective_temperature"] = ["optional", "numeric", ["min", 0], ["max", 999999]];
        $rules["radius"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["mass"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["surface_gravity"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];

        // For Each Star...
        foreach($data as $star) {
            $validator = new Validator($star);
            $validator->mapFieldsRules($rules);

            // If Data Is valid...
            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($star, ["star_name", "effective_temperature", "radius", "mass", "surface_gravity"]);
                
                // Insert Star Into Database
                $last_id = $this->insert($this->table_name, $fields);

                if ($last_id != 0) {
                    $results["row_inserted"][] = $this->selectStar($last_id);
                } else {
                    $results["rows_missing"][] = [...$star, "errors" => "An error occured while inserting row."];
                }
            } else {
                $results["rows_failed"][] = [...$star, "errors" => $validator->errors()];
            }
        }

        return $results;
    }

    public function updateStars($data) {

        $this->createValidators(true);

        $rules["star_id"] = ["required"];
        $rules["star_name"] = ["required", ["lengthBetween", 1, 64], ["star_Name_Exists"]];
        $rules["effective_temperature"] = ["optional", "numeric", ["min", 0], ["max", 999999]];
        $rules["radius"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["mass"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["surface_gravity"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];

        foreach ($data as $star) {

            $validator = new Validator($star);
            $validator->mapFieldsRules($rules);

            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($star, ["star_name", "effective_temperature", "radius", "mass", "surface_gravity"]);

                // Update Astronaut Into Database
                if (count($fields) != 0) {
                    $row_count = $this->update($this->table_name, $fields, ["star_id" => $star["star_id"]]);
                    if ($row_count != 0) {
                        $result["rows_affected"][] = $this->selectStar($star["star_id"]);
                    } else
                        $result["rows_missing"][] = [...$star, "errors" => "An error occurred while updating row or specified keys do not exist."];
                }
                else
                    $result["rows_failed"][] = [...$star, "errors" => "There must be at least one field to update a row."];
            } else {
                $result["rows_failed"][] = [...$star, "errors" => $validator->errors()];
            }
        }
        return $result;
    }

    private function createValidators($checkUpdate) {
        

        //Creating Custom planet_name validator 
        Validator::addRule('star_Name_Exists', function($field, $value, array $params, array $fields) use ($checkUpdate)  {
         
            if($value == NULL){
                return false;
            }
            $methodName = "selectStarsSimple";
            
            $namerChecker = $this->checkExistingName($value, $methodName, $this,'star_id', $field, $checkUpdate);
           
            if($checkUpdate){
                
                if($fields['star_id'] != $namerChecker){
                    
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