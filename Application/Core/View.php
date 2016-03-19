<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 0:55
 */

namespace Application\Core;
use Application\Core\System\Config;

/**
 * Class View implements data output
 * @package Application\Core
 */
class View {

    /**
     * Display data with selected file
     * If data is empty just a file display without data
     * @param $file
     * @param null $data
     */
    static function display($file,$data = null){
        include 'Application/Views/'.$file;
    }

    /**
     * Output data in json string
     * @param $data
     */
    static function output_json($data){
        $output_data = self::generate_json($data);
        // if type of build is debug the output information is visible on the screen 
        if (Config::get_instance()->get_build()['debug'])
            $data['display_view'] = 'block';
        else
            $data['display_view'] = 'none';
        self::display('Output_View.php',$output_data);
    }

    /**
     * Get json string by data
     * @param $data
     * @return string
     */
    static function get_json($data){
        return self::generate_json($data);
    }

    /**
     * generate json string with md5 signature
     * @param $data mixed
     * @return string json {data}{md5}
     */
    private static function generate_json($data){
        $json_data=json_encode($data,JSON_UNESCAPED_UNICODE );
        $md5 = md5($json_data);
        $result['data'] = $json_data;
        $result['md5'] = $md5;
        $result=json_encode($result,JSON_UNESCAPED_UNICODE );
        return $result;
    }
}