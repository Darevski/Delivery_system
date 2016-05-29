<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 2:30
 */

namespace Application\Models;


use Application\Core\Model;
use Application\Exceptions\Model_Except;
use Application\Units\Yandex_Geo_Api;

/**
 * Class Model_Delivery_Points Model include business logic for Points of Delivery
 * For example Add, Delete, point, cost calculation and etc
 * @package Application\Models
 */
class Model_Delivery_Points extends Model{

    /**
     * @var Model_Storage
     */
    private $Model_storage;

    /**
     * Model_Delivery_Points constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->Model_storage = new Model_Storage();
    }

    /**
     * Adding an empty point to database with identifier(yyyymmdd#today_orders+1) and Order_date as today date
     * Lock table 'Delivery_Point' to other SQL sessions on execution time
     *
     * Добавляет пустую точку с идентификатором заказа (вычисляется как yyyymmdd#заказы на сегодня +1)
     * и сегодняшей датой в базу данных. Возвращает уникальный идентификатор точки и идентификатор заказа
     * Блокирует таблицу Delivery_Point' для других транзакций
     *
     * @param integer $storage_id
     * @param integer $company_id
     *

     * @return mixed array{
     * 'point_id' -  id point that we inserted into database
     * 'identifier_order' - identifier of full order by that point in database
     * }
     * @throws Model_Except
     */
     public function add_empty_point($storage_id,$company_id){
        // checks thats storage are exist
        if (!$this->Model_storage->isset_storge($storage_id,$company_id))
            throw new Model_Except("Выбранного склада не существует, обновите страницу");

        $today = date('Ymd');

        // Lock Table to other user`s
        $this->database->query("LOCK TABLES Delivery_Points WRITE");

        // get identifier`s of order(point) from registered today points
        $query_today_points = "SELECT identifier_order From Delivery_Points WHERE Order_Date = ?p and Storage_ID=?i";
        $today_points = $this->database->getAll($query_today_points,$today,$storage_id);

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
        $query_insert_empty_point = "INSERT INTO Delivery_Points (identifier_order,Order_Date,Storage_ID) VALUES (?s,?p,?i)";
        $this->database->query($query_insert_empty_point,$identifier_order,$today,$storage_id);
        // get the id of point that we insert into database
        $point_id = $this->database->insertId();

        // Unlock Table to other users
        $this->database->query("UNLOCK TABLES");

        // forming return array
        $return_info['point_id']= (int)$point_id;
        $return_info['identifier_order'] = $identifier_order;
        return $return_info;
    }

    /**
     * Update information of delivery point in database
     * Обновляет информацию о точке доставки в базе данных
     * @param integer $storage_id
     * @param integer $company_id
     * @param string $street
     * @param string $house
     * @param string $entry
     * @param integer $floor
     * @param integer $flat
     * @param integer $point_id
     * @param string $note
     * @param string $time_start "H:i:s"
     * @param string $time_end "H:i:s"
     * @param integer $phone_number
     * @param integer $delivery_date unix timestamp
     * @throws Model_Except
     * @throws \Application\Exceptions\Curl_Except
     * @throws \Application\Exceptions\Server_Error_Except
     * @throws \Application\Exceptions\UFO_Except
     */
    public function fill_point($storage_id,$company_id,$street,$house,$entry,$floor,$flat,$point_id,$note,$time_start,$time_end,$phone_number,$delivery_date){
        // checks thats storage are exist
        if (!$this->Model_storage->isset_storge($storage_id,$company_id))
            throw new Model_Except("Выбранного склада не существует, обновите страницу");

        // check that point isset in database
        if (!$this->isset_point($point_id,$storage_id))
            throw new Model_Except("Обновляемой точки доставки не существует");
        // relevance date check
        /* FIXME: Проблема с клиентским временем  TEST IT*/
        if (mktime(0,0,0) > $delivery_date)
            throw new Model_Except("Дата заказа не может быть меньше текущей");
        $delivery_date = date('Y-m-d',$delivery_date);
        //convert string time to "H:i:s"
        $time_start = date('H:i:s',strtotime($time_start));
        $time_end = date('H:i:s',strtotime($time_end));
        if ($time_start > $time_end)
            throw new Model_Except("Начальное ограничение времени доставки, не может быть больше конечного ограничения");

        // Use Yandex. Maps GeoCoder Unit
        $yandex = new Yandex_Geo_Api();
        $point_info = $yandex->SetGeoCode(sprintf("%s,%s",$street,$house))
                             ->start_request()
                             ->Get_Response()->getList_GEO_objects()[0];
        //Get correctly street name from Yandex GeoCoder
        $street = $point_info->getThoroughfareName();
        $house = $point_info->getPremiseNumber();
        //Get Geo coordinates
        $latitude =$point_info->getLatitude();
        $longitude = $point_info->getLongitude();

        $update_point_query = "UPDATE Delivery_Points SET Street =?s, House=?s, Note=?s,Entry=?s,floor=?i,flat=?i,
              phone_number=?i,time_start=?s,time_end=?s,Delivery_Date=?s,Longitude=?s, Latitude=?s WHERE Point_ID=?i and Storage_ID=?i";

        $this->database->query($update_point_query,$street,$house,$note,$entry,$floor,$flat,
            $phone_number,$time_start,$time_end,$delivery_date,$longitude,$latitude,$point_id,$storage_id);
    }

