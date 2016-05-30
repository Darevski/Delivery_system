<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 0:51
 */

namespace Application\Core;

use Application\Units\Authentication;
use Application\Units\Filter_Unit;
/**
 * Controller - general class of controllers
 * @package Application\Core
 */
class Controller {

    /**
     * Unit that provides a filtering functions
     * @var Filter_Unit
     */
    protected $Filter_unit;

    /**
     * @var Authentication
     */
    protected $Authentication;

    /**
     * Controller constructor.
     */
    public function __construct() {
        $this->Authentication = new Authentication();
        $this->Filter_unit = new Filter_Unit();
    }
    
}