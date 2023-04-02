<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Validations\Validator;
use Vanier\Api\Models\BaseModel;
use Vanier\Api\Helpers\ArrayHelper;

class PlanetModel extends BaseModel  {
    private $table_name = "planet";

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
    public function selectPlanets(array $filters = [], $page = null, $page_size = null) {
        // Set Page and Page Size Default Values If Params Null
        if (!$page) $page = 1;
        if (!$page_size) $page_size = 10;

        $query_values = [];

        // Base Statement
        $select = "SELECT p.*";
        $from = " FROM $this->table_name AS p";
        $where = " WHERE 1 ";
        $group_by = "";

        if (isset($filters["planetName"])) {
            $where .= " AND p.planet_name LIKE CONCAT('%', :planet_name, '%')";
            $query_values[":planet_name"] = $filters["planetName"];
        }

        if (isset($filters["color"])) {
            
            $parts = explode(',', $filters["color"]);
            foreach ($parts as $key => $values) {
                $where .= " AND p.color LIKE CONCAT('%', :color, '%')";
                $query_values[":color"] = $values;
            }
           
        }

        if (isset($filters["star_id"])) {
            $where .= " AND p.star_id = :star_id";
            $query_values[":star_id"] = $filters["star_id"];
        }

        $sql = $select . $from . $where . $group_by;

        // Return Paginated Results
        $this->setPaginationOptions($page, $page_size);
        return $this->paginate($sql, $query_values);
    }


    public function selectPlanet(int $planet_id){
        
        // Base Statement
        $select = "SELECT s.*";
        $from = " FROM $this->table_name AS s";
        $where = " WHERE planet_id =:planet_id AND 1 ";
        $group_by = "";

        $sql = $select . $from . $where . $group_by;

        return $this->run($sql, [":planet_id"=> $planet_id])->fetch();
    }
}