<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 0:13
 * The entry point into application
 */
session_start();
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once 'Application/vendor/autoload.php';
require_once 'Application/BootStrap.php';