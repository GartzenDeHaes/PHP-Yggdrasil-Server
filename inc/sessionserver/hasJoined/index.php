<?php
// /hasJoined SERVER SIDE encryption key exchange with client (client uses /join)

require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');
if (cmethod::isPost() == false) {
    exceptions::doErr(405,'HTTP/1.1 405 Method not allowed','The request method is not supported', 10);
    exit;
}
$check_post_data = array(
    "username", "serverId"
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

$p_name = safe_input($data['username']);
$serverid = safe_input($data['serverId']);
if (isset($data['ip'])) {
	$ipaddr = safe_input($data['ip']);
} else {
	$ipaddr = 'NONE';
}

if ($db->chkSession($p_name, $serverid, $ipaddr)) {
	$acctoken = $db->getAcctokenByServerid($serverid);
	if ($db->getTokenState($acctoken) < 0) {
		exceptions::doErr(403, 'ForbiddenOperationException', 'The Token has expired', 18);
	}
	$userid = $db->getUseridByAcctoken($acctoken);
	$profile = $db->getProfileByOwner($userid);
	echo $profile;
} else {
	exceptions::doErr(403, 'ForbiddenOperationException', 'Session not found', 41);
}
