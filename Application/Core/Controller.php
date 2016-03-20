<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 0:51
 */

namespace Application\Core;

/**
 * Controller - general class of controllers
 * @package Application\Core
 */
class Controller {
    /**
     * trims invalid characters from variable
     * and return safe result
     * @param string array type
     * @return mixed
     */
    protected function secure_array($array){
        foreach ($array as &$value){
            if (is_array($value))
                $value=$this->secure_array($value);
            else if (!is_int($value)){
                $value=htmlentities($value);
                $value=strip_tags($value);
            }
        }
        unset($value);
        return $array;
    }
}