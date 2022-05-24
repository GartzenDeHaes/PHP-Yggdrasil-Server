<?php
header('content-type:application/json;charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/include.php');
if (cmethod::isPost() == false) {
	$db->updIp404($client_ip_int);
	exceptions::doErr(405, 'HTTP/1.1 405 Method not allowed', 'The request method is not supported');
	exit;
}
$check_post_data = array(
	"username", "password"
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
$email = $data['username'];
$passwd = $data['password'];
if ($email == '' or $passwd == '') {
	$db->updIpAuthFail($client_ip_int);
	exceptions::doErr(403, 'ForbiddenOperationException', 'Email or password cannot be empty');
	exit;
}
//header("Content-Type: application/json; charset=utf-8");
if ($db->isAvailable($email)) {
	$db->updIpAuthFail($client_ip_int);
	exceptions::doErr(404, 'ForbiddenOperationException', 'The account you entered does not exist');
	exit;
}
if (!$db->chkPasswd($email, $passwd)) {
	$db->updIpAuthFail($client_ip_int);
	exceptions::doErr(403, 'ForbiddenOperationException', 'The email or password you entered is incorrect');
	exit;
}
$userid = UUID::getUserUuid(md5($email));
$db->updateUser($email, $userid);
$available_userid = $db->getUserid($email);
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
if ($req_user) {
	//$authdata["user"] = (new User($json["username"],"",$userid,"zh_CN"))->getArrayFormated();
	$authdata["user"] = (new User($data["username"], "", $userid, "en_US"))->getArrayFormated();
}

echo json_encode($authdata);
