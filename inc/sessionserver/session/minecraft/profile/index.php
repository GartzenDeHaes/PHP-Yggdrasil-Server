<?php
header("Content-Type: application/json; charset=utf-8");
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');
if (cmethod::isGet() == false) {
    exceptions::doErr(405,'HTTP/1.1 405 Method not allowed','The request method is not supported');
    exit;
}
$uri = explode('/',$_SERVER["REQUEST_URI"]);
$uuid = $uri[count($uri)-1];
$unsigned = (isset($_GET["unsigned"])) ? ($_GET["unsigned"]=="true"):true;
$db->updateSkinData($uuid);
$profile = $db->getProfileByUuid($uuid);
if($profile == false){
    header(Exceptions::$codes[204]);
}
echo $profile;
