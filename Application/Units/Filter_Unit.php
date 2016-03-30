<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 29.03.16
 * Time: 17:31
 */

namespace Application\Units;

use Application\Exceptions\UFO_Except;

class Filter_Unit
{
    /**
     * checks validity of Json and decode it
     * @param string $input_json in json format
     * @return mixed decoded Json
     * @throws UFO_Except
     */
    public function decode_Json($input_json)
    {
        if ($input_json && !is_null($input_json)) {
            $valid_json = json_decode($input_json, JSON_UNESCAPED_UNICODE);
            // if not valid json throw exception
            if (is_null($valid_json) || !$valid_json)
                throw new UFO_Except('Not valid JSON', 400);
        } else
            throw new UFO_Except('Json_input not found', 400);
        return $valid_json;
    }

    /**
     * @param $data array
     * @param $args array key map with validation filters
     * @return mixed array with validated data
     */
    public function filter_array($data, $args)
    {
        $ref_map = array();
        foreach ($args as $key => $a) {
            $parts = explode('/', $key);
            $ref =& $data;
            foreach ($parts as $p) $ref =& $ref[$p];
            $ref_map[$key] =& $ref;
        }
        return filter_var_array($ref_map, $args);
    }

    /**
     * checking for compliance with the format "HH:MM:SS"
     * @param $time string with time
     * @return bool
     */
    public function time_check($time){
        return (!preg_match("/^(([0,1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/", $time)) ? false : true;
    }

    /**
     * check date for compliance with unix timestamp
     * @param $date
     * @return bool
     */
    public function date_check($date){
        return (strtotime(date('d-m-Y H:i:s',$date)) === (int)$date) ? true : false;
    }
}