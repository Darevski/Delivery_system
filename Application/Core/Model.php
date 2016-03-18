<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 0:52
 */

namespace Application\Core;

/**
 * Model general class of business logic
 * @package Application\Core
 */
class Model {
    /**
     * variable with object that operate with database
     * @var System\Safe_SQL|System\Safe_SQL
     */
    protected $database;

    /**
     * Model constructor.
     * Load into $database variable object that operate with database
     */
    public function __construct(){
        //get the configuration for connection to database
        $data_base_opt = System\Config::get_instance()->get_database_config();
        // receiving object for working with database
        $this->database = System\Safe_SQL::get_instance($data_base_opt);
    }
}