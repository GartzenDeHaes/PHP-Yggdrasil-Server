<?php
//load configuration file
require_once $_SERVER['DOCUMENT_ROOT'] ."/config.php";
spl_autoload_register("_autoload");
function _autoload($classname) {
    //load all classes
    require_once $_SERVER['DOCUMENT_ROOT'] . "/inc/classes/" . strtolower($classname) . ".php";
}
$db = new database();
