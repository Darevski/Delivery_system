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
     * 'state' - information about execute 'success' - all is ok
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
        unset ($value);

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
        $return_info['point_id']= $point_id;
        $return_info['identifier_order'] = $identifier_order;
        $return_info['state'] = 'success';
        return $return_info;
    }

    /**
     * @param $data mixed array with next values
     * address{
     *  string street
     *  string house
     *  string block
     *  string entry
     *  int flor
     *  int flat
     * }
     * int(12) phone for example '375291234567'
     * time {
     *  start: "H:i:s"
     *  end: "H:i:s"
     * }
     * unix timestamp delivery_date
     * int point_id - Unique value (Points_ID) from database Delivery_Points
     * @return array {state: information about execute 'success' - all is ok}
     */
    public function fill_empty_point($data){
        // +-----------address----------------+
        $street = $data['address']['street'];
        $house = $data['address']['house'];
        $corpus = $data['address']['block'];
        $entry = $data['address']['entry'];
        $floor= $data['address']['floor'];
        $flat = $data['address']['flat'];
        // ------------address-----------------
        $phone_number = $data['phone'];
        $time_start = date('H:i:s',strtotime($data['time']['start']));
        $time_end = date('H:i:s',strtotime($data['time']['end']));
        $delivery_Date = date('Ymd',$data['delivery_date']);
        $point_id = $data['point_id'];

        $update_point_query = "UPDATE Delivery_Points SET Street =?s, House=?s, Corps=?s,
        Entry=?s,floor=?i,flat=?i,phone_number=?i,time_start=?s,time_end=?s,Delivery_Date=?s WHERE Point_ID=?i";
        $this->database->query($update_point_query,$street,$house,$corpus,$entry,$floor,$flat,
            $phone_number,$time_start,$time_end,$delivery_Date,$point_id);
        $execution_result['state'] = 'success';
        return $execution_result;
    }
}