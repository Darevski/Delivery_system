<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 09.04.16
 * Time: 17:25
 */

namespace Application\Models;

use Application\Core\Model;
use Application\Core\System\Config;
use Application\Exceptions\Model_Except;
use Application\Units\Authentication;
use Application\Units\FPDF;

/**
 * Class Model_Route include business logic related with routes
 * @package Application\Models
 */
class Model_Route extends Model{

    private $Model_storage;
    /**
     * the start time of delivery
     * @var int
     * default 18:00
     */
    private $time_start_delivery = 64800;

    /**
     * time for delivery item
     * default 15 min
     * @var int
     */
    private $time_for_delivery = 900;

    /**
     * Convert time in config to seconds
     * Model_Route constructor.
     */
    public function __construct(){
        parent::__construct();
        $auth = new Authentication();
        $auth->access_check(1);

        // Конвертация Времени указанного в конфиге в секунды
        $config = Config::get_instance()->get_route_algorithm();
        list($hours, $min, $sec) = explode(':', $config['time_for_delivery']);
        $this->time_for_delivery = ($hours * 3600 ) + ($min * 60 ) + $sec;
        list($hours, $min, $sec) = explode(':', $config['time_start_delivery']);
        $this->time_start_delivery = ($hours * 3600 ) + ($min * 60 ) + $sec;
        $this->Model_storage = new Model_Storage();
    }


    /**
     * Matrix with time point to point way
     * @var array float
     */
    private $_timeMatrix;

    /**
     * Checks an existing routes and if it`s not existing
     * calculate new route and write into database
     * @param $points array {[int point_id, int time_start , int time_end],[]....}
     * @param $timeMatrix [[int/null],[int/null].....]
     * @param $date int unix timestamp
     * @param $storage_id
     * @param $company_id
     * @throws Model_Except
     * @return mixed
     */
    public function handling_route($points,$timeMatrix,$date,$storage_id,$company_id){
        $this->_timeMatrix = $timeMatrix;
        if (!$this->Model_storage->isset_storge($storage_id,$company_id))
            throw new Model_Except("Выбранного склада не существует");

        // Check on existing routes
        // If there is no routes then calculate new route
        if (!$this->checks_existence_route($date,$storage_id)) {
            if (count($points) <= 0)
                throw new Model_Except("Для постороения маршрута необходима хотя бы 1 точка");

            $model_points = new Model_Delivery_Points();
            //check exciting points in Database
            foreach ($points as $value)
                if (!$model_points->isset_point($value['point_id'],$storage_id))
                    throw new Model_Except("Одной из выбранных точек не существует в БД");
            // Get paths by algorithm
            $ways = $this->calculate_route($points);
            $tracks = array();

            // get info about delivery point && path
            foreach ($ways as $way) {
                $first_point = true;
                foreach ($way as $dot) {
                    // time from base to first point in route
                    if ($first_point === true)
                        $time_to_first_point = $timeMatrix[0][$dot['index_point']+1];

                    $first_point = false;
                    $point_info = $model_points->get_info_about_point($points[$dot['index_point']]['point_id'],$company_id);

                    $point['point_id'] = $points[$dot['index_point']]['point_id'];

                    $point['latitude'] = $point_info['latitude'];
                    $point['longitude'] =$point_info['longitude'];

                    $point['address'] = $point_info['street'].' д.'.$point_info['house'].' кв. '.$point_info['flat'];

                    $point['time_start'] = $point_info['time_start'];
                    $point['time_end'] = $point_info['time_end'];

                    $point['time'] = $dot['time'];

                    $route['points'][] = $point;
                }

                // Total time time ot first point from base + last point time - first point time + time for delivery
                $total_time = $time_to_first_point + $point['time'] + $this->time_for_delivery - $route['points'][0]['time'];

                $route['total_time'] = $total_time;
                $tracks[] = $route;
                $route = array();
            }
            //delete useless variables
            unset($ways, $route, $point, $point_info, $value, $way, $dot);

            // save calculated routes into database
            $this->save_route($tracks, $date,$storage_id);
        }
    }

    /**
     * get routes by selected date
     * @param int $date unix timestamp
     * @param int $storage_id number in database
     * @param int $company_id uniq user id in database
     * @return array mixed { [
     *      points:[
     *          {
     *              float latitude,
     *              float longitude,
     *              string time_start,
     *              string time_end,
     *              string address,
     *              float time
     *          }
     *     float total_time] ] }
     * @throws Model_Except
     */
    public function get_route_by_date($date,$storage_id,$company_id){
        if (!$this->Model_storage->isset_storge($storage_id,$company_id))
            throw new Model_Except('Склада не существует');

        $query = "SELECT routes FROM Routes WHERE calculating_date=?s and Storage_ID=?i";
        $date = date('Ymd',$date);
        $routes = unserialize(base64_decode($this->database->getRow($query,$date,$storage_id)['routes']));
        if ($routes == false)
            $routes = array();
        return $routes;
    }

