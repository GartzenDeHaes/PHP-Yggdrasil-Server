<?php
//Basic configuration of external login server//
$servername = "Portland Softworks LLC"; //External login server name
$impname = "portland-minecraft-auth";
$impver = "0.1"; //version number
$skinurl = array(
    ".zhjlfx.cn",//Skin station link, you can fill in multiple
    ".minecraft.net"
);
//$homepage = "https://portlandsoft.works"; //website homepage
//$regurl = "https://portlandsoft.works/registration"; //Player registration address

$homepage = "http://localhost"; //website homepage
$regurl = "http://localhost/registration"; //Player registration address
//External login server key configuration//

$pubKeyFileName = $_SERVER['DOCUMENT_ROOT'] ."/keys/yggdrasil-public-key.pem";
$privKeyFileName = $_SERVER['DOCUMENT_ROOT'] ."/keys/yggdrasil-private-key.pem";

$publickey = file_get_contents($pubKeyFileName); //public key file
$privatekey = file_get_contents($privKeyFileName); //private key

function ip2long_v6($ip) {
	$ip_n = inet_pton($ip);
	$bin = '';
	for ($bit = strlen($ip_n) - 1; $bit >= 0; $bit--) {
		 $bin = sprintf('%08b', ord($ip_n[$bit])) . $bin;
	}

	if (function_exists('gmp_init')) {
		 return gmp_strval(gmp_init($bin, 2), 10);
	} elseif (function_exists('bcadd')) {
		 $dec = '0';
		 for ($i = 0; $i < strlen($bin); $i++) {
			  $dec = bcmul($dec, '2', 0);
			  $dec = bcadd($dec, $bin[$i], 0);
		 }
		 return $dec;
	} else {
		 trigger_error('GMP or BCMATH extension not installed!', E_USER_ERROR);
	}
}
function long2ip_v6($dec) {
	if (function_exists('gmp_init')) {
		 $bin = gmp_strval(gmp_init($dec, 10), 2);
	} elseif (function_exists('bcadd')) {
		 $bin = '';
		 do {
			  $bin = bcmod($dec, '2') . $bin;
			  $dec = bcdiv($dec, '2', 0);
		 } while (bccomp($dec, '0'));
	} else {
		 trigger_error('GMP or BCMATH extension not installed!', E_USER_ERROR);
	}

	$bin = str_pad($bin, 128, '0', STR_PAD_LEFT);
	$ip = array();
	for ($bit = 0; $bit <= 7; $bit++) {
		 $bin_part = substr($bin, $bit * 16, 16);
		 $ip[] = dechex(bindec($bin_part));
	}
	$ip = implode(':', $ip);
	return inet_ntop(inet_pton($ip));
}
function is_ipv6($address) {
	$ipv4_mapped_ipv6 = strpos($address, "::ffff:");
	return (strpos($address, ":") !== FALSE) &&
			 ($ipv4_mapped_ipv6 === FALSE || $ipv4_mapped_ipv6 != 0);
}
function ip_to_int($ip) {
	if (is_ipv6($ip)) {
		return ip2long_v6($ip); // => 1113982819
	}
	else {
		return ip2long($ip); // => 1113982819
	}
}

function strip_input($txt) {
	return htmlspecialchars(stripslashes(trim($txt)));
}
function strip_sql_chars($txt) {
	$txt = stripslashes($txt);
	$txt = str_replace("'", "-", $txt);
	$txt = str_replace('"', "-", $txt);
	return $txt;
}
function safe_input($txt) {
	return trim(htmlspecialchars(strip_sql_chars($txt)));
}
function isvalid_input($txt) {
	return $txt == safe_input($txt);
}

$client_ip = $_SERVER['REMOTE_ADDR'];
$client_ip_int = ip_to_int($client_ip);
//$client_ip_int = ip2long($_SERVER["REMOTE_ADDR"]); // => 1113982819
//$dotted_ip  = long2ip_v6($client_ip_int);             // => "66.102.7.99"

