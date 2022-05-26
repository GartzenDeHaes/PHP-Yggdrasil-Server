<?php
header('content-type:application/json;charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');

$respdata = array(
	"server_dtm" => date("Y.m.d H:i:s ") . date_default_timezone_get(),
	"message" => "Message from ".$rootserver."/motd",
	"authserver" => $rootserver,
	"status" => "OK"
);
echo json_encode($respdata);
