<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 27.05.16
 * Time: 22:20
 */

namespace Application\Exceptions;


class Auth_Except extends Main_Except{

    /**
     * Error detection code and outputting appropriate page
     * @param Server_Error_Except $error
     */
    public function exception_handling(Auth_Except $error){
        //destructed session
        switch ($error->getCode()) {
            case 403:  // не подходящий пароль
                $data['title'] = '403 Forbidden';
                $data['message'] = $error->getMessage();
                $data['debug_message'] = sprintf("error initialized in %s on line %s <br>
                                          with message '%s' <br>",
                $error->getFile(),$error->getLine(),$error->getMessage());
                $data['response_code'] = 403;
                $this->print_error($data);
            break;

            case 4030:
                $data['title'] = '403 Forbidden';
                $data['message'] = $error->getMessage();
                $data['debug_message'] = sprintf("error initialized in %s on line %s <br>
                                          with message '%s' <br>",
                $error->getFile(),$error->getLine(),$error->getMessage());
                $data['response_code'] = 4030;
                $this->print_error($data);
            break;

            default:
                $data['title'] = '403 Forbidden';
                $data['message'] = $error->getMessage();
                $data['debug_message'] = sprintf("error initialized in %s on line %s <br>
                                          with message '%s' <br>",
                $error->getFile(),$error->getLine(),$error->getMessage());
                $data['response_code'] = 403;
                $this->print_error($data);
        }
    }

}