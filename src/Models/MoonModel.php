<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Validations\Validator;
use Vanier\Api\Models\BaseModel;
use Vanier\Api\Helpers\ArrayHelper;

class MoonModel extends BaseModel  {
    private $table_name = "moon";

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
    public function selectMoons(array $filters = [], $page = null, $page_size = null) {
        // Set Page and Page Size Default Values If Params Null
        if (!$page) $page = 1;
        if (!$page_size) $page_size = 10;

        $query_values = [];

        // Base Statement
        $select = "SELECT m.*";
        $from = " FROM $this->table_name AS m";
        $where = " WHERE 1 ";
        $group_by = "";

        if (isset($filters["moonName"])) {
            $where .= "AND m.moon_name LIKE CONCAT('%', :moon_name, '%') ";
            $query_values[":moon_name"] = $filters["moonName"];
        }

        if (isset($filters["moonMass"])) {
            $where .= "AND m.moon_mass = :moon_mass ";
            $query_values[":moon_mass"] = $filters["moonMass"];
        }

        if (isset($filters["fromMoonRadius"])) {
            $where .= "AND m.moon_radius >= :from_moon_radius ";
            $query_values[":from_moon_radius"] = $filters["fromMoonRadius"];
        }

        if (isset($filters["toMoonRadius"])) {
            $where .= "AND m.moon_radius <= :to_moon_radius ";
            $query_values[":to_moon_radius"] = $filters["toMoonRadius"];
        }

        if (isset($filters["moonDensity"])) {
            $where .= "AND m.moon_desity = :moon_desity ";
            $query_values[":moon_desity"] = $filters["moonDensity"];
        }

        $sql = $select . $from . $where . $group_by;

        // Return Paginated Results
        $this->setPaginationOptions($page, $page_size);
        return $this->paginate($sql, $query_values);
    }

    public function selectMoon(int $moon_id){
        
        // Base Statement
        $select = "SELECT m.*";
        $from = " FROM $this->table_name AS m";
        $where = " WHERE moon_id =:moon_id AND 1 ";
        $group_by = "";

        $sql = $select . $from . $where . $group_by;

        return $this->run($sql, [":moon_id"=> $moon_id])->fetch();
    }
}