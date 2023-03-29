<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Validations\Validator;
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

        // Apply Filters If They Exist...
        /*
        if (isset($filters["first_name"])) {
            $where .= "AND a.first_name LIKE CONCAT('%', :first_name, '%') ";
            $query_values[":first_name"] = $filters["first_name"];
        }

        if (isset($filters["last_name"])) {
            $where .= "AND a.last_name LIKE CONCAT('%', :last_name, '%') ";
            $query_values[":last_name"] = $filters["last_name"];
        }
        */

        $sql = $select . $from . $where . $group_by;

        // Return Paginated Results
        $this->setPaginationOptions($page, $page_size);
        return $this->paginate($sql, $query_values);
    }
}