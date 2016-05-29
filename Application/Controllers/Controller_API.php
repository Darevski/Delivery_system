<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 31.03.16
 * Time: 2:03
 */

namespace Application\Controllers;

use Application\Core\Controller;
use Application\Core\View;

/**
 * Class Controller_API
 * @package Application\Controllers
 */
class Controller_API extends Controller{
    /**
     * Output time unix timestamp on server
     * @api 'Server/API/get_time'
     */
    public function action_get_time(){
        View::output_json(time());
    }

    /**
     * auth user in system
     * @throws \Application\Exceptions\Auth_Except
     * @throws \Application\Exceptions\UFO_Except
     */
    public function action_user_enter(){
        // Example Post Request
        $input_json = '{"login":"test","password":"test"}';

        //$input_json = filter_input(INPUT_POST,'Json_input',FILTER_DEFAULT);

        // checks validity and decode input JSON
        $decoded_json = $this->Filter_unit->decode_Json($input_json);

        $validate_map = array(
            'login' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW),
            'password' => array('filter'=>FILTER_SANITIZE_STRING, 'flags'=>FILTER_FLAG_STRIP_LOW)
        );

        $valid_arr = $this->Filter_unit->filter_array($decoded_json,$validate_map);

        $this->Authentication->user_enter($valid_arr['login'],$valid_arr['password']);
        
        View::output_json(array('state'=>"success"));
    }

    /**
     * user logout from system
     */
    public function action_user_exit() {
        $this->Authentication->user_exit();
        //Redirect to main page
        header("Location:/");
    }
}