<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 01.06.16
 * Time: 0:31
 */

namespace Application\Controllers;


use Application\Core\Controller;
use Application\Core\View;
use Application\Units\Authentication;


/**
 * Controlls functions of settings for users
 * Class Controller_Settings
 * @package Application\Controllers
 */
class Controller_Settings extends Controller{

    /**
     * Controller_Settings constructor.
     */
    public function __construct() {
        parent::__construct();
        $auth= new Authentication();
        $auth->access_check(1);
    }


    /**
     * Display html code settings page
     */
    public function action_start(){
        View::display("adminPanel.html");
    }

}