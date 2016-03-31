<?php

/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 02:18
 * @author Darevski
 */

namespace Application\Exceptions;

/**
 * Class UFO_Except
 * Exception`s handling associated with non-existent page`s, access error`s and so on
 * Outputting information about exception in error page
 * @package Application\Exceptions
 */
class UFO_Except extends Main_Except
{
    /**
     * Error detection code and outputting appropriate page
     * @param UFO_Except $error received exception
     */
    function classification_error(UFO_except $error){
        $code = $error->getCode();
        switch ($code) {
            case 404:   //Отсутсвие страницы
                $data['title'] = '404 Bad Gateway';
                $data['message'] = 'Запрашиваемой страницы не существует';
                $data['debug_message'] = sprintf("error initialized in %s on line %s <br>
                                          with message '%s' <br>",
                    $error->getFile(),$error->getLine(),$error->getMessage());
                $data['response_code'] = 404;
                break;
            default:
                $data['title'] = '400 Bad Request';
                $data['message'] = 'нераспознанный запрос';
                $data['debug_message'] = sprintf("error initialized in %s on line %s <br>
                                          with message '%s' <br>",
                    $error->getFile(),$error->getLine(),$error->getMessage());
                $data['response_code'] = 400;
                break;
        }
        $this->print_error($data);
    }


}