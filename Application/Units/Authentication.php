<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 27.05.16
 * Time: 15:29
 */

namespace Application\Units;

use Application\Core\System;
use Application\Exceptions\Auth_Except;

class Authentication {

    /**
     * @var System\Safe_SQL
     */
    private $database;

    public function __construct() {
        //get the configuration for connection to database
        $data_base_opt = System\Config::get_instance()->get_database_config();
        // receiving object for working with database
        $this->database = System\Safe_SQL::get_instance($data_base_opt);
    }

    /**
     * Checks login, password, privilege of user
     * @param $login
     * @param $password_hash
     * @param $privilege
     * @return bool
     */
    private function get_user_data($login,$password_hash,$privilege,$id){
        $query = "SELECT id,privilege FROM Company WHERE id=?s and login=?s and password=?s and privilege=?s";
        $result = $this->database->getRow($query, $id, $login, $password_hash, $privilege);
        return is_null($result) ? false : true;
    }


    /**
     * get user params from database
     * @param $login
     * @param $hash
     * @return array|null {int id,int privilege}
     */
    private function get_params($login,$hash){
        $query = "SELECT id,privilege FROM Company WHERE login=?s and password=?s";
        $result = $this->database->getRow($query, $login, $hash);
        return $result;
    }

    /**
     * Cheks userinfo updates in database
     * @throws Auth_Except
     */
    public function user_data_check(){
        if (isset($_SESSION['hash']) and isset($_SESSION['login']))
            // if  data from the database and session not the same unset current session
            // clear session storage and redirect to main page
            if (!$this->get_user_data($_SESSION['login'], $_SESSION['hash'], $_SESSION['privilege'], $_SESSION['id'])) {
                $_SESSION = array();
                throw new Auth_Except("Ошибка безопасности, перезайдите в систему", 4030);
            }
    }

    /**
     * checks the rights for access to the function
     * @privilege int priority level
     * @throws Auth_Except
     */
    public function access_check($privilege){
        $this->user_data_check();
        if ($_SESSION['privilege'] < $privilege)
            throw new Auth_Except("Доступ запрещен, отсутствие необходимых привелегий",4030);
    }

    /**
     * authorizes user in system
     * @param $login
     * @param $password
     * @throws Auth_Except
     */
    public function user_enter($login,$password){
        $hash = md5($password);
        //get user params if it`s exists
        $params = $this->get_params($login,$hash);
        if (!is_null($params)){
            $_SESSION['login'] = $login;
            $_SESSION['hash'] = $hash;
            $_SESSION['privilege'] = $params['privilege'];
            $_SESSION['id'] = $params['id'];
        }
        else
            throw new Auth_Except("Неверный пароль",403);
    }

    /**
     * Destroy Session storage
     */
    public function user_exit() {
        $_SESSION = array();
    }
}