<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 19.03.16
 * Time: 0:16
 */
namespace Application;

include_once 'Core/System/Psr4AutoLoaderClass.php';

$Auto_Loader = new Core\System\Psr4AutoloaderClass();
$Auto_Loader->register();
//Register NameSpaces from Project for Auto loader class
$Auto_Loader->addNamespace('Application\Core',$_SERVER['DOCUMENT_ROOT'].'/Application/Core');
$Auto_Loader->addNamespace('Application\Controllers',$_SERVER['DOCUMENT_ROOT'].'/Application/Controllers');
$Auto_Loader->addNamespace('Application\Exceptions',$_SERVER['DOCUMENT_ROOT'].'/Application/Exceptions');
$Auto_Loader->addNamespace('Application\Models',$_SERVER['DOCUMENT_ROOT'].'/Application/Models');

$route_system = new Core\System\AltoRouter();
// Register map of requests for Routing system
// Method, route regex and point of target
$route_system->map('GET|POST','/', 'Start_Page');
$route_system->map('GET|POST','/[a:controller]/[a:action]','Application');

// Search matches between URL request and Reg maps
$route_result = $route_system->match();
//Application Start`s
try{
    $Route = new Core\Route();
    $Route->Start($route_result);
}
catch (Exceptions\UFO_Except $error){
    echo $error->getMessage();
}
catch (Exceptions\SQL_Except $error){
    echo $error->getMessage();
}
catch (Exceptions\Main_Except $error){
    echo $error->getMessage();
}