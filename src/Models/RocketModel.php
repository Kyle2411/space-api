<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Validations\Validator;
use Vanier\Api\Models\BaseModel;
use Vanier\Api\Helpers\ArrayHelper;

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
        
        return $this->run($sql, $query_values)->fetch();
    }
}