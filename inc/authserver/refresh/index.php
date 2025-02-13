<?php
header('content-type:application/json;charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');
if (cmethod::isPost() == false) {
    exceptions::doErr(405,'HTTP/1.1 405 Method not allowed','The request method is not supported', 10);
    exit;
}
$check_post_data = array(
    "accessToken"
);
$data = json_decode(file_get_contents('php://input'),true,10);
if ($data == null) {
    exceptions::doErr(400,'IllegalArgumentException','Input is not JSON', 4);
    exit;
}
foreach ($check_post_data as $v) {
    if (!isset($data[$v])) {
        exceptions::doErr(400,'IllegalArgumentException','Missing parameters', 12);
        exit;
    }
}
$acctoken = safe_input($data['accessToken']);
$clitoken = $data['clientToken'];
$available_userid = $db->getUseridByAcctoken($acctoken);
if (!isset($data['requestUser'])) {
    $req_user = false;
} else {
    $req_user = $data['requestUser'];
}
if (!$db->isAcctokenAvailable($acctoken)) {
    exceptions::doErr(403,'ForbiddenOperationException','The Token does not exist', 16);
}
if (!(isset($clitoken) == $db->chkAcctoken($acctoken,$clitoken))) {
    exceptions::doErr(403,'ForbiddenOperationException','The specified ClientToken is invalid', 17);
}
if ($db->getTokenState($acctoken) < 0) {
    exceptions::doErr(403,'ForbiddenOperationException','The Token has expired', 18);
}
$db->setTokenState($acctoken);
//$db->creToken($cli_token,$available_userid,);
//$tokens = $db->getTokensByOwner($cli_token,$available_userid);
$tokens = $db->getTokensByOwner($available_userid);
$profile = $db->getProfileByOwner($available_userid);
$db->profileToken($tokens[0],$profile->UUID);
$authdata = array(
    "accessToken" => $tokens[0],
    "clientToken" => $tokens[1],
	 "username" => $profile->name,
	 "status" => "OK"
 );
$authdata["availableProfiles"] = array(
    $profile->getArrayFormated()
);
$authdata["selectedProfile"] = $profile->getArrayFormated();
if ($req_user) {
    //$authdata["user"] = (new User($json["username"],"",$userid,"zh_CN"))->getArrayFormated();
    $authdata["user"] = (new User($data["username"],"",$userid,"en_US"))->getArrayFormated();
}

echo json_encode($authdata);
