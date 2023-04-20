<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Helpers\Validator;
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

        if (isset($filters["exoPlanetName"])) {
            $where .= " AND p.exoplanet_name LIKE CONCAT('%', :exoplanet_name, '%')";
            $query_values[":exoplanet_name"] = $filters["exoPlanetName"];
        }

        if (isset($filters["discoveryMethod"])) {
            $where .= " AND p.discovery_method LIKE CONCAT('%', :discovery_method, '%')";
            $query_values[":discovery_method"] = $filters["discoveryMethod"];
        }

        if (isset($filters["fromDiscoveryYear"])) {
            $where .= " AND p.discovery_year >= :fromDiscoveryYear";
            $query_values[":fromDiscoveryYear"] = $filters["fromDiscoveryYear"];
        }

        if (isset($filters["toDiscoveryYear"])) {
            $where .= " AND p.discovery_year <= :toDiscoveryYear";
            $query_values[":toDiscoveryYear"] = $filters["toDiscoveryYear"];
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

    public function selectExoPlanet(int $exoPlanet_id){
        
        // Base Statement
        $select = "SELECT s.*";
        $from = " FROM $this->table_name AS s";
        $where = " WHERE exoplanet_id =:exoplanet_id AND 1 ";
        $group_by = "";

        $sql = $select . $from . $where . $group_by;

        return $this->run($sql, [":exoplanet_id"=> $exoPlanet_id])->fetch();
    }

    /**
     * Insert ExoPlanets Into Database
     * @param array $data ExoPlanets to Insert
     * @return array Rows Inserted, Failed and/or Missing
     */
    public function insertExoPlanets(array $data) {
        $rules["star_id"] = ["required", "numeric", ["min", 0], ["max", 99999999]];
        $rules["exoplanet_name"] = ["required", ["lengthBetween", 1, 64]];
        $rules["discovery_method"] = ["optional", ["in", ["Radial Velocity", "Imaging", "Pulsation Timing Variations", "Transit", "Eclipse Timing Variations", "Microlensing", "Transit Timing Variations", "Pulsation Timing", "Disk Kinematics", "Orbital Brightness Modulation"]]];
        $rules["discovery_year"] = ["required", "numeric", ["min", 0], ["max", 9999]];
        $rules["orbital_period_days"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["mass"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];

        // For Each Rocket...
        foreach($data as $exoPlanet) {
            $validator = new Validator($exoPlanet);
            $validator->mapFieldsRules($rules);

            // If Data Is valid...
            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($exoPlanet, ["star_id", "exoplanet_name", "discovery_method", "discovery_year", "orbital_period_days", "mass"]);
                
                // Insert Star Into Database
                $last_id = $this->insert($this->table_name, $fields);

                if ($last_id != 0) {
                    $results["row_inserted"][] = $this->selectExoPlanet($last_id);
                } else {
                    $results["rows_missing"][] = [...$exoPlanet, "errors" => "An error occured while inserting row."];
                }
            } else {
                $results["rows_failed"][] = [...$exoPlanet, "errors" => $validator->errors()];
            }
        }

        return $results;
    }
}