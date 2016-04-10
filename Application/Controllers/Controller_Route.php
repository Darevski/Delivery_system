<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 09.04.16
 * Time: 16:06
 */

namespace Application\Controllers;

use Application\Core\Controller;
use Application\Core\View;
use Application\Exceptions\UFO_Except;
use Application\Models\Model_Route;
use Application\Units\Filter_Unit;

/**
 * Class Controller_Route
 * @package Application\Controllers
 */
class Controller_Route extends Controller
{

    /**
     * Model with logic of Routes calculating
     * @var Model_Route
     */
    private $Model_Route;

    /**
     * Unit that provides a filtering functions
     * @var Filter_Unit
     */
    private $Filter_unit;

    /**
     * create an object of Model_Delivery_Points
     * Controller_Points constructor.
     */
    public function __construct(){
        $this->Model_Route = new Model_Route();
        $this->Filter_unit = new Filter_Unit();
    }

    /**
     * Calculated routes based on points
     * Structure of Json_input{
     *                      "points":[int point_id, int time_start , int time_end]}
     *                      "timeMatrix":[[int/null],[int/null].....]
     *                      "date": int unix timestamp
     * @api 'server\Route\calculation'
     * @throws \Application\Exceptions\UFO_Except
     * @throws \Application\Exceptions\Model_Except
     */
    public function action_calculation(){
        // Example Post Request
        /*
        $input_json = '
        {"points": [{"point_id":1,"time_start":64800,"time_end":67500},
                    {"point_id":2,"time_start":72000,"time_end":79200},
                    {"point_id":3,"time_start":68400,"time_end":72000},
                    {"point_id":4,"time_start":61200,"time_end":79200},
                    {"point_id":5,"time_start":64800,"time_end":70200},
                    {"point_id":6,"time_start":61200,"time_end":79200},
                    {"point_id":7,"time_start":61200,"time_end":79200},
                    {"point_id":8,"time_start":61200,"time_end":68400}],
        "timeMatrix": [[null,1656.12,734.27,1112.47,580.27,1332.96,1363.7,1388.85,967.64],
                       [null,null,1764.67,1008.5,1453.29,1976.4,1088.95,1464.54,1557.95],
                       [null,1760.91,null,1419.62,606.87,773.62,1283.16,1166.9,1322.79],
                       [null,973.96,1579.36,null,1210.7,1729.83,1305.59,1681.17,1264.21],
                       [null,1462.83,539.7,983.06,null,1138.39,1007.56,1198.14,958.97],
                       [null,2087.04,1253.93,2018.62,1205.87,null,1655.65,1493.8,1731.95],
                       [null,1026.58,1113.47,1222.81,986.68,1583.84,null,732.52,1561.03],
                       [null,868.78,1423.27,1352.77,1375.28,1462.55,437.39,null,1902.22],
                       [null,1756.53,1169.13,1109.37,950.37,1644.08,1576.06,1823.72,null]],
        "date":1459448664
        }';
        */

        $input_json = filter_input(INPUT_POST,'Json_input',FILTER_DEFAULT);
        $decoded_json = $this->Filter_unit->decode_Json($input_json);


        $validate_points_map = array(
            'point_id' => array('filter'=>FILTER_VALIDATE_INT, 'flags'=>FILTER_NULL_ON_FAILURE),
            'time_start' => array('filter'=>FILTER_VALIDATE_INT, 'flags'=>FILTER_NULL_ON_FAILURE),
            'time_end' => array('filter'=>FILTER_VALIDATE_INT, 'flags'=>FILTER_NULL_ON_FAILURE),
        );

        $points_arr = array();
        foreach ($decoded_json['points'] as $value)
            $points_arr[]= $this->Filter_unit->filter_array($value,$validate_points_map);

        $validate_map = array(
            'timeMatrix' => array('filter'=>FILTER_VALIDATE_FLOAT, 'flags'=>FILTER_REQUIRE_ARRAY | FILTER_NULL_ON_FAILURE),
            'date' =>array('filter'=>FILTER_VALIDATE_INT, 'flags'=>FILTER_NULL_ON_FAILURE)
        );

        $valid_arr = $this->Filter_unit->filter_array($decoded_json,$validate_map);

        // Checking that date is correct
        if ($this->Filter_unit->date_check($valid_arr['date']) === false)
            throw new UFO_Except("Incorrect date in Json",400);


        $this->Model_Route->handling_route($points_arr,$valid_arr['timeMatrix'],$valid_arr['date']);
        View::output_json(array('state'=>'success'));
    }

    /**
     * Return Routes (if existing) on selected date
     * Structure of Json_input{ "date": int unix timestamp }
     * structure of output{ [
     *      points:[
     *          {
     *              float latitude,
     *              float longitude,
     *              string time_start,
     *              string time_end,
     *              string address,
     *              float time
     *          }
     *     float total_time] ] ...}
     *
     * @api 'server/Route/get_routes'
     * @throws UFO_Except
     */
    public function action_get_routes(){
        // Example Post Request
        //$input_json = '{"date":1459448664}';

        $input_json = filter_input(INPUT_POST,'Json_input',FILTER_DEFAULT);
        $decoded_json = $this->Filter_unit->decode_Json($input_json);

        $validate_map = array(
            'date' =>array('filter'=>FILTER_VALIDATE_INT, 'flags'=>FILTER_NULL_ON_FAILURE)
        );

        $valid_arr = $this->Filter_unit->filter_array($decoded_json,$validate_map);
        // Checking that date is correct
        if ($this->Filter_unit->date_check($valid_arr['date']) === false)
            throw new UFO_Except("Incorrect date in Json",400);

        $routes = $this->Model_Route->get_route_by_date($valid_arr['date']);
        $result['routes'] = $routes;
        $result['state'] = 'success';
        View::output_json($result);
    }


    /**
     * Display route calculation interface
     * @api 'Server/Route'
     */
    public function action_start(){
        View::display('routes.html');
    }
}