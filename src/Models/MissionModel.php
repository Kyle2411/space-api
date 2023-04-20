<?php

namespace Vanier\Api\Models;

use Slim\Exception\HttpBadRequestException;
use Vanier\Api\Models\BaseModel;
use Vanier\Api\Helpers\Validator;
use Vanier\Api\Helpers\ArrayHelper;

/**
 * Summary of MissionModel
 */
class MissionModel extends BaseModel  {
    /**
     * Summary of table_name
     * @var string
     */
    private $table_name = "mission";

    /**
     * Summary of __construct
     */
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
    public function selectMissions(array $filters = [], $page = null, $page_size = null) {
        // Set Page and Page Size Default Values If Params Null
        if (!$page) $page = 1;
        if (!$page_size) $page_size = 10;

        $query_values = [];

        // Base Statement
        $select = "SELECT m.*";
        $from = " FROM $this->table_name AS m";
        $join = "";
        $where = " WHERE 1 ";
        $group_by = "";

        if (isset($filters["missionName"])) {
            $where .= " AND m.mission_name LIKE CONCAT('%', :mission_name, '%')";
            $query_values[":mission_name"] = $filters["missionName"];
        }

        if (isset($filters["companyName"])) {
            $where .= " AND m.company_name LIKE CONCAT('%', :company_name, '%')";
            $query_values[":company_name"] = $filters["companyName"];
        }

        if (isset($filters["fromMissionDate"])) {
            $where .= " AND m.mission_date >= :fromMissionDate";
            $query_values[":fromMissionDate"] = $filters["fromMissionDate"];
        }

        if (isset($filters["toMissionDate"])) {
            $where .= " AND m.mission_date <= :toMissionDate";
            $query_values[":toMissionDate"] = $filters["toMissionDate"];
        }

        if (isset($filters["missionStatus"])) {
            $where .= " AND m.mission_status LIKE CONCAT('%', :mission_status, '%')";
            $query_values[":mission_status"] = $filters["missionStatus"];
        }

        if (isset($filters["astronautId"])) {
            $join .= " JOIN mission_astronaut AS ma ON ma.mission_id = m.mission_id JOIN astronaut AS a ON ma.astronaut_id = a.astronaut_id";
            $where .= " AND a.astronaut_id = :astronautId";
            $query_values[":astronautId"] = $filters["astronautId"];
        }

        if (isset($filters["rocketId"])) {
            $join .= " JOIN rocket as r ON m.rocket_id = r.rocket_id";
            $where .= " AND r.rocket_id = :rocketId";
            $query_values[":rocketId"] = $filters["rocketId"];
        }

        $sql = $select . $from . $join . $where . $group_by;

        // Return Paginated Results
        $this->setPaginationOptions($page, $page_size);
        return $this->paginate($sql, $query_values);
    }

    /**
     * Summary of selectMission
     * @param int $mission_id
     * @return mixed
     */
    public function selectMission(int $mission_id){
        
        // Base Statement
        $select = "SELECT s.*";
        $from = " FROM $this->table_name AS s";
        $where = " WHERE mission_id =:mission_id AND 1 ";
        $group_by = "";

        $sql = $select . $from . $where . $group_by;

        return $this->run($sql, [":mission_id"=> $mission_id])->fetch();

    }

    /**
     * Insert Missions Into Database
     * @param array $data Missions to Insert
     * @return array Rows Inserted, Failed and/or Missing
     */
    public function insertMissions(array $data) {
        $rules["rocket_id"] = ["optional", "numeric", ["min", 0], ["max", 99999999]];
        $rules["mission_name"] = ["required", ["lengthBetween", 1, 128]];
        $rules["company_name"] = ["required", ["lengthBetween", 1, 64]];
        $rules["mission_location"] = ["required", ["lengthBetween", 1, 128]];
        $rules["mission_date"] = ["required", ["dateFormat", "Y-m-d" ]];
        $rules["mission_time"] = ["optional", ["instanceOf", new \DateTime()]];
        $rules["mission_status"] = ["optional", ["in", ["Success", "Failure", "Partial Failure", "Prelaunch Failure"]]];

        // For Each Rocket...
        foreach($data as $mission) {
            $validator = new Validator($mission);
            $validator->mapFieldsRules($rules);

            // If Data Is valid...
            if ($validator->validate()) {
                // Get Fields from Data
                $fields = ArrayHelper::filterKeys($mission, ["rocket_id", "mission_name", "company_name", "mission_location", "mission_date", "mission_time", "mission_status"]);
                
                // Insert Star Into Database
                $last_id = $this->insert($this->table_name, $fields);

                if ($last_id != 0) {
                    $results["row_inserted"][] = $this->selectMission($last_id);
                } else {
                    $results["rows_missing"][] = [...$mission, "errors" => "An error occured while inserting row."];
                }
            } else {
                $results["rows_failed"][] = [...$mission, "errors" => $validator->errors()];
            }
        }

        return $results;
    }

    
}