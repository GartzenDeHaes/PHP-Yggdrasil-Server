<?php
header('content-type:application/json;charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');

$respdata = array();

$respdata["auth"] = array(
	"protocol" => "http",
	"host" => $rootserver,
	"port" => 80,
	"path" => "/authserv"
);

$respdata["session"] = array(
	"protocol" => "http",
	"host" => $rootserver,
	"port" => 80,
	"path" => "/sessionserver/session/minecraft"
);

$respdata["profle"] = array(
	"protocol" => "http",
	"host" => $rootserver,
	"port" => 80,
	"path" => "/api/profiles/minecraft"
);

$respdata["content"] = array(
	"protocol" => "http",
	"host" => $rootserver,
	"port" => 80,
	"path" => "/content"
);

$respdata["status"] = "OK";

echo json_encode($respdata);
