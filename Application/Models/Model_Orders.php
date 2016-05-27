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

    /**
     * Insert order in database table and return his id
     * @param integer $point_id unique id from Delivery point table
     * @param string $description description of item
     * @param float $cost cost of this item
     * @return integer - id in database of inserted order
     * @throws Model_Except
     */
    public function add_order($point_id,$description,$cost){
        // using Delivery Points model for verify the existence of point
        $model_points = new Model_Delivery_Points();
        if (!$model_points->isset_point($point_id))
            throw new Model_Except("Точки доставки не существует");
        if ($cost <0)
            throw new Model_Except("Стоимость не может быть меньше 0");
        $insert_query = "INSERT INTO Orders (Point_ID,Description,Cost) VALUES (?i,?s,?s)";
        $this->database->query($insert_query,$point_id,$description,$cost);

        // Get id of inserted order
        $insert_id = $this->database->insertId();
        return $insert_id;
    }

    /**
     * Return list of orders for selected delivery point
     * @param int $point_id
     * @return mixed array{ [integer 'order_id', string 'description', float 'cost'] }
     * @throws Model_Except
     */
    public function get_list_orders_by_point_id($point_id){
        // using Delivery Points model for verify the existence of point
        $model_points = new Model_Delivery_Points();
        if (!$model_points->isset_point($point_id))
            throw new Model_Except("Точки доставки не существует");

        $query = "SELECT order_id,description,cost FROM Orders WHERE Point_ID=?i";
        $result_query = $this->database->getAll($query,$point_id);

        foreach ($result_query as &$value){
            settype($value['order_id'],"integer");
            settype($value['cost'],"float");
        }
        return $result_query;
    }

    /**
     * Delete Order From DataBase
     * @param integer $order_id
     * @throws Model_Except
     */
    public function delete_order($order_id){
        if(!$this->isset_order($order_id))
            throw  new Model_Except("Заказа с указанным id не существует");

        $delete_query = "DELETE FROM Orders WHERE Order_ID = ?i";
        $this->database->query($delete_query,$order_id);
    }

    /**
     * Update Order parameters in Database
     * @param integer $order_id
     * @param string $description
     * @param float $cost
     * @throws Model_Except
     */
    public function update_order($order_id,$description,$cost){
        if(!$this->isset_order($order_id))
            throw  new Model_Except("Заказа с указанным id не существует");

        $update_query = "UPDATE Orders SET Description=?s,Cost=?s WHERE Order_ID=?i";
        $this->database->query($update_query,$description,$cost,$order_id);
    }

    /**
     * Check`s availability order in database
     * @param $order_id
     * @return bool
     */
    public function isset_order($order_id){
        $query = "SELECT 1 FROM Orders WHERE Order_ID = ?i LIMIT 1";
        $result =  $this->database->query($query,$order_id);
        $count = $this->database->numRows($result);
        return ($count > 0) ? true : false;
    }
}