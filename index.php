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
date_default_timezone_set('Europe/Minsk');
//guest privilege
if (!isset($_SESSION['privilege']))
    $_SESSION['privilege']=0;
require_once 'Application/vendor/autoload.php';
require_once 'Application/BootStrap.php';