<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 2:22
 */

namespace Application\Models;
use Application\Core\Model;
use Application\Exceptions\Model_Except;

/**
 * Class Model_Orders Model include business logic for orders
 * For example Add, Delete, Update order and etc
 * @package Application\Models
 */
class Model_Orders extends Model{

    /** Insert order in database table
     * @param array $data with next structure
     * int point_id - unique id from Delivery point table
     * string description - description of item
     * double cost - cost of this item
     * @return mixed
     * @throws Model_Except
     */
    public function add_order($data){
        $point_id = $data['point_id'];
        $description = $data['description'];
        $cost = $data['cost'];

        $insert_query = "INSERT INTO Orders (Point_ID,Description,Cost) VALUES (?i,?s,?s)";
        $result = $this->database->query($insert_query,$point_id,$description,$cost);
        if ($result)
            $execution_result['state'] = 'success';
        else
            throw new Model_Except("Mysql error");
        return $execution_result;
    }
}