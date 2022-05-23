<?php
header('content-type:application/json;charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/include.php');
if (cmethod::isPost() == false) {
	$db->updIp404($client_ip_int);
	exceptions::doErr(405, 'HTTP/1.1 405 Method not allowed', 'The request method is not supported');
	exit;
}
$check_post_data = array(
	"server_id", "server_token", "name", "ipaddr", "port", "version", "ispublic", "max_users", "cur_users"
);
$data = json_decode(file_get_contents('php://input'), true, 10);
if ($data == null) {
	$db->updIp404($client_ip_int);
	exceptions::doErr(400, 'IllegalArgumentException', 'Submitted data is not JSON data');
	exit;
}
foreach ($check_post_data as $v) {
	if (!isset($data[$v])) {
		$db->updIp404($client_ip_int);
		exceptions::doErr(400, 'IllegalArgumentException', 'Missing parameters');
		exit;
	}
}
$server_id = safe_input($data['server_id']);
$server_token = safe_input($data['server_token']);
$sip = $data['ipaddr'];
$str_name = safe_input($data['name']);
$int_port = $data['port'];
$int_prot_ver = $data['version'];
$char_is_public = safe_input($data['ispublic']);
$int_max_users = $data['max_users'];
$int_cur_users = $data['cur_users'];
if ($server_id == '' or $server_token == '' or $sip == '' or $str_name == '' or $int_port == '' or $int_prot_ver == '' or $char_is_public == '' or $int_max_users == '') {
	$db->updIp404($client_ip_int);
	exceptions::doErr(403, 'ForbiddenOperationException', 'invalid request parameters');
	exit;
}
if (strlen($str_name) < 4) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'name too short');
	exit;
}
if ($char_is_public != 'Y' && $char_is_public != 'N') {
	exceptions::doErr(403, 'ForbiddenOperationException', 'ispublic must be Y or N');
	exit;
}
if (! is_numeric($int_port)) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'non-numeric port');
	exit;
}
if (! is_numeric($int_prot_ver)) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'non-numeric verison');
	exit;
}
if (! is_numeric($int_max_users)) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'non-numeric max_users');
	exit;
}
if (! is_numeric($int_cur_users)) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'non-numeric cur_users');
	exit;
}

$int_ip = ip_to_int($sip);

if ($db->updServerHeartbeat($server_id, $server_token, $int_ip, $str_name, $int_port, $int_prot_ver, $char_is_public, $int_max_users, $int_cur_users)) {
	$db->updIpAuthFail($client_ip_int);
	exceptions::doErr(404, 'ForbiddenOperationException', 'Unauthorized');
	exit;
}
$respdata = array(
	"status" => "OK",
	"server_id" => $server_id
);

echo json_encode($respdata);
