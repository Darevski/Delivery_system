<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 29.05.16
 * Time: 1:13
 */

namespace Application\Models;

use Application\Core\Model;

class Model_Company extends Model{

    /**
     * Checks thats company are exists in database
     * @param $company_id
     * @return bool
     */
    public function isset_user($company_id){
        $query = "SELECT 1 FROM Company WHERE id = ?s LIMIT 1";
        $result =  $this->database->query($query,$company_id);
        $count =  $this->database->numRows($result);
        return ($count > 0) ? true : false;
    }
}