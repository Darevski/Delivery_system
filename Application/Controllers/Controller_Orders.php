<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 2:20
 */

namespace Application\Controllers;

use Application\Core\Controller;
use Application\Core\View;
use Application\Exceptions\UFO_Except;
use Application\Models\Model_Orders;
use Application\Units\Filter_Unit;

/**
 * Class Controller_orders Controls actions related with orders
 * @package Application\Controllers
 */
class Controller_Orders extends Controller{

    /**
     * Model with logic responsible by order`s
     * @var Model_Orders
     */
    private $Model_orders;

    /**
     * Unit that provides a filtering functions
     * @var Filter_Unit
     */
    private $Filter_unit;

    /**
     * create an object of Model_Orders and insert it to $Model_orders
     * Controller_Orders constructor.
     */
    public function __construct(){
        $this->Model_orders = new Model_Orders();
        $this->Filter_unit = new Filter_Unit();
    }

    /**
     * Add order into database with description and cost and return his id
     * Structure of Json_input{
     *  int Point_id  - unique id from Delivery_point Database Table
     *  string Description
     *  double cost
     * }
     * structure of output {order_id - id of inserted order, state = success - all is ok}
     * @api 'Server/Orders/add_order'
     * @throws UFO_Except
     * @throws \Application\Exceptions\Model_Except
     */
    public function action_add_order(){

        // Example Post Request
        //$input_json = '{"point_id":1,"description":"Товар №3","cost":252}';

        $input_json = filter_input(INPUT_POST,'Json_input',FILTER_DEFAULT);

        // checks validity and decode input JSON
        $decoded_json = $this->Filter_unit->decode_Json($input_json);

        $validate_map = array(
            'point_id' => array('filter'=>FILTER_VALIDATE_INT, 'flags'=>FILTER_NULL_ON_FAILURE),
            'description' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW),
            'cost' => array('filter'=>FILTER_VALIDATE_FLOAT, 'flags'=>FILTER_NULL_ON_FAILURE)
        );

        $valid_arr = $this->Filter_unit->filter_array($decoded_json,$validate_map);

        $order_id =$this->Model_orders->add_order($valid_arr['point_id'],$valid_arr['description'],$valid_arr['cost']);

        $output['order_id'] = $order_id;
        $output['state']='success';
        View::output_json($output);
    }

    /**
     * Delete Order from DataBase
     * Structure of Json_input{ int 'order_id' }
     * @api 'Server/Orders/delete_order'
     * @throws UFO_Except
     * @throws \Application\Exceptions\Model_Except
     */
    public function action_delete_order(){
        // Example of Json_input
        //$input_json = '{"order_id":1}';

        $input_json = filter_input(INPUT_POST,'Json_input',FILTER_DEFAULT);
        // checks validity and decode input JSON
        $decoded_json = $this->Filter_unit->decode_Json($input_json);

        $order_id = filter_var($decoded_json['order_id'],FILTER_VALIDATE_INT,FILTER_NULL_ON_FAILURE);

        if (is_null($order_id))
            throw new UFO_Except("incorrect Json value 'order_id' ",400);
        // if all checks are successful we are call model method
        $this->Model_orders->delete_order($order_id);
        View::output_json(array('state'=>'success'));

    }

    /**
     * Update order parameters in database
     * structure of Json_input{
     *  int order_id
     *  string description
     *  float cost
     * }
     * @api 'Server/Orders/update_order'
     * @throws UFO_Except
     * @throws \Application\Exceptions\Model_Except
     */
    public function action_update_order(){
        // Example Post Request
        //$input_json = '{"order_id":2,"description":"Товар №3","cost":352.14 }';

        $input_json = filter_input(INPUT_POST,'Json_input',FILTER_DEFAULT);

        // checks validity and decode input JSON
        $decoded_json = $this->Filter_unit->decode_Json($input_json);

        $validate_map = array(
            'order_id' => array('filter'=>FILTER_VALIDATE_INT, 'flags'=>FILTER_NULL_ON_FAILURE),
            'description' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW),
            'cost' => array('filter'=>FILTER_VALIDATE_FLOAT, 'flags'=>FILTER_NULL_ON_FAILURE)
        );

        $valid_arr = $this->Filter_unit->filter_array($decoded_json,$validate_map);

        // if all checks are successful we are call model method
        $this->Model_orders->update_order($valid_arr['order_id'],$valid_arr['description'],$valid_arr['cost']);
        View::output_json(array('state'=>'success'));
    }

    /**
     * Output list orders of selected delivery point
     * structure of Json_input { int Point_id }
     * structure of output:
     * orders[ { integer 'order_id', string 'description', integer 'cost'} ]
     * string 'state' = 'success' - all is ok
     * @api 'Server/Orders/get_list_orders_by_point_id'
     * @throws UFO_Except
     */
    public function action_get_list_orders_by_point_id(){
        // Example of Json_input
        //$input_json = '{"point_id":1}';

        $input_json = filter_input(INPUT_POST,'Json_input',FILTER_DEFAULT);
        // checks validity and decode input JSON
        $decoded_json = $this->Filter_unit->decode_Json($input_json);

        $point_id = filter_var($decoded_json['point_id'],FILTER_VALIDATE_INT,FILTER_NULL_ON_FAILURE);

        if (is_null($point_id))
            throw new UFO_Except("incorrect Json value 'point_id' ",400);
        // if all checks are successful we are call model method
        $orders =$this->Model_orders->get_list_orders_by_point_id($point_id);
        $output['orders'] = $orders;
        $output['state'] = 'success';
        View::output_json($output);
    }
}