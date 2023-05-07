<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\BaseModel;
use Vanier\Api\Helpers\ArrayHelper;
use Vanier\Api\Helpers\Validator as HelpersValidator;

class RocketModel extends BaseModel  {
    private $table_name = "rocket";

    public function __construct() {
        parent::__construct();
    }

    /**
     * Select Rockets from Database Based on Filters
     * @param array $filters Filters for Query
     * @param int $page Number of Current Page
     * @param int $page_size Size of Current Page
     * @return array Paginated Rocket Result Set
     */
    public function selectRockets(array $filters = [], $page = null, $page_size = null) {
        // Set Page and Page Size Default Values If Params Null
        if (!$page) $page = 1;
        if (!$page_size) $page_size = 10;
        
        // Initialize Query Values Variable
        $query_values = [];

        // Base Statement
        $select = "SELECT r.*";
        $from = " FROM $this->table_name AS r";
        $where = " WHERE 1 ";
        $group_by = "";

        // Apply Filters If They Exist... 
        if (isset($filters["name"])) {
            $where .= "AND r.rocket_name LIKE CONCAT('%', :name, '%') ";
            $query_values[":name"] = $filters["name"];
        }

        if (isset($filters["company"])) {
            $where .= "AND r.company_name LIKE CONCAT('%', :company, '%') ";
            $query_values[":company"] = $filters["company"];
        }

        if (isset($filters["status"])) {
            $where .= "AND r.rocket_status = :status ";
            $query_values[":status"] = $filters["status"];
        }

        if (isset($filters["fromThrust"])) {
            $where .= "AND r.rocket_thrust >= :fromThrust ";
            $query_values[":fromThrust"] = $filters["fromThrust"];
        }

        if (isset($filters["toThrust"])) {
            $where .= "AND r.rocket_thrust <= :toThrust ";
            $query_values[":toThrust"] = $filters["toThrust"];
        }

        if (isset($filters["fromHeight"])) {
            $where .= "AND r.rocket_height >= :fromHeight ";
            $query_values[":fromHeight"] = $filters["fromHeight"];
        }

        if (isset($filters["toHeight"])) {
            $where .= "AND r.rocket_height <= :toHeight ";
            $query_values[":toHeight"] = $filters["toHeight"];
        }

        if (isset($filters["fromPrice"])) {
            $where .= "AND r.rocket_price >= :fromPrice ";
            $query_values[":fromPrice"] = $filters["fromPrice"];
        }

        if (isset($filters["toPrice"])) {
            $where .= "AND r.rocket_price <= :toPrice ";
            $query_values[":toPrice"] = $filters["toPrice"];
        }

        $sql = $select . $from . $where . $group_by;

        // Return Paginated Results
        $this->setPaginationOptions($page, $page_size);
        return $this->paginate($sql, $query_values);
    }

    /**
     * Select Rocket from Database Based on Id
     * @param int $rocket_id Id of Rocket
     * @return array Rocket at Id
     */
    public function selectRocket(int $rocket_id) {
        // Base Statement
        $select = "SELECT r.*";
        $from = " FROM $this->table_name AS r";
        $where = " WHERE r.rocket_id = :rocket_id";

        $query_values[":rocket_id"] = $rocket_id;

        $sql = $select . $from . $where;
        
        return $this->run($sql, $query_values)->fetchAll();
    }

    /**
     * Insert Rockets Into Database
     * @param array $data Rockets to Insert
     * @return array Rows Inserted, Failed and/or Missing
     */
    public function insertRockets(array $data) {
        $rules["rocket_name"] = ["required", ["lengthBetween", 1, 64]];
        $rules["company_name"] = ["required", ["lengthBetween", 1, 64]];
        $rules["rocket_status"] = ["required", ["in", ["Active", "Retired", "Planned"]]];
        $rules["rocket_thrust"] = ["optional", "integer", ["min", 0], ["max", 9999999]];
        $rules["rocket_height"] = ["optional", "numeric", ["min", 0], ["max", 999999]];
        $rules["rocket_price"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];

        // For Each Rocket...
        foreach($data as $rocket) {
            $validator = new Validator($rocket);
            $validator->mapFieldsRules($rules);

            // If Data Is valid...
            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($rocket, ["rocket_name", "company_name", "rocket_status", "rocket_thrust", "rocket_height", "rocket_price"]);
                
                // Insert Rocket Into Database
                $last_id = $this->insert($this->table_name, $fields);

                if ($last_id != 0) {
                    $results["row_inserted"][] = $this->selectRocket($last_id);
                } else {
                    $results["rows_missing"][] = [...$rocket, "errors" => "An error occured while inserting row."];
                }
            } else {
                $results["rows_failed"][] = [...$rocket, "errors" => $validator->errors()];
            }
        }

        return $results;
    }

     /**
     * Update Rockets Into Database
     * @param array $data Rockets to Update
     * @return array Rows Deleted, Failed, and/or Missing Feeback
     */
    public function updateRockets($data) {
        $rules["rocket_id"] = ["required"];
        $rules["rocket_name"] = ["optional", ["lengthBetween", 1, 64]];
        $rules["company_name"] = ["optional", ["lengthBetween", 1, 64]];
        $rules["rocket_status"] = ["optional", ["in", ["Active", "Retired", "Planned"]]];
        $rules["rocket_thrust"] = ["optional", "integer", ["min", 0], ["max", 9999999]];
        $rules["rocket_height"] = ["optional", "numeric", ["min", 0], ["max", 999999]];
        $rules["rocket_price"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];

        foreach ($data as $rocket) {
            $validator = new Validator($rocket);
            $validator->mapFieldsRules($rules);

            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($rocket, ["rocket_name", "company_name", "rocket_status", "rocket_thrust", "rocket_height", "rocket_price"]);

                // Update Astronaut Into Database
                $row_count = $this->update($this->table_name, $fields, ["rocket_id" => $rocket["rocket_id"]]);

                if ($row_count != 0) {
                    $result["rows_affected"][] = $this->selectRocket($rocket["rocket_id"]);
                } else
                    $result["rows_missing"][] = [...$rocket, "errors" => "An error occured while updating row or specified keys do not exist."];
            } else {
                $result["rows_failed"][] = [...$rocket, "errors" => $validator->errors()];
            }
        }

        return $result;
    }
}