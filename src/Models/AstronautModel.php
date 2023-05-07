<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\BaseModel;
use Vanier\Api\Helpers\ArrayHelper;


class AstronautModel extends BaseModel  {
    private $table_name = "astronaut";

    public function __construct() {
        parent::__construct();
    }

    /**
     * Select Astronauts from Database Based on Filters
     * @param array $filters Filters for Query
     * @param int $page Number of Current Page
     * @param int $page_size Size of Current Page
     * @return array Paginated Astronaut Result Set
     */
    public function selectAstronauts(array $filters = [], $page = null, $page_size = null) {
        // Set Page and Page Size Default Values If Params Null
        if (!$page) $page = 1;
        if (!$page_size) $page_size = 10;
        
        // Initialize Query Values Variable
        $query_values = [];

        // Base Statement
        $select = "SELECT a.*";
        $from = " FROM $this->table_name AS a";
        $where = " WHERE 1 ";
        $group_by = "";

        // Apply Filters If They Exist... 
        if (isset($filters["name"])) {
            $where .= "AND a.astronaut_name LIKE CONCAT('%', :name, '%') ";
            $query_values[":name"] = $filters["name"];
        }

        if (isset($filters["sex"])) {
            $where .= "AND a.astronaut_sex = :sex ";
            $query_values[":sex"] = $filters["sex"];
        }

        if (isset($filters["fromBirthYear"])) {
            $where .= "AND a.year_of_birth >= :fromBirthYear ";
            $query_values[":fromBirthYear"] = $filters["fromBirthYear"];
        }

        if (isset($filters["toBirthYear"])) {
            $where .= "AND a.year_of_birth <= :toBirthYear ";
            $query_values[":toBirthYear"] = $filters["toBirthYear"];
        }

        if (isset($filters["militaryStatus"])) {
            $filters["militaryStatus"] = $filters["militaryStatus"] == "true" ? 1 : 0;

            $where .= "AND a.military_status = :militaryStatus ";
            $query_values[":militaryStatus"] = $filters["militaryStatus"];
        }

        $sql = $select . $from . $where . $group_by;

        // Return Paginated Results
        $this->setPaginationOptions($page, $page_size);
        return $this->paginate($sql, $query_values);
    }

    /**
     * Select Astronaut from Database Based on Id
     * @param int $astronaut_id Id of Astronaut
     * @return array Astronaut at Id
     */
    public function selectAstronaut(int $astronaut_id) {
        // Base Statement
        $select = "SELECT a.*";
        $from = " FROM astronaut AS a";
        $where = " WHERE a.astronaut_id = :astronaut_id";

        $query_values[":astronaut_id"] = $astronaut_id;

        $sql = $select . $from . $where;
        
        return $this->run($sql, $query_values)->fetch();
    }

    public function selectAstronautByMission(int $mission_id){
        
        $select = "SELECT s.*, a.*";
        $from = " FROM astronaut AS a INNER JOIN mission_astronaut AS s ON s.astronaut_id = a.astronaut_id";
        $where = " WHERE s.mission_id = :mission_id AND 1";
        $group_by = "";

        $sql = $select . $from . $where . $group_by;

        return $this->run($sql, [":mission_id"=> $mission_id])->fetchAll();

    }

    /**
     * Insert Astronauts Into Database
     * @param array $data Stars to Insert
     * @return array Rows Inserted, Failed and/or Missing
     */
    public function insertAstronauts(array $data) {
        $rules["astronaut_name"] = ["required", ["lengthBetween", 1, 128]];
        $rules["astronaut_nationality"] = ["required", ["lengthBetween", 1, 32]];
        $rules["astronaut_sex"] =  ["required", ["in", ["male", "female"]]];
        $rules["year_of_birth"] = ["required", "integer", ["min", 0], ["max", 99999]];
        $rules["military_status"] = ["required", "integer", ["min", 0], ["max", 1]];

        // For Each Rocket...
        foreach($data as $astronaut) {
            $validator = new Validator($astronaut);
            $validator->mapFieldsRules($rules);

            // If Data Is valid...
            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($astronaut, ["astronaut_name", "astronaut_nationality", "astronaut_sex", "year_of_birth", "military_status"]);
                
                // Insert Star Into Database
                $last_id = $this->insert($this->table_name, $fields);

                if ($last_id != 0) {
                    $results["row_inserted"][] = $this->selectAstronaut($last_id);
                } else {
                    $results["rows_missing"][] = [...$astronaut, "errors" => "An error occured while inserting row."];
                }
            } else {
                $results["rows_failed"][] = [...$astronaut, "errors" => $validator->errors()];
            }
        }

        return $results;
    }

    /**
     * Update Astronauts Into Database
     * @param array $data Astronauts to Update
     * @return array Rows Deleted, Failed, and/or Missing Feeback
     */
    public function updateAstronauts($data) {
        $rules["astronaut_id"] = ["required"];
        $rules["astronaut_name"] = ["optional", ["lengthBetween", 1, 128]];
        $rules["astronaut_nationality"] = ["optional", ["lengthBetween", 1, 32]];
        $rules["astronaut_sex"] =  ["optional", ["in", ["male", "female"]]];
        $rules["year_of_birth"] = ["optional", "integer", ["min", 0], ["max", 99999]];
        $rules["military_status"] = ["optional", "integer", ["min", 0], ["max", 1]];

        foreach ($data as $astronaut) {
            $validator = new Validator($astronaut);
            $validator->mapFieldsRules($rules);

            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($astronaut, ["astronaut_name", "astronaut_nationality", "astronaut_sex", "year_of_birth", "military_status"]);

                // Update Astronaut Into Database
                $row_count = $this->update($this->table_name, $fields, ["astronaut_id" => $astronaut["astronaut_id"]]);

                if ($row_count != 0) {
                    $result["rows_affected"][] = $this->selectAstronaut($astronaut["astronaut_id"]);
                } else
                    $result["rows_missing"][] = [...$astronaut, "errors" => "An error occured while updating row or specified keys do not exist."];
            } else {
                $result["rows_failed"][] = [...$astronaut, "errors" => $validator->errors()];
            }
        }

        return $result;
    }
}