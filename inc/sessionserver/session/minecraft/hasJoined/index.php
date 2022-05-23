<?php
// /hasJoined SERVER SIDE encryption key exchange with client (client uses /join)
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/include.php');
if (cmethod::isGet() == false) {
	exceptions::doErr(405, 'HTTP/1.1 405 Method not allowed', 'The request method is not supported');
	exit;
}
$p_name = $_GET['username'];
$serverid = $_GET['serverId'];
if (isset($_GET['ip'])) {
	$ipaddr = $_GET['ip'];
} else {
	$ipaddr = 'NONE';
}
if ($db->chkSession($p_name, $serverid, $ipaddr)) {
	$acctoken = $db->getAcctokenByServerid($serverid);
	if ($db->getTokenState($acctoken) < 0) {
		exceptions::doErr(403, 'ForbiddenOperationException', 'The Token has expired');
	}
	$userid = $db->getUseridByAcctoken($acctoken);
	$profile = $db->getProfileByOwner($userid);
	echo $profile;
} else if ($mojanglogin) {
	$opts = array(
		'http' => array(
			'method' => "GET",
			'timeout' => 10,
		)
	);
	$mojangdata = file_get_contents("https://sessionserver.mojang.com/session/minecraft/hasJoined?username=" . $p_name . "&serverId=" . $serverid . (($ipaddr == null) ? "" : "&ip=" . $ipaddr), false, stream_context_create($opts));
	if (strlen($mojangdata) > 2) {
		echo $mojangdata;
	} else if ($mojangdata == false) {
		header(Exceptions::$codes[500]);
	} else
		header(Exceptions::$codes[204]);
} else {
	header(Exceptions::$codes[204]);
}
