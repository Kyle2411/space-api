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