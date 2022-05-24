<?php
header('content-type:application/json;charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/include.php');
if (cmethod::isPost() == false) {
	$db->updIp404($client_ip_int);
	exceptions::doErr(405, 'HTTP/1.1 405 Method not allowed', 'The request method is not supported',10);
	exit;
}
$check_post_data = array(
	"username", "email", "password", "secphz"
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
$username = safe_input($data['username']);
$email = safe_input($data['email']);
$password = safe_input($data['password']);
$secphz = safe_input($data['secphz']);
if ($username == '' or $email == '' or $password == '' or $secphz == '') {
	$db->updIp404($client_ip_int);
	exceptions::doErr(403, 'ForbiddenOperationException', 'invalid request parameters', 13);
	exit;
}
if (strlen($username) < 3) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'username too short', 19);
	exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'invalid email', 20);
	exit;
}
if (strlen($password) < 7) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'password too short', 21);
	exit;
}
if (strlen($secphz) < 6) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'security phrase too short', 22);
	exit;
}

if (! $db->isAvailableUserName($username)) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'user name in use', 23);
	exit;
}

if (! $db->isAvailable($email)) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'email is in use', 24);
	exit;
}

if (! $db->createUser($username, $password, $email, $secphz, $client_ip)) {
	exceptions::doErr(403, 'ForbiddenOperationException', 'internal error 1', 25);
	exit;
}

$userid = UUID::getUserUuid(md5($username));
$db->updateUser($username, $userid);
$available_userid = $db->getUserid($username);
if (!isset($data['clientToken'])) {
	$cli_token = UUID::getUserUuid(md5(md5(uniqid()) . $available_userid));
} else {
	$cli_token = $data['clientToken'];
}
if (!isset($data['requestUser'])) {
	$req_user = false;
} else {
	$req_user = $data['requestUser'];
}
$db->creToken($cli_token, $available_userid);
$tokens = $db->getTokensByOwner($available_userid);
$profile = $db->getProfileByOwner($available_userid);
$authdata = array(
	"accessToken" => $tokens[0],
	"clientToken" => $tokens[1],
	"username" => $profile->name,
	"status" => "OK"
);
if ($profile !== null) {
	$db->profileToken($tokens[0], $profile->UUID);
	$authdata["availableProfiles"] = array(
		$profile->getArrayFormated()
	);
	$authdata["selectedProfile"] = $profile->getArrayFormated();
}

echo json_encode($authdata);
