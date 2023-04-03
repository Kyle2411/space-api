<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Validations\Validator;
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
}