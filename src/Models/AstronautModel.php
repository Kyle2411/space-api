<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Validations\Validator;
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
        $from = " FROM astronaut AS a";
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
}