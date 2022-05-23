<?php
header('content-type:application/json;charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/include.php');
if (cmethod::isPost() == false) {
	$db->updIp404($client_ip_int);
	exceptions::doErr(405, 'HTTP/1.1 405 Method not allowed', 'The request method is not supported');
	exit;
}
$check_post_data = array(
	"client_token", "region", "lang", "start", "limit"
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
$client_token = $data['client_token'];
$region = $data['region'];
$lang = $data['lang'];
$limit = $data['limit'];
$start = $data['start'];

if ($client_token == '') {
	$db->updIp404($client_ip_int);
	exceptions::doErr(403, 'ForbiddenOperationException', 'invalid request parameters');
	exit;
}
if (strlen($region) != 2) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'region must be NA, EU, CN, KO, or JP');
	exit;
}
if (strlen($lang) != 2) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'lang must be 2 chars');
	exit;
}
if (! is_numeric($limit)) {
	$limit = 100;
}
if (! is_numeric($start) || $start < 0) {
	$start = 0;
}

if ($db->getServers()) {
	$db->updIpAuthFail($client_ip_int);
	exceptions::doErr(404, 'ForbiddenOperationException', 'Unauthorized');
	exit;
}

$respdata = array();
$count = 0;

// server_id, name, ipaddr, port, version, max_users, cur_users, lang, region, updated_dts
while ($res = $ret->fetchArray() AND $limit > 0) {
	if ($count >= $start) {
		array_push($respdata, $res);
		$limit = $limit - 1;
	}
	$count = $count + 1;
}

echo json_encode($respdata);
