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
        $_POST['Json_input'] = '{"point_id":1,"description":"Товар №3","cost":252.234}';
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
}