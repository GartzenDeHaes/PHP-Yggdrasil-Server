<?php
header('content-type:application/json;charset=utf8');
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');
if (cmethod::isPost() == false) {
    exceptions::doErr(405,'HTTP/1.1 405 Method not allowed','The request method is not supported');
    exit;
}
$data = json_decode(file_get_contents('php://input'),true,10);
if ($data == null) {
    exceptions::doErr(400,'IllegalArgumentException','Submitted data is not JSON data',json_last_error());
    exit;
}
if (count($data)>10){
    Exceptions::doErr(400,"IllegalArgumentException","Too much data submitted","一Only 10 pieces of data can be submitted at the same time");
    exit;
}
$result = array();
foreach($data as $pname){
    $result[]=$db->getProfileByPlayer($pname)->getArrayFormated();
}
echo json_encode($result);
