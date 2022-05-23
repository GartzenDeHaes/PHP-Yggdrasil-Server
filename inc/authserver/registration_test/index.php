<?php
header('content-type:text/html;charset=utf-8');
header("Pragma: no-cache");
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/include.php');

$error_msg = "&nbsp;";

function strip_input($txt) {
	return htmlspecialchars(stripslashes(trim($txt)));
}

function isvalid_input($txt) {
	return $txt == strip_input($txt);
}

function validate_field($txt, $minlen, $maxlen, $fieldName) {
	global $error_msg;

	if (! isvalid_input($txt)) {
		$error_msg = $fieldName . " has invalid chararacter";
		return false;
	}
	if (strlen($txt) < $minlen) {
		$error_msg = $fieldName . " must be at least " . $minlen . " characters";
		return false;
	}
	if (strlen($txt) > $maxlen) {
		$error_msg = $fieldName . " must be less than " . $maxlen . " characters";
		return false;
	}
	return true;
}

function attemp_create_user($username, $password, $email, $secphz, $userIp) {
	global $error_msg;
	global $db;// = new database();

	if (! $db->isAvailableUserName($username)) {
		$error_msg = "User name " . $username . " is already taken";
		return false;
	}

	if (! $db->isAvailable($email)) {
		$error_msg = "Email " . $email . " is in use";
		return false;
	}

	if (! $db->createUser($username, $password, $email, $secphz, $userIp)) {
		$error_msg = "Database insert failed";
		return false;
	}

	return true;
}

$inpageStyle = "display:block;visibility:visible;";
$confpageStyle = "display:none;visibility:hidden;";

if (! cmethod::isPost()) {
	$username = "user";
	$email = "president@whitehouse.gov";
	$password = "password123";
	$secphrase = "Might ask you this at some point";
} else {
	$username = htmlspecialchars(trim($_POST["username"]));
	$email = htmlspecialchars(trim($_POST["email"]));
	$password = htmlspecialchars(trim($_POST["password"]));
	$secphrase = htmlspecialchars(trim($_POST["secphz"]));

	if 
	(
		validate_field($username, 2, 50, "User Name") &&
		validate_field($email, 2, 32, "Email") &&
		validate_field($password, 2, 32, "Password") &&
		validate_field($secphrase, 2, 50, "Security Phrase")
	) {
		if (attemp_create_user($username, $password, $email, $secphrase, $_SERVER['REMOTE_ADDR'])) {
			$inpageStyle = "display:none;visibility:hidden;";
			$confpageStyle = "display:block;visibility:visible;";
		}
	}
}
?><html>
<head>
<meta http-equiv="expires" content="Sun, 01 Jan 2014 00:00:00 GMT"/>
<meta http-equiv="pragma" content="no-cache" />
<style>
input {background:linen;};
</style>
</head>
<body bgcolor="black">
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
&nbsp;<br/>
<div style="<?php echo $inpageStyle ?>">
<table width=400px bgcolor=green cellpadding=2><tr><td>
<table width=100% bgcolor=black align="center" cellpadding=2 cellspacing=0 border=0 style="color:white;">
	<tr style="background:green;"><th colspan=2>Registration</th></tr>
	<tr><td colspan=2>&nbsp;</td></tr>
	<tr><td colspan=2 align=center><span style="color:red;"><small><?php echo $error_msg ?></small></span></td></tr>
	<tr><td align=right>User Name:</td><td><input type="text" name="username" value="<?php echo $username; ?>" maxlength="50" style="width:250px;"><br>
	<tr><td align=right>Password:</td><td><input type="text" name="password" value="<?php echo $password; ?>" maxlength="32" style="width:250px;"><br>
	<tr><td align=right>E-mail:</td><td><input type="text" name="email" value="<?php echo $email; ?>" maxlength="32" style="width:250px;"><br>
	<tr><td align=right tooltop="bobz">Security Phrase:</td><td><input type="text" name="secphz" value="<?php echo $secphrase; ?>" style="width:250px;"><br>
	<tr><td colspan=2>&nbsp;</td></tr>
	<tr><td colspan=2 align=center><input type="submit" value="Register" /></td></tr>		
	<tr><td colspan=2>&nbsp;</td></tr>
	</td></tr>
</table>
</td></tr></table>
</div>
<div style="<?php echo $confpageStyle ?>">
<table width=400px bgcolor=green cellpadding=2><tr><td>
<table width=100% bgcolor=black style="color:green;" align="center" cellpadding=2 cellspacing=0 border=0>
	<tr style="background:green;color:white;"><th colspan=2>Registration Confirmation</th></tr>
	<tr><td colspan=2>&nbsp;</td></tr>
	<tr><td colspan=2 style="color:green;" align=center>CONGRATULATIONS<br/><?php echo $username ?></td></tr>
	<tr><td colspan=2>&nbsp;</td></tr>
	<tr><td colspan=2>Registration complete.  You can now logon in the Totally Not MineCraft application with your EMAIL (<?php echo $email ?>).</td></tr>
	<tr><td colspan=2>&nbsp;</td></tr>
	<tr><td colspan=2 align=center><input type="button" onclick="window.location.href='https://portlandsoft.works'" value="Close" /></td></tr>		
	<tr><td colspan=2>&nbsp;</td></tr>
	</td></tr>
</table>
</td></tr></table>
</div>
</form>
</body>
</html> 
