<?php
header('content-type:application/json;charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/include.php');
if (cmethod::isPost() == false) {
	$db->updIp404($client_ip_int);
	exceptions::doErr(405, 'HTTP/1.1 405 Method not allowed', 'The request method is not supported', 10);
	exit;
}
$check_post_data = array(
	"server_id", "name", "salt", "host", "port", "version", "ispublic", "max_users"
);
$data = json_decode(file_get_contents('php://input'), true, 10);
if ($data == null) {
	$db->updIp404($client_ip_int);
	exceptions::doErr(400, 'IllegalArgumentException', 'Submitted data is not JSON data', 4);
	exit;
}
foreach ($check_post_data as $v) {
	if (!isset($data[$v])) {
		$db->updIp404($client_ip_int);
		exceptions::doErr(400, 'IllegalArgumentException', 'Missing parameters', 12);
		exit;
	}
}
$server_id = safe_input($data['server_id']);
$host = safe_input($data['host']);
$str_name = safe_input($data['name']);
$str16_salt = safe_input($data['salt']);
$int_port = $data['port'];
$int_prot_ver = $data['version'];
$char_is_public = safe_input($data['ispublic']);
$int_max_users = $data['max_users'];
if ($server_id == '' or $host == '' or $str_name == '' or $str16_salt == '' or $int_port == '' or $int_prot_ver == '' or $char_is_public == '' or $int_max_users == '') {
	$db->updIp404($client_ip_int);
	exceptions::doErr(403, 'ForbiddenOperationException', 'invalid request parameters', 13);
	exit;
}
if (strlen($server_id) < 4) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'server_id too short', 37);
	exit;
}
if (strlen($str_name) < 4) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'name too short', 38);
	exit;
}
if ($char_is_public != 'Y' && $char_is_public != 'N') {
	exceptions::doErr(403, 'ForbiddenOperationException', 'ispublic must be Y or N', 28);
	exit;
}
if (strlen($str16_salt) < 8) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'salt too short', 39);
	exit;
}
if (! is_numeric($int_port)) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'non-numeric port', 29);
	exit;
}
if (! is_numeric($int_prot_ver)) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'non-numeric verison', 30);
	exit;
}
if (! is_numeric($int_max_users)) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'non-numeric max_users', 31);
	exit;
}

$server_token = UUID::getUserUuid(md5($str16_salt));
if (! $db->creOrUpdServer($server_id, $server_token, $host, $str_name, $str16_salt, $int_port, $int_prot_ver, $char_is_public, $int_max_users)) {
	$db->updIpAuthFail($client_ip_int);
	exceptions::doErr(404, 'ForbiddenOperationException', 'Server information non unique, or salt does not match server_id', 40);
	exit;
}
$respdata = array(
	"server_id" => $server_id,
	"server_token" => $server_token,
	"status" => "OK"
);

echo json_encode($respdata);
