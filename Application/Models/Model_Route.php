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
use Application\Units\FPDF;

/**
 * Class Model_Route include business logic related with routes
 * @package Application\Models
 */
class Model_Route extends Model{

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
        // Конвертация Времени указанного в конфиге в секунды
        $config = Config::get_instance()->get_route_algorithm();
        list($hours, $min, $sec) = explode(':', $config['time_for_delivery']);
        $this->time_for_delivery = ($hours * 3600 ) + ($min * 60 ) + $sec;
        list($hours, $min, $sec) = explode(':', $config['time_start_delivery']);
        $this->time_start_delivery = ($hours * 3600 ) + ($min * 60 ) + $sec;
    }

    /**
     * Feature is in development
     * Don`t use it
     */
   public function get_pdf($date){
        $pdf_unit = new FPDF();
        $model_orders = new Model_Orders();
        $pdf_unit->SetTitle('Маршруты доставки');
        $pdf_unit->SetAuthor('Delivery_System');
        $pdf_unit->SetTextColor(50,60,100);

        $routes = $this->get_route_by_date($date);
        $height = 20;
        foreach ($routes as $key=>$route){
            $hours = floor($route['total_time']/3600);
            $minutes = floor(($route['total_time'] - $hours*3600)/60);
            $route_number = $key+1;

            $pdf_unit->AddPage('P');
            $pdf_unit->SetFont('Helvetica','B',20);
            $pdf_unit->SetXY(20,$height);
            $pdf_unit->Write(10,"Маршрут №$route_number занимает времени: $hours:$minutes");

            $height+=20;
            foreach ($route['points'] as $point){
                $pdf_unit->SetXY(50,$height);
                $pdf_unit->SetFont('Helvetica','',14);
                $pdf_unit->Write(10,'Адрес доставки: ');
                $model_orders->get_list_orders_by_point_id($point['point_id']);
            }
        }

    }

    /**
     * Checks an existing routes and if it`s not existing
     * calculate new route and write into database
     * @param $points array {[int point_id, int time_start , int time_end],[]....}
     * @param $timeMatrix [[int/null],[int/null].....]
     * @param $date int unix timestamp
     * @throws Model_Except
     * @return mixed
     */
    public function handling_route($points,$timeMatrix,$date){
        // Check on existing routes
        // If there is no routes then calculate new route
        if (!$this->checks_existence_route($date)) {

           $model_points = new Model_Delivery_Points();
            //check exciting points in Database
            foreach ($points as $value)
                if (!$model_points->isset_point($value['point_id']))
                    throw new Model_Except("Одной из выбранных точек не существует в БД");
            // Get paths by algorithm
            $ways = $this->calculate_route($points, $timeMatrix);
            $tracks = array();

            // get info about delivery point && path
            foreach ($ways as $way) {
                foreach ($way['path'] as $dot) {
                    $point_info = $model_points->get_info_about_point($points[$dot['index_point']]['point_id']);
                    $point['point_id'] = $points[$dot['index_point']]['point_id'];
                    $point['latitude'] = $point_info['point_info']['latitude'];
                    $point['longitude'] = $point_info['point_info']['longitude'];
                    //$point['time_start'] = $point_info['point_info']['time_start'];
                    //$point['time_end'] = $point_info['point_info']['time_end'];
                    $point['address'] = $point_info['point_info']['street'] . ' ' . $point_info['point_info']['house'];
                    $point['time'] = $dot['time'];
                    $route['points'][] = $point;
                }
                $route['total_time']=$way['total_time'];
                $tracks[] = $route;
                $route = array();
            }
            //delete useless variables
            unset($ways,$route,$point,$point_info,$value,$way,$dot);

            // save calculated routes into database
            $this->save_route($tracks, $date);
        }
    }

    /**
     * get routes by selected date
     * @param int $date unix timestamp
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
     */
    public function get_route_by_date($date){
        $query = "SELECT routes FROM Routes WHERE calculating_date=?s";
        $date = date('Ymd',$date);
        $routes = unserialize(base64_decode($this->database->getRow($query,$date)['routes']));
        if ($routes == false)
            $routes = array();
        return $routes;
    }

    /**
     * Calculate routes by points and time matrix
     * @param $points
     * @param $timeMatrix
     * @return array
     * @throws Model_Except
     */
    private function calculate_route($points,$timeMatrix){
        $used_points = array();
        for ($i = 0; $i< count($points); $i++)
            $used_points[$i]=false;

        $path = array();
        $calculation = true;
        // Check that we can enter this point from warehouse
        for ($i =0 ;$i< count($timeMatrix[0]);$i++)
            if ($timeMatrix[0][$i] != null)
                if ($timeMatrix[0][$i] + $this->time_start_delivery > $points[$i-1]['time_end'])
                    throw new Model_Except("Для точки $i задано не выполнимое условие по времени доставки");

        while ($calculation){
            $bestPath = $this->routeByTime(-1, $this->time_start_delivery, array(),$used_points, $timeMatrix, $points);
            // Запись использованных точек
            $used_points = $bestPath['usedArray'];
            // Запись пути и его общего времени
            $path_info['path'] = $bestPath['path'];
            $path_info['total_time'] = $bestPath['time'];
            $path[] = $path_info;

            $calculation = false;
            // Проверка на то что все точки пройдены
            foreach ($used_points as $value)
                if (!$value){
                    $calculation = true;
                    break;
                }
        }
        return $path;
    }

    /**
     * Sort descending Callback
     * @param $a null/int
     * @param $b null/int
     * @return int
     */
    private function callback_sort_by_time($a,$b){
        return (($a['time']==null) || ($b['time'] == null)) ? (($a['time'] == null) ? 1 : -1) : ($a['time'] - $b['time']);
    }

    /**
     * Recursive algorithm of calculating Route
     * Магия в чистом виде, не трогать и не пытаться понять
     * @param $position
     * @param $time
     * @param $path
     * @param $usedArray
     * @param $timeMatrix
     * @param $points
     * @return array|null
     */
    private function routeByTime($position,$time,$path,$usedArray,$timeMatrix,$points){
        // Определение точки из которой двигаемся -1 - склад
        ($position != -1) ? ($usedArray[$position-1]=true) : ($position = 0);

        $canmove = false;
        $temp = array();
        $answer = array('usedArray' => $usedArray,
                        'path' => $path,
                        'time' => $time);
        // создание массива содержащего строку из матрицы времени
        for ($i = 0; $i < count($timeMatrix);$i++)
            $temp[] = array('index' =>$i, 'time' => $timeMatrix[$position][$i]);

        // сортировка по возрастанию
        usort($temp,array($this,'callback_sort_by_time'));

        $best = null;
        for ($i = 0; $i < count($temp);$i++){
            // Если мы можем попаст в точку
            if ($temp[$i]['time']!= null)
                // и это точка доступна
                if (!$usedArray[$temp[$i]['index']-1])
                    // Если курьер поподает по времени
                    if ($time + $temp[$i]['time'] < $points[$temp[$i]['index']-1]['time_end']){
                        // ожидаем при приезде раньше времени

                        $time_temp = $time;
                        ($position == 0) ? ($time_temp+=$temp[$i]['time']) : ($time_temp+=$temp[$i]['time']+$this->time_for_delivery);
                        if ($time + $temp[$i]['time'] < $points[$temp[$i]['index']-1]['time_start'])
                            $time_temp = $points[$temp[$i]['index']-1]['time_start']+$this->time_start_delivery;

                        $canmove = true;

                        // добавляем точку в путь
                        $_path =  $path;
                        $path_info['index_point']= $temp[$i]['index']-1;
                        $path_info['time'] = $time + $temp[$i]['time'];
                        $_path[] = $path_info;
                        // Рекурсивная обработка с учетом времени затраченного на доставку
                        $temp_path = $this->routeByTime($temp[$i]['index'],($time + $temp[$i]['time']+$this->time_for_delivery),$_path, $usedArray,$timeMatrix,$points);
                        if ($best == null)
                            $best = $temp_path;
                        else
                            if (count($best['path']) < count($temp_path['path']))
                                if (count($temp_path['path'])*($best['time']/count($best['path'])) > ($temp_path['time']/count($temp_path['path'])))
                                    $best = $temp_path;

                    }
        }
        if (!$canmove){
            return $answer;
        }
        else
            return $best;
    }

    /**
     * Save serialized routes into database
     * @param $data
     * @param $date
     */
    private function save_route($data,$date){
        $query = "INSERT INTO Routes (calculating_date,routes) VALUES (?s,?s)";
        $date = date('Ymd',$date);
        $serialized_data = base64_encode(serialize($data));
        $this->database->query($query,$date,$serialized_data);
    }

    /** Checks that routes in selected day is existing
     * @param $date
     * @return bool
     */
    private function checks_existence_route($date){
        $query = "SELECT 1 FROM Routes WHERE calculating_date=?s LIMIT 1";
        $date = date('Ymd',$date);
        $result = $this->database->query($query,$date);
        $count = $this->database->numRows($result);
        return ($count > 0) ? true : false;
    }
}