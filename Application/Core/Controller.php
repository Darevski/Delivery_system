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
            else if (!is_int($value) && !is_null($value)){
                $value=htmlentities($value);
                $value=strip_tags($value);
            }
        }
        unset($value);
        return $array;
    }

    /**
     * check of the existence the specified keys in the array
     * @param $key_map array with string keys that must be included in array
     * @param $array mixed checked array
     * @return bool true - all keys from map are included in specified array
     *              false - not all keys from map are included in specified array
     */
    protected function check_array_keys($key_map,$array){
        foreach($key_map as $key_name)
            if (!$this->search_key_in_array($key_name,$array))
                return false;
        return true;
    }

    /**
     * Search entry key with specified name
     * @param $key_name
     * @param $array
     * @return bool true key with name like $key_name exist in array
     *              matches does not found
     */
    private function search_key_in_array($key_name,$array){
        $match = false;
        foreach ($array as $key=>$value){
            if (is_array($value) && ($this->search_key_in_array($key_name,$value)))
                $match = true;
            else if ($key === $key_name)
                $match = true;
        }
        return ($match == true) ? true : false;
    }
}