<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Validations\Validator;
use Vanier\Api\Models\BaseModel;
use Vanier\Api\Helpers\ArrayHelper;

class ExoMoonModel extends BaseModel  {
    private $table_name = "exomoon";

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
    public function selectExoMoons(array $filters = [], $page = null, $page_size = null) {
        // Set Page and Page Size Default Values If Params Null
        if (!$page) $page = 1;
        if (!$page_size) $page_size = 10;

        $query_values = [];

        // Base Statement
        $select = "SELECT exM.*";
        $from = " FROM $this->table_name AS exM";
        $where = " WHERE 1 ";
        $group_by = "";

        if (isset($filters["exoMoonName"])) {
            $where .= "AND exM.exomoon_name LIKE CONCAT('%', :exomoon_name, '%') ";
            $query_values[":exomoon_name"] = $filters["exoMoonName"];
        }

        if (isset($filters["exoMass"])) {
            $where .= "AND exM.mass = :mass ";
            $query_values[":mass"] = $filters["exoMass"];
        }

        if (isset($filters["orbitalPeriodDays"])) {
            $where .= "AND exM.orbital_period_days = :orbital_period_days ";
            $query_values[":orbital_period_days"] = $filters["orbitalPeriodDays"];
        }

        if (isset($filters["discoveryMethod"])) {
            $where .= "AND exM.discovery_method >= :from_discovery_method ";
            $query_values[":from_discovery_method"] = $filters["discoveryMethod"];
        }

        if (isset($filters["discoveryMethod"])) {
            $where .= "AND exM.discovery_method <= :to_discovery_method ";
            $query_values[":to_discovery_method"] = $filters["discoveryMethod"];
        }

        $sql = $select . $from . $where . $group_by;

        // Return Paginated Results
        $this->setPaginationOptions($page, $page_size);
        return $this->paginate($sql, $query_values);
    }

    public function selectExoMoon(int $exomoon_id){
        
        // Base Statement
        $select = "SELECT exM.*";
        $from = " FROM $this->table_name AS exM";
        $where = " WHERE exomoon_id =:exomoon_id AND 1 ";
        $group_by = "";

        $sql = $select . $from . $where . $group_by;

        return $this->run($sql, [":exomoon_id"=> $exomoon_id])->fetch();
    }
}