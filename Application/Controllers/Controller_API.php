<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 31.03.16
 * Time: 2:03
 */

namespace Application\Controllers;

use Application\Core\Controller;
use Application\Core\View;

/**
 * Class Controller_API
 * @package Application\Controllers
 */
class Controller_API extends Controller{
    /**
     * Output time unix timestamp on server
     * @api 'Server/API/get_time'
     */
    public function action_get_time(){
        View::output_json(time());
    }
}