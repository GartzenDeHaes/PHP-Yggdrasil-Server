<?php
header('content-type:application/json;charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');
if (cmethod::isPost() == false) {
    exceptions::doErr(405,'HTTP/1.1 405 Method not allowed','The request method is not supported', 10);
    exit;
}
$check_post_data = array(
    "username","password"
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
$username = safe_input($data['username']);
$passwd = safe_input($data['password']);
if ($username == '' or $passwd == '') {
    exceptions::doErr(403,'ForbiddenOperationException','Email or password cannot be empty', 13);
    exit;
}
//header("Content-Type: application/json; charset=utf-8");
if (!$db->isAvailableUserName($username)) {
    exceptions::doErr(404,'ForbiddenOperationException','The account you entered does not exist', 26);
    exit;
}
if (!$db->chkPasswd($username,$passwd)) {
    exceptions::doErr(403,'ForbiddenOperationException','The email or password you entered is incorrect', 15);
    exit;
}
$available_userid = $db->getUserid($username);
$db->killTokensByOwner($available_userid);

//header(Exceptions::$codes[204]);
$respdata = array("status" => "OK");
echo json_encode($respdata);
