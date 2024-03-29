<?php
namespace Vanier\Api\Models;

use DateTime;
use DateTimeZone;

/**
 * A class that is used for logging user actions.
 *
 * @author Sleiman Rabah
 */
class WSLoggingModel extends BaseModel {

    private $table_name = "ws_log";

    function __construct() {
        // Call the parent class and initialize the database connection settings.
        parent::__construct();
    }

    /**
     * Adds to the database an entry about a user's action
     * For example, what resource has been invoked, by whom and at what date and time...
     * 
     * @param array $log_data The data to be logged in the DB.
     * @return array
     */
    public function logUserAction($jwt_payload, $uer_action, $status) {
        $log_data["user_id"] = $jwt_payload["id"];
        $log_data["email"] = $jwt_payload["email"];
        $log_data["user_action"] = $uer_action;
        $log_data["status"] = $status;
        $log_data["logged_at"] = $this->getCurrentDateAndTime();
        return $this->insert($this->table_name, $log_data);
    }



    // public function getUserAction()
    // {
    //     $select = "SELECT user.*, ws_users.first_name, ws_users.last_name, ws_users.role";
    //     $from = " FROM $this->table_name AS user";
    //     $join = " LEFT JOIN ws_users ON user.id = ws_users.user_id";
    //     $where = " WHERE 1 ";
    //     $group_by = "";
    
    //     $sql = $select . $from . $join . $where . $group_by;
    
    //     return $this->run($sql)->fetch();
    // }
    


    /**
     * Gets the current date and time give the provided time zone.
     * 
     * For more information about the supported time zones, 
     * @see: https://www.php.net/manual/en/timezones.america.php
     * 
     * @return string
     */
    private function getCurrentDateAndTime() {
        // By setting the time zone, we ensure that the produced time 
        // is accurate.
        $tz_object = new DateTimeZone('America/Toronto');
        $datetime = new DateTime();
        $datetime->setTimezone($tz_object);
        return $datetime->format('Y\-m\-d\ h:i:s');
    }

}
