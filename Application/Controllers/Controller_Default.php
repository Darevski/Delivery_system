<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 1:29
 */

namespace Application\Controllers;

use Application\Core\Controller;
use Application\Core\View;

/**
 * Class Controller_Default Default controller who display Start page
 * @package Application\Controllers
 */
class Controller_Default extends Controller{
    // Display Start Page
    public function action_start(){
        View::display('index.html');
    }
}