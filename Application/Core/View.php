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
     * @param array $response
     */
    static function display($file,$response = null){
        include 'Application/Views/'.$file;
    }

    /**
     * Display Page Contents for Errors
     * @param $data array with
     * string 'title'
     * string 'message'
     * string 'debug_message'
     * integer 'response_code'
     */
    static function display_errors($data){
        $response['title'] = $data['title'];
        $response['message'] = $data['message'];
        $response['response_code'] = $data['response_code'];
        $response['json'] = self::get_json($response);
        // if type of build is debug the output information is visible on the screen
        if (Config::get_instance()->get_build()['Build'] == 'Debug') {
            $response['debug_message'] = $data['debug_message'];
            $response['display_view'] = 'block';
        }
        else
            $response['display_view'] = 'none';

        self::display('Error_View.php',$response);
    }

    /**
     * Output data in json string
     * @param $data
     */
    static function output_json($data){
        $response['json'] = self::get_json($data);
        // if type of build is debug the output information is visible on the screen
        if (Config::get_instance()->get_build()['Build'] == 'Debug')
            $response['display_view'] = 'block';
        else
            $response['display_view'] = 'none';

        self::display('Output_View.php',$response);
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
        $result['data'] = $data;
        $result['md5'] = $md5;
        $result=json_encode($result,JSON_UNESCAPED_UNICODE );
        return $result;
    }
}