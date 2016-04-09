<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 09.04.16
 * Time: 17:25
 */

namespace Application\Models;


use Application\Core\Model;

/**
 * Class Model_Route include business logic related with routes
 * @package Application\Models
 */
class Model_Route extends Model{
//ATTENTION THIS IS BIG BULLSHIT
// DON`T try UNDERSTAND IT
    public function calculate_route($points,$timeMatrix){
        $used_points = array();
        for ($i = 0; $i< count($points); $i++)
            $used_points[$i]=false;

        $calculation = true;
        while ($calculation){
            $calculation = false;


            foreach ($used_points as $value)
                if (!$value){
                    $calculation = true;
                    break;
                }

            if ($calculation){
                $result = $this->routeByTime(-1,64800,[],$used_points,$timeMatrix,$points);
                $used_points = $result['usedArray'];
                if (!is_null($result['path']))
                    $path[]=$result['path'];
            }

        }

        $execute_result['path'] = $path;
        $execute_result['state'] = 'success';
        return $execute_result;
    }

    private function callback_sort_by_time($a,$b){
        return (($a['time']==null) || ($b['time'] == null)) ? (($a['time'] == null) ? 1 : -1) : ($b['time'] - $a['time']);
    }

    private function routeByTime($position,$time,$path,$usedArray,$timeMatrix,$points){
        ($position != -1) ? ($usedArray[$position-1]=true) : ($position = 0);

        $canmove = false;
        $temp = array();
        for ($i = 0; $i < count($timeMatrix);$i++)
            $temp[] = array('index' =>$i, 'time' => $timeMatrix[$position][$i]);

        usort($temp,array($this,'callback_sort_by_time'));

        $best_value = array ('path'=>-1 , 'usedArray'=>null);
        for ($i = 0; $i < count($temp);$i++){
            if ($temp[$i]['time']!= null)
                if (!$usedArray[$temp[$i]['index']-1])

                    if ($time + $temp[$i]['time'] < $points[$temp[$i]['index']-1]['time_end']){
                        // ожидаем при приезде раньше времени
                        if ($time + $temp[$i]['time'] < $points[$temp[$i]['index']-1]['time_start'])
                            $time = $points[$temp[$i]['index']-1]['time_start'];
                        $canmove = true;

                        $_path =  $path;
                        $_path[] = $temp[$i]['index']-1;
                        $best_temp = $this->routeByTime($temp[$i]['index'],($time + $temp[$i]['time']+900),$_path, $usedArray,$timeMatrix,$points);
                        //if ($best_value['path'] < $best_temp['path'])
                        $best_value = $best_temp;
                    }
        }
        if (!$canmove){
            $best['path'] = $path;
            $best['usedArray'] = $usedArray;
            return $best;
        }
        else{
            $best = $best_value;
            return $best;
        }
    }
// END OF BULL SHIT
}