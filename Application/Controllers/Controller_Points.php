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
use Application\Models\Model_Delivery_Points;

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
     */
    public function __construct(){
        $this->Model_Points = new Model_Delivery_Points();
    }

    /**
     * Adding empty point to database and output identifier of this point and unique point id
     * Example of output {"point_id":1,"identifier_order":"20160321#2"}
     * @api 'server/Points/add_empty_point'
     */
    public function action_add_empty_point(){
        $point_info = $this->Model_Points->add_empty_point();
        View::output_json($point_info);
    }
}