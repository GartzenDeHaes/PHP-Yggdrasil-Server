<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/include.php');

function do404()
{
	global $servername, $impname, $impver, $homepage, $regurl, $skinurl, $publickey;
	global $db, $client_ip_int;

	// 404 strike
	$db->updIp404($client_ip_int);

	header('content-type:application/json;charset=utf-8');
	echo json_encode(serverinfo::info($servername, $impname, $impver, $homepage, $regurl, $skinurl, $publickey));
}

if ($_SERVER["REQUEST_URI"] != "/index.php") {
	$requri = explode("?", $_SERVER["REQUEST_URI"])[0];
	if (strpos($requri, "sessionserver/session/minecraft/profile") > -1) {
		if ($db->allowIp($client_ip_int)) {
			include "inc/sessionserver/session/minecraft/profile/index.php";
		}
	} else {
		if ($db->allowIp($client_ip_int)) {
			if (!(include "inc" . $requri . "/index.php")) {
				do404();
			}
		}
	}
} else {
	do404();
}
