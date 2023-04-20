<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Models\BaseModel;
use Vanier\Api\Helpers\ArrayHelper;
use Vanier\Api\Helpers\Validator as HelpersValidator;


/**
 * Summary of ExoMoonModel
 */
class ExoMoonModel extends BaseModel  {
    private $table_name = "exomoon";

    /**
     * Summary of __construct
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Summary of createExoMoon
     * @param array $actor
     * @return bool|string
     */
    public function createExoMoon(array $data)
    {
        $rules["exomoon_name"] = ["required", ["lengthBetween", 1, 64]];
        $rules["discovery_method"] =  ["required", ["in", ["Radial Velocity","Imaging","Pulsation Timing Variations","Transit","Eclipse Timing Variations","Microlensing","Transit Timing Variations","Pulsation Timing","Disk Kinematics","Orbital Brightness Modulation"]]];
        $rules["orbital_period_days"] = ["optional", "numeric", ["min", 0], ["max", 999999]];
        $rules["mass"] = ["optional", "numeric", ["min", 0], ["max", 999999]];

        // for each exoMoon
        foreach ($data as $exoMoon) {
            $validator = new Validator($exoMoon);
            $validator->mapFieldsRules($rules);

            // If Data Is valid...
            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($exoMoon, ["exomoon_name", "discovery_method", "orbital_period_days", "mass"]);
                
                // Insert Rocket Into Database
                $last_id = $this->insert($this->table_name, $fields);

                if ($last_id != 0) {
                    $results["row_inserted"][] = $this->selectExoMoon($last_id);
                } else {
                    $results["rows_missing"][] = [...$exoMoon, "errors" => "An error occured while inserting row."];
                }
            } else {
                $results["rows_failed"][] = [...$exoMoon, "errors" => $validator->errors()];
            }
        }
        return $results;
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
        $where = " WHERE exM.exomoon_id =:exomoon_id AND 1 ";
        $group_by = "";

        $sql = $select . $from . $where . $group_by;

        return $this->run($sql, [":exomoon_id"=> $exomoon_id])->fetch();
    }

    public function selectExoMoonByExoPlanet(int $exoPlanet_id){
        
        // Base Statement
        $select = "SELECT m.*";
        $from = " FROM exomoon AS m";
        $where = " WHERE exoplanet_id =:exoplanet_id AND 1 ";
        $group_by = "";

        $sql = $select . $from . $where . $group_by;
        
        return $this->run($sql, [":exoplanet_id"=> $exoPlanet_id])->fetchAll();
    }
}