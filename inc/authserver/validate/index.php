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
$uuid = $db->getPlayerUuidByAcctoken($acctoken);
if (!isset($clitoken)) {
    $cli_token = UUID::getUserUuid(md5(md5(uniqid()).$available_userid));
} else {
    $cli_token = $data['clientToken'];
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
if($db->isPlayerNameChanged($uuid)){
    $db->setTokenState($acctoken);
}
// no content
//header(Exceptions::$codes[204]);

$respdata = array("status" => "OK");
echo json_encode($respdata);
