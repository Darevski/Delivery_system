<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 2:30
 */

namespace Application\Models;


use Application\Core\Model;

/**
 * Class Model_Delivery_Points Model include business logic for Points of Delivery
 * For example Add, Delete, point, cost calculation and etc
 * @package Application\Models
 */
class Model_Delivery_Points extends Model{

    /**
     * Adding an empty point to database with identifier(yyyymmdd#today_orders+1) and Order_date as today date
     * Lock table 'Delivery_Point' to other SQL sessions on execution time
     *
     * Добавляет пустую точку с идентификатором заказа (вычисляется как yyyymmdd#заказы на сегодня +1)
     * и сегодняшей датой в базу данных. Возвращает уникальный идентификатор точки и идентификатор заказа
     * Блокирует таблицу Delivery_Point' для других транзакций
     *
     * @return mixed array{
     * 'point_id' -  id point that we inserted into database
     * 'identifier_order' - identifier of full order by that point in database
     * }
     */
    public function add_empty_point(){
        $today = date('Ymd');

        // Lock Table to other user`s
        $this->database->query("LOCK TABLES Delivery_Points WRITE");

        // get identifier`s of order(point) from registered today points
        $query_today_points = "SELECT identifier_order From Delivery_Points WHERE Order_Date = ?p";
        $today_points = $this->database->getAll($query_today_points,$today);

        // get max value from identifier mask yyyymmdd#value
        foreach($today_points as &$value)
            $value = preg_replace('/.*#/',"",$value['identifier_order']);
        if ( count($today_points) > 0 )
            $max_ind = max($today_points);
        else
            $max_ind = 0;

        // make identifier with mask (yyyymmdd#max_value+1)
        $identifier_order = $today.'#'.($max_ind+1);
        // insert the values into database
        $query_insert_empty_point = "INSERT INTO Delivery_Points (identifier_order,Order_Date) VALUES (?s,?p)";
        $this->database->query($query_insert_empty_point,$identifier_order,$today);
        // get the id of point that we insert into database
        $point_id = $this->database->insertId();

        // Unlock Table to other users
        $this->database->query("UNLOCK TABLES");

        // forming return array
        $point_info['point_id']= $point_id;
        $point_info['identifier_order'] = $identifier_order;
        return $point_info;
    }
}