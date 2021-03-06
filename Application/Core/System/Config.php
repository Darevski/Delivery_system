<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 0:31
 */

namespace Application\Core\System;

/**
 * Class Config using classic template Singleton
 * It provides configuration information about database connection and type of build (debug or release)
 * from Configuration file in Application\Config.ini
 * @package Application\Core\System
 */
class Config {
    /**
     * @var Config singleton object
     */
    private static $instance =null;

    /**
     * using Singleton
     * @return Config
     */
    public static function get_instance(){
        if (is_null(self::$instance))
            self::$instance = new Config();
        return self::$instance;
    }

    /**
     * @var array database connect configuration
     */
    private $database_config = null;

    /**
     * build type 'Debug' on default
     * @var string debug/production
     */
    private $build = 'Debug';

    /**
     * configuration for request to yandex.maps service
     * @var null
     */
    private $yandex_unit = null;
    /**
     * Parse Application\Config.ini on construct and put`s it into private variables
     */
    private function __construct(){
        $Config = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/Application/Config.ini',true);
        $this->database_config = $Config['Data_Base_config'];
        $this->build = $Config['Build'];
        $this->yandex_unit  = $Config['Yandex Unit'];
    }

    /** cap for protection */
    private function __clone(){}

    /**
     * return array with database connection configuration
     * @return array
     */
    public function get_database_config(){
        return $this->database_config;
    }

    /**
     * return Build type ('Debug'/'Production')
     * @return string
     */
    public function get_build(){
        return $this->build;
    }

    /**
     * return configuration information for request to yandex.maps service
     * @return array
     */
    public function get_yandex_unit(){
        return $this->yandex_unit;
    }
}