    /**
     * Calculate routes by points and time matrix
     * @param $points array {[int point_id, int time_start , int time_end],[]....}
     * @return array
     * @throws Model_Except
     */
    private function calculate_route($points){
        $used_points = array();
        for ($i = 0; $i< count($points); $i++)
            $used_points[$i]=false;

        $path = array();
        $calculation = true;
        // Check that we can enter this point from warehouse
        for ($i =0 ;$i< count($this->_timeMatrix[0]);$i++)
            if ($this->_timeMatrix[0][$i] != null)
                if ($this->_timeMatrix[0][$i] + $this->time_start_delivery + $this->time_for_delivery > $points[$i-1]['time_end'])
                    throw new Model_Except("Для точки $i задано не выполнимое условие по времени доставки");

        while ($calculation){
            $this->_position=-1;
            $path[] = $this->routeByTime($this->time_start_delivery,[],$used_points,$points);
            $calculation = false;
            for ($i = 0; $i< count($used_points);$i++)
                if ($used_points[$i] == false)
                    $calculation = true;
        }
        return $path;
    }

    /**
     * @var int indicate at what point stay now
     */
    private $_position;

    /**
     * Sort descending Callback
     * @param $a null/int
     * @param $b null/int
     * @return int
     */
    private function callback_sort_by_time($a,$b){
        if ($a['time_start']!==$b['time_start'])
            return $a['time_start'] - $b['time_start'];
        // fucking magic
        return $this->_timeMatrix[$this->_position][$a['index']+1] - $this->_timeMatrix[$this->_position][$b['index']+1];
    }


    /**
     * Return best point to go next from current point
     * but seriously, it`s still a little magic
     * @param $time
     * @param $used_array
     * @param $points_array
     * @return bool
     */
    private function next_point_where_we_going($time,$used_array,$points_array){
        for ($i =0 ; $i < count($points_array);$i++)
            $points_array[$i]['index'] = $i;

        usort($points_array,array($this,'callback_sort_by_time'));

        // one more fucking magic
        for ($i =0 ; $i < count($points_array);$i++)
            if (!$used_array[$points_array[$i]['index']])
                if($time + $this->_timeMatrix[$this->_position][$points_array[$i]['index']+1] + $this->time_for_delivery <= $points_array[$i]['time_end'])
                    return $points_array[$i]['index'];
        return false;
    }


    /**
     * Recursive algorithm of calculating Route
     * Магия в чистом виде, не трогать и не пытаться понять
     * @param $time
     * @param $path
     * @param $usedArray
     * @param $points
     * @return array|null
     */
    private function routeByTime($time,$path,&$usedArray,$points){
        // Определение точки из которой двигаемся -1 - склад
        ($this->_position != -1) ? ($usedArray[$this->_position-1]=true) : ($this->_position = 0);

        //Определяем слуедующую точку в которую мы движемся
        $next_point = $this->next_point_where_we_going($time,$usedArray,$points);

        if ($next_point !== false){
            $new_time= $time+ $this->_timeMatrix[$this->_position][$next_point+1];
            if ($time + $this->_timeMatrix[$this->_position][$next_point+1] <= $points[$next_point]['time_start'])
                $new_time = $points[$next_point]['time_start'];

            $temp ['index_point'] = $next_point;
            $temp ['time'] = $new_time;
            $path[] = $temp;
            unset($temp);

            $this->_position= $next_point+1;
            return $this->routeByTime($new_time+$this->time_for_delivery,$path,$usedArray,$points);
        }
        return $path;
    }

    /**
     * Save serialized routes into database
     * @param $data
     * @param $date
     */
    private function save_route($data,$date,$storage_id){
        $query = "INSERT INTO Routes (calculating_date,routes,Storage_ID) VALUES (?s,?s,?i)";
        $date = date('Ymd',$date);
        $serialized_data = base64_encode(serialize($data));
        $this->database->query($query,$date,$serialized_data,$storage_id);
    }

    /** Checks that routes in selected day is existing
     * @param $date
     * @return bool
     */
    private function checks_existence_route($date,$storage_id){
        $query = "SELECT 1 FROM Routes WHERE calculating_date=?s and Storage_ID=?i LIMIT 1";
        $date = date('Ymd',$date);
        $result = $this->database->query($query,$date,$storage_id);
        $count = $this->database->numRows($result);
        return ($count > 0) ? true : false;
    }
}