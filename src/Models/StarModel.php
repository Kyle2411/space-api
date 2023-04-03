<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Validations\Validator;
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
}