    /**
     * Delete Delivery point and Orders related with from database
     * @param integer $storage_id
     * @param integer $company_id
     * @param integer $point_id
     * @throws Model_Except
     */
    public function delete_point($storage_id,$company_id,$point_id){

        if (!$this->Model_storage->isset_storge($storage_id,$company_id))
            throw new Model_Except("Выбранного склада не существует, обновите страницу");

        if (!$this->isset_point($point_id,$storage_id))
            throw new Model_Except("Выбранной для удаления точки доставки не существует");

        $delete_query = "DELETE FROM Delivery_Points WHERE Point_ID=?i LIMIT 1";
        $this->database->query($delete_query,$point_id);
    }

    /**
     * return list of all delivery point`s on selected date
     * @param integer $storage_id
     * @param integer $company_id
     * @param $date int in unix timestamp
     * @return array(int) list of delivery point`s
     * @throws Model_Except
     */
    public function get_points_by_date($storage_id,$company_id,$date){
        //convert unix timestamp to human format

        if (!$this->Model_storage->isset_storge($storage_id,$company_id))
            throw new Model_Except("Выбранного склада не существует, обновите страницу");

        $delivery_date = date('Ymd',$date);

        $query = "SELECT Point_ID FROM Delivery_Points WHERE Delivery_Date=?s and Storage_ID=?i";
        $result_of_query = $this->database->getAll($query,$delivery_date,$storage_id);

        $result['points_id'] = array();
        foreach ($result_of_query as $value)
            $result['points_id'][] = (int)$value['Point_ID'];

        return $result;
    }

    /**
     * return info about selected point
     * structure of output {
     *  float 'total_cost'
     *  string 'identifier_order'
     *  string 'street'
     *  string 'house'
     *  string 'note'
     *  string 'entry'
     *  integer 'floor'
     *  integer 'flat'
     *  float 'latitude'
     *  float 'longitude'
     *  integer 'phone_number' 12 digits
     *  string 'time_start'
     *  string 'time_end'
     *  string 'delivery_date'
     *  string 'order_date'
     * }
     * @param integer $storage_id
     * @param integer $company_id
     * @param $point_id
     * @return mixed
     * @throws Model_Except
     */
    public function get_info_about_point($storage_id,$company_id,$point_id){

        if (!$this->Model_storage->isset_storge($storage_id,$company_id))
            throw new Model_Except("Выбранного склада не существует, обновите страницу");

        if (!$this->isset_point($point_id,$storage_id))
            throw new Model_Except("Точки доставки не существует");

        $query = "SELECT total_cost,identifier_order,street,house,note,entry,floor,flat,latitude,longitude,
                phone_number,time_start,time_end,delivery_date,order_Date FROM Delivery_Points WHERE Point_ID=?i";
        $result_of_query = $this->database->getRow($query,$point_id);
        //conversion output types
        settype($result_of_query['total_cost'],"float");
        settype($result_of_query['floor'],"integer");
        settype($result_of_query['flat'],"integer");
        settype($result_of_query['latitude'],"float");
        settype($result_of_query['longitude'],"float");
        settype($result_of_query['phone_number'],"integer");

        return $result_of_query;
    }

    /**
     * Check`s availability point in database
     * @param $point_id
     * @param $storage_id
     * @return bool
     */
    public function isset_point($point_id,$storage_id){
        $query = "SELECT 1 FROM Delivery_Points WHERE Point_ID = ?i and Storage_ID=?i LIMIT 1";
        $result =  $this->database->query($query,$point_id,$storage_id);
        $count = $this->database->numRows($result);
        return ($count > 0) ? true : false;
    }
}