<?php
// /joined CLIENT SIDE encryption key exchange with server (server uses /hasJoined)

header('content-type:application/json;charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');
if (cmethod::isPost() == false) {
    exceptions::doErr(405,'HTTP/1.1 405 Method not allowed','The request method is not supported');
    exit;
}
$check_post_data = array(
    "accessToken","selectedProfile","serverId"
);
$data = json_decode(file_get_contents('php://input'),true,10);
if ($data == null) {
    exceptions::doErr(400,'IllegalArgumentException','Submitted data is not JSON data');
    exit;
}
foreach ($check_post_data as $v) {
    if (!isset($data[$v])) {
        exceptions::doErr(400,'IllegalArgumentException','Missing parameters');
        exit;
    }
}
$acctoken = $data['accessToken'];
$selected = $data['selectedProfile'];
$serverid = $data['serverId'];
if (!$db->isAcctokenAvailable($acctoken)) {
    exceptions::doErr(403,'ForbiddenOperationException','The Token does not exist','Token_Not_Exist');
}
if (!(isset($selected) == $db->chkProfileToken($acctoken,$selected))) {
    exceptions::doErr(403,'ForbiddenOperationException','The specified Profile is invalid','Wrong_Profile_UUID');
}
if ($db->getTokenState($acctoken) < 0) {
    exceptions::doErr(403,'ForbiddenOperationException','The Token has expired','Token_Not_Ready');
}
$ip = $_SERVER['REMOTE_ADDR'];
$db->creSession($serverid,$acctoken,$ip);

header(Exceptions::$codes[204]);
$respdata = array("status" => "OK");
echo json_encode($respdata);
