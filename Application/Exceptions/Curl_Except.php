<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 23.03.16
 * Time: 19:44
 */

namespace Application\Exceptions;

class Curl_Except extends Main_Except{

    public function exception_handling(Curl_Except $error){
        $data['title'] = '500 Internal Server Error';
        $data['message'] = 'Ошибка при работе с Yandex Geo Decoder';
        $data['debug_message'] = sprintf("error initialized in %s on line %s <br>
                                          with message '%s' <br>",
            $error->getFile(),$error->getLine(),$error->getMessage());
        $data['response_code'] = 500;
        $this->print_error($data);
        $this->action_log($error);
    }
}