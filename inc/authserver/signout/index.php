<?php
header('content-type:application/json;charset=utf8');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');
if (cmethod::isPost() == false) {
    exceptions::doErr(405,'HTTP/1.1 405 Method not allowed','The request method is not supported');
    exit;
}
$check_post_data = array(
    "username","password"
);
$data = json_decode(file_get_contents('php://input'),true,10);
if ($data == null) {
    exceptions::doErr(400,'IllegalArgumentException','Input is not JSON');
    exit;
}
foreach ($check_post_data as $v) {
    if (!isset($data[$v])) {
        exceptions::doErr(400,'IllegalArgumentException','Missing parameters');
        exit;
    }
}
$email = $data['username'];
$passwd = $data['password'];
if ($email == '' or $passwd == '') {
    exceptions::doErr(403,'ForbiddenOperationException','Email or password cannot be empty');
    exit;
}
//header("Content-Type: application/json; charset=utf-8");
if (!$db->isAvailable($email)) {
    exceptions::doErr(404,'ForbiddenOperationException','The account you entered does not exist');
    exit;
}
if (!$db->chkPasswd($email,$passwd)) {
    exceptions::doErr(403,'ForbiddenOperationException','The email or password you entered is incorrect');
    exit;
}
$available_userid = $db->getUserid($email);
$db->killTokensByOwner($available_userid);
header(Exceptions::$codes[204]);
