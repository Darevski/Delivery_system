<?php

/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 0:59
 */

namespace Application\Exceptions;
use Application\Core\View;

/**
 * Main_Except general class of exception handling
 * @package Application\Exceptions
 */
class Main_Except extends \Exception{
    /**
     * Output Information about error
     * @param $data
     */
    protected function print_error($data){
        View::display_errors($data);
    }

    /**
     * Logging information about error
     * @param $error
     */
    protected function action_log($error){

    }
}