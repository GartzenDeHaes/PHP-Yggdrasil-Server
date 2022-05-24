<?php
// /joined CLIENT SIDE encryption key exchange with server (server uses /hasJoined)

header('content-type:application/json;charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');
if (cmethod::isPost() == false) {
    exceptions::doErr(405,'HTTP/1.1 405 Method not allowed','The request method is not supported', 10);
    exit;
}
$check_post_data = array(
    "accessToken","selectedProfile","serverId"
);
$data = json_decode(file_get_contents('php://input'),true,10);
if ($data == null) {
    exceptions::doErr(400,'IllegalArgumentException','Submitted data is not JSON data', 4);
    exit;
}
foreach ($check_post_data as $v) {
    if (!isset($data[$v])) {
        exceptions::doErr(400,'IllegalArgumentException','Missing parameters', 12);
        exit;
    }
}
$acctoken = safe_input($data['accessToken']);
$selected = safe_input($data['selectedProfile']);
$serverid = safe_input($data['serverId']);
if (!$db->isAcctokenAvailable($acctoken)) {
    exceptions::doErr(403,'ForbiddenOperationException','The Token does not exist',16);
}
if (!(isset($selected) == $db->chkProfileToken($acctoken,$selected))) {
    exceptions::doErr(403,'ForbiddenOperationException','The specified Profile is invalid',33);
}
if ($db->getTokenState($acctoken) < 0) {
    exceptions::doErr(403,'ForbiddenOperationException','The Token has expired',18);
}
$ip = $_SERVER['REMOTE_ADDR'];
$db->creSession($serverid,$acctoken,$ip);

header(Exceptions::$codes[204]);
$respdata = array("status" => "OK");
echo json_encode($respdata);
