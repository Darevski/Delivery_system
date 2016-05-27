<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 2:36
 */

namespace Application\Controllers;
use Application\Core\Controller;
use Application\Core\View;
use Application\Exceptions\UFO_Except;
use Application\Models\Model_Delivery_Points;
use Application\Units\Authentication;

/**
 * Class Controller_Points Controls actions related with delivery points
 * @package Application\Controllers
 */
class Controller_Points extends Controller{
    /**
     * Model with logic responsible by point`s (Delivery place`s)
     * @var Model_Delivery_Points
     */
    private $Model_Points;

    /**
     * create an object of Model_Delivery_Points
     * Controller_Points constructor.
     * Access level - 1 registered user
     */
    public function __construct(){
        parent::__construct();
        $this->Authentication->access_check(1);
        $this->Model_Points = new Model_Delivery_Points();
    }

    /**
     * Adding empty point to database and output identifier of this point and unique point id
     * Example of output {"point_id":1,"identifier_order":"20160321#2"}
     * @api 'server/Points/add_empty_point'
     */
    public function action_add_empty_point(){
        $point_info = $this->Model_Points->add_empty_point();
        $output = $point_info;
        $output['state']='success';
        View::output_json($output);
    }

    /**
     * Fill information about a specified delivery Point
     * Structure of JSON_Input
     * address{
     *  string street
     *  string house - house with block for ex. 'Рафиева 83к1' or 'Рафиева 113'
     *  string entry
     *  string note
     *  int floor
     *  int flat
     * }
     * int(12) phone for example '375291234567'
     * time {
     *  string start: "H:i:s"
     *  string end: "H:i:s"
     * }
     * unix timestamp delivery_date
     * int point_id - Unique value (Points_ID) from database Delivery_Points
     * @api 'server/Points/fill_empty_point'
     * @throws \Application\Exceptions\Model_Except
     * @throws \Application\Exceptions\UFO_Except
     */
    public function action_fill_point(){

        // Example Post Request
        /*
        $input_json = '{"address":
                        {"street":"ул. Рафиева","house":"113","entry":"5","floor":4,"flat":159},
                       "point_id":2, "note":"Нерабочий домофон",
                       "time":{"start":"18:00:00","end":"20:15:00"},
                       "phone":375297768637,
                       "delivery_date":1459448664
                       }';
        */
        $input_json = filter_input(INPUT_POST,'Json_input',FILTER_DEFAULT);

        // checks validity and decode input JSON
        $decoded_json = $this->Filter_unit->decode_Json($input_json);

        $validate_map = array(
            'address/street' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW),
            'address/house' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW),
            'address/entry' => array('filter'=>FILTER_VALIDATE_INT, 'flags'=>FILTER_NULL_ON_FAILURE),
            'address/floor' => array('filter'=>FILTER_VALIDATE_INT, 'flags'=>FILTER_NULL_ON_FAILURE),
            'address/flat' => array('filter'=>FILTER_VALIDATE_INT, 'flags'=>FILTER_NULL_ON_FAILURE),
            'point_id' => array('filter'=>FILTER_VALIDATE_INT, 'flags'=>FILTER_NULL_ON_FAILURE),
            'note' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW),
            'time/start' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW),
            'time/end' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW),
            'phone' => array('filter'=>FILTER_VALIDATE_INT, 'flags'=>FILTER_NULL_ON_FAILURE),
            'delivery_date' =>array('filter'=>FILTER_VALIDATE_INT, 'flags'=>FILTER_NULL_ON_FAILURE),
        );

        $valid_arr = $this->Filter_unit->filter_array($decoded_json,$validate_map);

        //checking for incorrect time
        if ($this->Filter_unit->time_check($valid_arr['time/start']) === false ||
            $this->Filter_unit->time_check( $valid_arr['time/end']) === false)
            throw new UFO_Except("Incorrect time in Json",400);
        //checking for incorrect date
        if ($this->Filter_unit->date_check($valid_arr['delivery_date']) === false)
            throw new UFO_Except("Incorrect date in Json",400);

        $this->Model_Points->fill_point($valid_arr['address/street'],
                                        $valid_arr['address/house'],
                                        $valid_arr['address/entry'],
                                        $valid_arr['address/floor'],
                                        $valid_arr['address/flat'],
                                        $valid_arr['point_id'],
                                        $valid_arr['note'],
                                        $valid_arr['time/start'],
                                        $valid_arr['time/end'],
                                        $valid_arr['phone'],
                                        $valid_arr['delivery_date']);
        View::output_json(array('state'=>'success'));
    }

    /**
     * Delete Delivery point with related orders from database
     * Structure of Json_input{int point_id}
     * @api 'Server/Points/delete_point'
     * @throws UFO_Except
     * @throws \Application\Exceptions\Model_Except
     */
    public function action_delete_point(){
        // Example of Json_input
        //$input_json = '{"point_id":1}';

        $input_json = filter_input(INPUT_POST,'Json_input',FILTER_DEFAULT);
        // checks validity and decode input JSON
        $decoded_json = $this->Filter_unit->decode_Json($input_json);

        $point_id = filter_var($decoded_json['point_id'],FILTER_VALIDATE_INT,FILTER_NULL_ON_FAILURE);

        if (is_null($point_id))
            throw new UFO_Except("incorrect Json value 'point_id' ",400);

        // if all checks are successful we are call model method
        $this->Model_Points->delete_point($point_id);
        View::output_json(array('state'=>'success'));
    }

    /**
     * Output list of all delivery point`s on selected date
     * Structure of Json_input{
     *  int unix timestamp 'delivery_date'
     * }
     * @api 'Server/Points/get_points_by_date'
     * @throws UFO_Except
     * @throws \Application\Exceptions\Model_Except
     */
    public function action_get_points_by_date(){
        //Example of Json_input
        //$input_json = '{"delivery_date":145855604800}';

        $input_json = filter_input(INPUT_POST,'Json_input',FILTER_DEFAULT);

        $decoded_json = $this->Filter_unit->decode_Json($input_json);

        $delivery_date = filter_var($decoded_json['delivery_date'],FILTER_VALIDATE_INT,FILTER_NULL_ON_FAILURE);

        if (is_null($delivery_date) || !$this->Filter_unit->date_check($delivery_date))
            throw new UFO_Except("incorrect Json value 'delivery_date' ",400);

        // if all checks are successful we are call model method
        $result =$this->Model_Points->get_points_by_date($delivery_date);
        $result['state']='success';
        View::output_json($result);
    }

    /**
     * Output info about delivery point
     * structure of Json_input {int point_id}
     * structure of output 'point_info'{
     *  float 'total_cost'
     *  string 'identifier_order'
     *  string 'street'
     *  string 'house'
     *  string 'block'
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
     * string state;
     * @throws UFO_Except
     * @throws \Application\Exceptions\Model_Except
     */
    public function action_get_info_about_point(){
        // Example of Json_input
        //$input_json = '{"point_id":"1"}';

        $input_json = filter_input(INPUT_POST,'Json_input',FILTER_DEFAULT);
        // checks validity and decode input JSON
        $decoded_json = $this->Filter_unit->decode_Json($input_json);

        $point_id = filter_var($decoded_json['point_id'],FILTER_VALIDATE_INT,FILTER_NULL_ON_FAILURE);

        if (is_null($point_id))
            throw new UFO_Except("incorrect Json value 'point_id' ",400);

        // if all checks are successful we are call model method
        $point_info =$this->Model_Points->get_info_about_point($point_id);
        $result['point_info']=$point_info;
        $result['state']='success';
        View::output_json($result);

    }
}