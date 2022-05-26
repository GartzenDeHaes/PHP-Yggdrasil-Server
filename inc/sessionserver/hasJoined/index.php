<?php
// /hasJoined SERVER SIDE encryption key exchange with client (client uses /join)

require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/include.php');
if (cmethod::isGet() == false) {
	exceptions::doErr(405, 'HTTP/1.1 405 Method not allowed', 'The request method is not supported', 10);
	exit;
}
$p_name = safe_input($_GET['username']);
$serverid = safe_input($_GET['serverId']);
if (isset($_GET['ip'])) {
	$ipaddr = safe_input($_GET['ip']);
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
