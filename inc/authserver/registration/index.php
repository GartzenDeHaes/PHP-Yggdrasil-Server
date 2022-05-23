<?php
header('content-type:application/json;charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/include.php');
if (cmethod::isPost() == false) {
	$db->updIp404($client_ip_int);
	exceptions::doErr(405, 'HTTP/1.1 405 Method not allowed', 'The request method is not supported');
	exit;
}
$check_post_data = array(
	"username", "email", "password", "secphz"
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
$username = safe_input($data['username']);
$email = safe_input($data['email']);
$password = safe_input($data['password']);
$secphz = safe_input($data['secphz']);
if ($username == '' or $email == '' or $password == '' or $secphz == '') {
	$db->updIp404($client_ip_int);
	exceptions::doErr(403, 'ForbiddenOperationException', 'invalid request parameters');
	exit;
}
if (strlen($username) < 3) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'username too short');
	exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'invalid email');
	exit;
}
if (strlen($password) < 7) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'password too short');
	exit;
}
if (strlen($secphz) < 6) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'security phrase too short');
	exit;
}

$message = "OK";

if (! attemp_create_user($username, $password, $email, $secphrase, $_SERVER['REMOTE_ADDR'])) {
	$message = "Internal error";
}

$respdata = array(
	"username" => $username,
	"status" => $message
);

echo json_encode($respdata);
