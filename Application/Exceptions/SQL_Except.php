<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 1:02
 */

namespace Application\Exceptions;

/**
 * SQL_Except class implements handle the SQL exception`s
 * @package Application\Exceptions
 */
class SQL_Except extends Main_Except{

    /**
     * Error detection code and outputting appropriate page
     * @param SQL_Except  $error received exception
     */
    public function classification_error(SQL_Except $error){
        $data['title'] = '500 Internal Server Error';
        $data['message'] = 'Ошибка работы БД';
        $data['debug_message'] = sprintf("error initialized in %s on line %s <br>
                                          with message '%s' <br>",
            $error->getFile(),$error->getLine(),$error->getMessage());
        $data['response_code'] = 500;
        $this->print_error($data);
        $this->action_log($error);
    }

}