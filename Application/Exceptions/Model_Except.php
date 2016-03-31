<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 25.03.16
 * Time: 14:12
 */

namespace Application\Exceptions;

class Model_Except extends Main_Except{
    /**
     * Error detection code and outputting appropriate page
     * @param Model_Except $error received exception
     */
    public function exception_handling(Model_Except $error){
        $data['title'] = '500 Internal Server Error';
        $data['message'] = $error->getMessage();
        $data['debug_message'] = sprintf("error initialized in %s on line %s <br>
                                          with message '%s' <br>",
            $error->getFile(),$error->getLine(),$error->getMessage());
        $data['response_code'] = 500;
        $this->print_error($data);
        $this->action_log($error);
    }
}