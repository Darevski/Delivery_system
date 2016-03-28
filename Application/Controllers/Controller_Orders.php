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
     * create an object of Model_Orders and insert it to $Model_orders
     * Controller_Orders constructor.
     */
    public function __construct(){
        $this->Model_orders = new Model_Orders();
    }

    /**
     * Add order into database with description and cost
     * Structure of Json_input{
     *  int Point_id  - unique id from Delivery_point Database Table
     *  string Description
     *  double cost
     * }
     * @api 'Server/Orders/add_order'
     * @throws UFO_Except
     */
    public function action_add_order(){
        //$_POST['Json_input'] = '{"point_id":1,"description":"Товар №3","cost":252.234}';
        if (isset($_POST['Json_input'])){
            $input_json = json_decode($_POST['Json_input'], JSON_UNESCAPED_UNICODE);
            if (is_null($input_json))
                throw new UFO_Except('Not valid JSON',400);
        }
        else throw new UFO_Except('Json_input not found',400);

        // Secure input data
        $data=$this->secure_array($input_json);
        unset($input_json);
        $key_map = array('point_id','description','cost');
        if ($this->check_array_keys($key_map,$data)){
            // if all checks are successful we are call model method
            $result =$this->Model_orders->add_order($data);
            View::output_json($result);
        }
        else
            throw new UFO_Except('incorrect JSON values',400);
    }

    /**
     * Delete Order from DataBase
     * Structure of Json_input{ int 'order_id' }
     * @api 'Server/Orders/delete_order'
     * @throws UFO_Except
     * @throws \Application\Exceptions\Model_Except
     */
    public function action_delete_order(){
        //$_POST['Json_input'] = '{"order_id":1}';
        if (isset($_POST['Json_input'])){
            $input_json = json_decode($_POST['Json_input'], JSON_UNESCAPED_UNICODE);
            if (is_null($input_json))
                throw new UFO_Except('Not valid JSON',400);
        }
        else throw new UFO_Except('Json_input not found',400);

        // Secure input data
        $data=$this->secure_array($input_json);
        unset($input_json);
        $key_map = array('order_id');
        if ($this->check_array_keys($key_map,$data)){
            $order_id = $data['order_id'];
            // if all checks are successful we are call model method
            $result =$this->Model_orders->delete_order($order_id);
            View::output_json($result);
        }
        else
            throw new UFO_Except('incorrect JSON values',400);
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
        //$_POST['Json_input'] = '{"order_id":1,"description":"Описание №1","cost":250}';
        if (isset($_POST['Json_input'])){
            $input_json = json_decode($_POST['Json_input'], JSON_UNESCAPED_UNICODE);
            if (is_null($input_json))
                throw new UFO_Except('Not valid JSON',400);
        }
        else throw new UFO_Except('Json_input not found',400);

        // Secure input data
        $data=$this->secure_array($input_json);
        unset($input_json);
        $key_map = array('order_id','cost','description');
        if ($this->check_array_keys($key_map,$data)){
            // if all checks are successful we are call model method
            $result =$this->Model_orders->update_order($data);
            View::output_json($result);
        }
        else
            throw new UFO_Except('incorrect JSON values',400);
    }

    /**
     * Output list orders of selected delivery point
     * structure of Json_input { int Point_id }
     * structure of output:
     * orders[ { integer 'order_id', string 'description', integer 'cost'} ]
     *
     * @api 'Server/Orders/get_list_orders_by_point_id'
     * @throws UFO_Except
     */
    public function action_get_list_orders_by_point_id(){
        //$_POST['Json_input'] = '{"point_id":1}';
        if (isset($_POST['Json_input'])){
            $input_json = json_decode($_POST['Json_input'], JSON_UNESCAPED_UNICODE);
            if (is_null($input_json))
                throw new UFO_Except('Not valid JSON',400);
        }
        else throw new UFO_Except('Json_input not found',400);

        // Secure input data
        $data=$this->secure_array($input_json);
        unset($input_json);
        $key_map = array('point_id');
        if ($this->check_array_keys($key_map,$data)){
            $point_id = $data['point_id'];
            // if all checks are successful we are call model method
            $result =$this->Model_orders->get_list_orders_by_point_id($point_id);
            View::output_json($result);
        }
        else
            throw new UFO_Except('incorrect JSON values',400);
    }
}