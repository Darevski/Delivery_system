<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 30.05.16
 * Time: 13:43
 */

namespace Application\Units;


use Application\Core\System\Config;

class Long_Poll {

    private $ex_time;

    protected $_handlers = array();

    public function __construct() {
        $config = Config::get_instance();
        $this->ex_time=$config->get_poll_time();

        $shmid = shmop_open(ftok(__FILE__, 't'), 'c', 0777, 8192);
        shmop_close($shmid);
    }


    /**
     * 
     * @param $event
     * @param null $data
     */
    public function push($event, $data = null) {
        /**
         * Первые 8 байт хранят длинну сериализованного массива
         * Оставшиеся хранят сериализованный массив вида 'time' => time(), 'data' => $data, 'user_id'=>$_SESSION['id'],'event'=>$evet,
         * где, $event - действие которое необходимо выполнить
         */
        $shmid = shmop_open(ftok(__FILE__, 't'), 'w', 0, 0);

        if ($shmid) {
            $storageLength = (int) shmop_read($shmid, 0, 8);
            $storage = array();
            if ($storageLength) {
                $storage = unserialize(strval(shmop_read($shmid, 8, (int) $storageLength)));
            }

            //unset useless events
            foreach ($storage as $item => $value)
                if ($value['time'] < (time() - ($this->ex_time*1.5)))
                    unset($storage[$item]);

            $storage[] = array('time' => time()+1, 'data' => $data, 'user_id'=>$_SESSION['id'],'event'=>$event);
            $storageLength =  strlen(serialize($storage));

            shmop_write($shmid,  (int) $storageLength, 0);
            shmop_write($shmid, serialize($storage), 8);
            shmop_close($shmid);
        }
    }


    /**
     * Daemon which listen memory bock in RAM for incoming notification
     * @param null $QueryTime
     * @return array
     */
    public function listen($QueryTime=null) {
        $flag = false;
        $QueryTime = ($QueryTime) ?: time();
        $endTime = time() + $this->ex_time-5;
        $shmid = shmop_open(ftok(__FILE__, 't'), 'w', 0, 0);
        $result = array();
        if ($shmid) {
            while (time() < $endTime) {
                sleep(1);
                $storageLength = (int) shmop_read($shmid, 0, 8);
                $storage= unserialize(strval(shmop_read($shmid, 8, (int) $storageLength)));

                /**
                 * Обход и выполнение зарегистрированных обработчиков событий
                 */
                foreach ($storage as $event => $data){


                    if ($data['time']+150 > $QueryTime && $data['user_id']==$_SESSION['id']) {
                        $result_handler =$this->_runHandler($data['event'], $data['data']);
                        if ($result_handler){
                            unset($storage[$event]);
                            $result[$data['event']][] = $result_handler;
                            $flag = true;
                        }

                    }
                }

                // При возникновении события завершаем запрос и отправляем данные клиенту
                if ($flag == true){
                    $storage = serialize($storage);
                    $storageLength = strlen($storage);

                    shmop_write($shmid, (int) $storageLength,0);
                    shmop_write($shmid, $storage,8);
                    break;
                }


            }

            shmop_close($shmid);
        }
        return $result;
    }


    /**
     * Function which register handlers
     * @param $event
     * @param $callback
     * @param $param
     */
    public function registerEvent($event, $callback=null,$param=null) {
        $this->_handlers[$event]['callback'] = $callback;
        $this->_handlers[$event]['param'] = $param;
    }

    /**
     * Run Registered Handler
     * @param $data
     * @param $key
     * @return mixed
     */
    protected function _runHandler($key,$data = null) {
        if (!is_null($this->_handlers[$key]['callback'])) {
            return call_user_func_array($this->_handlers[$key]['callback'],$this->_handlers[$key]['param']);
        }
        else
            return $data;
    }

}