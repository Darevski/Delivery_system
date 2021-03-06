<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 23.03.16
 * Time: 19:50
 */

namespace Application\Exceptions;


class Server_Error_Except extends Main_Except{
    /**
     * Error detection code and outputting appropriate page
     * @param Server_Error_Except $error
     */
    public function exception_handling(Server_Error_Except $error){
        $data['title'] = '500 Internal Server Error';
        $data['message'] = 'Внутренняя ошибка сервера';
        $data['debug_message'] = sprintf("error initialized in %s on line %s <br>
                                          with message '%s' <br>",
                                 $error->getFile(),$error->getLine(),$error->getMessage());
        $data['response_code'] = 500;
        $this->print_error($data);
        $this->action_log($error);
    }
}