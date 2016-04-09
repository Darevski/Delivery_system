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
     * @api 'server\Route\calculation'
     * @throws \Application\Exceptions\UFO_Except
     */
    public function action_calculation(){
        // Example Post Request
        $input_json = '{"points": [ {"point_id":1,"time_start":72000,"time_end":79200},
                                    {"point_id":4,"time_start":64800,"time_end":72000},
                                    {"point_id":7,"time_start":61200,"time_end":79200},
                                    {"point_id":8,"time_start":61200,"time_end":79200},
                                    {"point_id":10,"time_start":72000,"time_end":73800},
                                    {"point_id":12,"time_start":68400,"time_end":79200},
                                    {"point_id":13,"time_start":61200,"time_end":79200},
                                    {"point_id":14,"time_start":68400,"time_end":79200},
                                    {"point_id":15,"time_start":66600,"time_end":68400}],
                        "timeMatrix": [ [null,1407.23,339,701,1119,1418,833,471,905,1314],
                                        [null,null,1520,1181,1683,2156,1827,1676,1572,1552],
                                        [null,1467,null,782,1056,1200,607,168,833,1252],
                                        [null,1224,730,null,1282,1807,1216,861,1155,1569],
                                        [null,1751,1202,1492,null,1813,1465,1240,496,856],
                                        [null,2171,1174,1794,1847,null,996,1172,1606,1459],
                                        [null,1714,535,1075,1259,1016,null,527,1036,1278],
                                        [null,1429,250,870,1099,1031,446,null,870,1162],
                                        [null,1534,943,1308,373,1454,1206,981,null,639],
                                        [null,1086,1341,1447,794,1504,1390,1358,705,null]]
        }';

        //$input_json = filter_input(INPUT_POST,'Json_input',FILTER_DEFAULT);
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
            'timeMatrix' => array('filter'=>FILTER_VALIDATE_FLOAT, 'flags'=>FILTER_REQUIRE_ARRAY | FILTER_NULL_ON_FAILURE)
        );
        $matrix = $this->Filter_unit->filter_array($decoded_json,$validate_map);

        $result = $this->Model_Route->calculate_route($points_arr,$matrix['timeMatrix']);
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