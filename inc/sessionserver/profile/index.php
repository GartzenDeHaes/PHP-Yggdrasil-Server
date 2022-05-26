<?php
header("Content-Type: application/json; charset=utf-8");
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');
if (cmethod::isGet() == false) {
    exceptions::doErr(405,'HTTP/1.1 405 Method not allowed','The request method is not supported', 10);
    exit;
}
$uri = explode('/',$_SERVER["REQUEST_URI"]);
$uuid = safe_input($uri[count($uri)-1]);
$unsigned = (isset($_GET["unsigned"])) ? ($_GET["unsigned"]=="true"):true;
$db->updateSkinData($uuid);
$profile = $db->getProfileByUuid($uuid);
if($profile == false){
	exceptions::doErr(204,'ForbiddenOperationException','Session not found', 41);
	exit;
}
echo $profile;
