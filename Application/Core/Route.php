<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 0:43
 */

namespace Application\Core;
use Application\Exceptions\UFO_Except;

/**
 * Class Route transforms URL to control commands
 * @package Application\Core
 */
class Route {

    Const default_controller = 'Controller_Default';
    Const default_action = 'action_start';
    Const namespace_controllers = 'Application\Controllers\\';
    Const controller_prefix = 'Controller_';
    Const action_prefix = 'action_';

    // Function which creates class objects in accordance with the received command
    public function Start($Route_parameters){
        $controller_name = '';
        $action = '';
        // If it`s empty request, application display start_page
        if ($Route_parameters['target'] === 'Start_Page'){
            $controller_name = self::namespace_controllers.self::default_controller;
            $action = self::default_action;
        }
        // Display`s page by selected controller
        else if ($Route_parameters['target'] === 'Controller'){
            $controller_name = self::namespace_controllers.self::controller_prefix.$Route_parameters['params']['controller'];;
            $action = self::default_action;
        }
        // Or if the are some control commands with controller and action for him
        // Application Execute requested action
        else if ($Route_parameters['target'] === 'Application'){

            $controller_name = self::namespace_controllers.self::controller_prefix.
                                $Route_parameters['params']['controller'];

            $action = self::action_prefix.$Route_parameters['params']['action'];
        }
        // If there is no requested page
        // Application throws Exception With 404 code and situational message
        else
            throw new UFO_Except("System of routing hasn't found Matches",404);
        // Transform NameSpace+Class Name to File Name
        $file_name = preg_replace('/\\\/','/',$controller_name).'.php';
        // If exists controller
        if (file_exists($file_name)){
            $controller_object = new $controller_name;
            // And in him exists requested method
            // Application execute these commands
            if (method_exists($controller_object,$action))
                $controller_object->$action();
            // if the are not exist method in selected controller throws exception With 404 code and situational message
            else
                throw new UFO_Except("Method { $action } does not exist in { $controller_name }",404);

        }
        // If controller are not exist throws exception With 404 code and situational message
        else
            throw new UFO_Except("File { $controller_name } does not exist",404);

    }
}