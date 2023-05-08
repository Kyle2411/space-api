<?php

namespace Vanier\Api\Models;

use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\BaseModel;
use Vanier\Api\Helpers\ArrayHelper;

class AsteroidModel extends BaseModel  {
    private $table_name = "asteroid";

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
    public function selectAsteroids(array $filters = [], $page = null, $page_size = null) {
        
        // Set Page and Page Size Default Values If Params Null
        if (!$page) $page = 1;
        if (!$page_size) $page_size = 10;

        $query_values = [];

        // Base Statement
        $select = "SELECT a.*";
        $from = " FROM $this->table_name AS a";
        $where = " WHERE 1 ";
        $group_by = "";

        if (isset($filters["asteroidName"])) {
            $where .= " AND a.asteroid_name LIKE CONCAT('%', :asteroidName, '%') ";
            $query_values[":asteroidName"] = $filters["asteroidName"];
        }

        if (isset($filters["danger"])) {
            $where .= " AND a.asteroid_dangerous = :danger ";
            $query_values[":danger"] = $filters["danger"];
        }

        if (isset($filters["fromMinDiameter"])) {
            $where .= " AND a.asteroid_min_diameter >= :fromMinDiameter ";
            $query_values[":fromMinDiameter"] = $filters["fromMinDiameter"];
        }
        
        if (isset($filters["toMaxDiameter"])) {
            $where .= " AND a.asteroid_max_diameter <= :toMaxDiameter ";
            $query_values[":toMaxDiameter"] = $filters["toMaxDiameter"];
        }

        if (isset($filters["fromMagnitude"])) {
            $where .= " AND a.asteroid_magnitude >= :fromMagnitude ";
            $query_values[":fromMagnitude"] = $filters["fromMagnitude"];
        }
        
        if (isset($filters["toMagnitude"])) {
            $where .= " AND a.asteroid_magnitude <= :toMagnitude ";
            $query_values[":toMagnitude"] = $filters["toMagnitude"];
        }

        if (isset($filters["designation"])) {
            $where .= " AND a.asteroid_designation = :designation ";
            $query_values[":designation"] = $filters["designation"];
        }

        if (isset($filters["monitored"])) {
            $where .= " AND a.sentry_monitored = :monitored ";
            $query_values[":monitored"] = $filters["monitored"];
        }

        $sql = $select . $from . $where . $group_by;

        // Return Paginated Results
        $this->setPaginationOptions($page, $page_size);
        return $this->paginate($sql, $query_values);
    }

    public function selectAsteroid(int $asteroid_id){
        
        // Base Statement
        $select = "SELECT a.*";
        $from = " FROM $this->table_name AS a";
        $where = " WHERE asteroid_id =:asteroid_id AND 1 ";
        $group_by = "";

        $sql = $select . $from . $where . $group_by;

        return $this->run($sql, [":asteroid_id"=> $asteroid_id])->fetch();
    }

    /**
     * Insert Asteroids Into Database
     * @param array $data Stars to Insert
     * @return array Rows Inserted, Failed and/or Missing
     */
    public function insertAsteroids(array $data) {
        $rules["asteroid_name"] = ["optional", ["lengthBetween", 1, 64]];
        $rules["asteroid_designation"] = ["required", "numeric", ["min", 0], ["max", 999999]];
        $rules["sentry_monitored"] = ["optional", "integer", ["min", 0], ["max", 1]];
        $rules["asteroid_dangerous"] = ["optional", "integer", ["min", 0], ["max", 1]];
        $rules["asteroid_magnitude"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["asteroid_min_diameter"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["asteroid_max_diameter"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];

        // For Each Rocket...
        foreach($data as $asteroid) {
            $validator = new Validator($asteroid);
            $validator->mapFieldsRules($rules);

            // If Data Is valid...
            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($asteroid, ["asteroid_name", "asteroid_designation", "sentry_monitored", "asteroid_dangerous", "asteroid_magnitude", "asteroid_min_diameter", "asteroid_max_diameter"]);
                
                // Insert Star Into Database
                $last_id = $this->insert($this->table_name, $fields);

                if ($last_id != 0) {
                    $results["row_inserted"][] = $this->selectAsteroid($last_id);
                } else {
                    $results["rows_missing"][] = [...$asteroid, "errors" => "An error occured while inserting row."];
                }
            } else {
                $results["rows_failed"][] = [...$asteroid, "errors" => $validator->errors()];
            }
        }

        return $results;
    }

    public function updateAsteroids($data) {

        //$this->createValidators(true);
        $rules["asteroid_id"] = ["required"];
        $rules["asteroid_name"] = ["optional", ["lengthBetween", 1, 64]];
        $rules["asteroid_designation"] = ["required", "numeric", ["min", 0], ["max", 999999]];
        $rules["sentry_monitored"] = ["optional", "integer", ["min", 0], ["max", 1]];
        $rules["asteroid_dangerous"] = ["optional", "integer", ["min", 0], ["max", 1]];
        $rules["asteroid_magnitude"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["asteroid_min_diameter"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["asteroid_max_diameter"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];

        foreach ($data as $asteroid) {

            $validator = new Validator($asteroid);
            $validator->mapFieldsRules($rules);

            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($asteroid, ["asteroid_name", "asteroid_designation", "sentry_monitored", "asteroid_dangerous", "asteroid_magnitude", "asteroid_min_diameter", "asteroid_max_diameter"]);

                // Update Astronaut Into Database
                if (count($fields) != 0) {
                    $row_count = $this->update($this->table_name, $fields, ["asteroid_id" => $asteroid["asteroid_id"]]);
                    if ($row_count != 0) {
                        $result["rows_affected"][] = $this->selectAsteroid($asteroid["asteroid_id"]);
                    } else
                        $result["rows_missing"][] = [...$asteroid, "errors" => "An error occurred while updating row or specified keys do not exist."];
                }
                else
                    $result["rows_failed"][] = [...$asteroid, "errors" => "There must be at least one field to update a row."];
            } else {
                $result["rows_failed"][] = [...$asteroid, "errors" => $validator->errors()];
            }
        }
        return $result;
    }

    /**
     * Delete Asteroids In Database
     * @param array $data Asteroids to Delete
     * @return array Rows Deleted, Failed, and/or Missing Feedback
     */
    public function deleteAsteroids($data) {
        foreach ($data as $asteroid_id) {
            if (is_int($asteroid_id)) {
                // Delete Asteroid in Database
                $row_count = $this->delete($this->table_name, ["asteroid_id" => $asteroid_id]);
                
                if ($row_count > 0) {
                    $result["rows_deleted"][] = ["asteroid_id" => $asteroid_id];
                } else
                    $result["rows_missing"][] = ["asteroid_id" => $asteroid_id, "errors" => "An error occured while deleting row or row doesn't exist."];
            } else {
                $result["rows_failed"][] = ["data" => $asteroid_id, "errors" => "Request body value must be an integer."];
            }
        }
    
        return $result;
    }
}