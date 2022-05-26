<?php
header('content-type:application/json;charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/include.php');
if (cmethod::isPost() == false) {
	$db->updIp404($client_ip_int);
	exceptions::doErr(405, 'HTTP/1.1 405 Method not allowed', 'The request method is not supported', 10);
	exit;
}
$check_post_data = array(
	"username", "password"
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
$passwd = safe_input($data['password']);
if ($username == '' or $passwd == '') {
	$db->updIpAuthFail($client_ip_int);
	exceptions::doErr(403, 'ForbiddenOperationException', 'Email or password cannot be empty', 13);
	exit;
}
//header("Content-Type: application/json; charset=utf-8");
if ($db->isAvailableUserName($username)) {
	$db->updIpAuthFail($client_ip_int);
	exceptions::doErr(404, 'ForbiddenOperationException', 'The email or password you entered is incorrect', 14);
	exit;
}
if (!$db->chkPasswd($username, $passwd)) {
	$db->updIpAuthFail($client_ip_int);
	exceptions::doErr(403, 'ForbiddenOperationException', 'The email or password you entered is incorrect', 15);
	exit;
}
$userid = UUID::getUserUuid(md5($username));
$db->updateUser($username, $userid);
$available_userid = $db->getUserid($username);
$tokens = $db->getTokensByOwner($available_userid);

if (!isset($data['requestUser'])) {
	$req_user = false;
} else {
	$req_user = $data['requestUser'];
}
$db->creToken("temp", $available_userid, $username);
$tokens = $db->getTokensByOwner($available_userid);
$profile = $db->getProfileByOwner($available_userid);
$authdata = array(
	"accessToken" => $tokens[0],
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
if ($req_user) {
	//$authdata["user"] = (new User($json["username"],"",$userid,"zh_CN"))->getArrayFormated();
	$authdata["user"] = (new User($data["username"], "", $userid, "en_US"))->getArrayFormated();
}

echo json_encode($authdata);
