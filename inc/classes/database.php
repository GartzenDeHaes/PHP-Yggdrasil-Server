<?php
class database
{
	public $mysqli;
	function __construct()
	{
		$dbexists = file_exists($_SERVER['DOCUMENT_ROOT'] . "/data/db.sqlite");

		$this->mysqli = new SQLite3($_SERVER['DOCUMENT_ROOT'] . "/data/db.sqlite");
		$this->mysqli->busyTimeout(5000);
		$this->mysqli->exec('PRAGMA journal_mode = wal;');
		$this->mysqli->exec('PRAGMA synchronous=NORMAL;');

		if (!$dbexists) {
			$sql = "CREATE TABLE IF NOT EXISTS [chkname] (" . PHP_EOL .
				"[uuid] varchar(50) PRIMARY KEY," . PHP_EOL .
				"[playername] varchar(30) NOT NULL UNIQUE" . PHP_EOL .
				");" . PHP_EOL;
			$this->mysqli->exec($sql);

			$sql = "CREATE TABLE IF NOT EXISTS [users] (" . PHP_EOL .
				"[uid] INTEGER PRIMARY KEY," . PHP_EOL .
				"[username] NVARCHAR(50) NOT NULL DEFAULT '' UNIQUE," . PHP_EOL .
				"[password] NVARCHAR(32) NOT NULL DEFAULT ''," . PHP_EOL .
				"[email] VARCHAR(32) NOT NULL UNIQUE," . PHP_EOL .
				"[myid] NVARCHAR(30) NOT NULL DEFAULT ''," . PHP_EOL .
				"[myidkey] NVARCHAR(16) NOT NULL DEFAULT ''," . PHP_EOL .
				"[regip] VARCHAR(50) NOT NULL DEFAULT ''," . PHP_EOL .
				"[regdate] INTEGER(10) NOT NULL DEFAULT 0," . PHP_EOL .
				"[lastloginip] INTEGER(10) NOT NULL DEFAULT 0," . PHP_EOL .
				"[lastlogintime] INTEGER(10) NOT NULL DEFAULT 0," . PHP_EOL .
				"[salt] CHAR(6) NOT NULL DEFAULT 'salty'," . PHP_EOL .
				"[secques] NVARCHAR(50) NOT NULL DEFAULT ''," . PHP_EOL .
				"[vtime] INTEGER(11) NOT NULL DEFAULT 0," . PHP_EOL .
				"[userid] NVARCHAR(50) UNIQUE," . PHP_EOL .
				"[uuid] VARCHAR(50) DEFAULT NULL," . PHP_EOL .
				"[texturedata] TEXT NOT NULL DEFAULT ''," . PHP_EOL .
				"[mojang] VARCHAR(255) DEFAULT 'false'," . PHP_EOL .
				"[space] VARCHAR(50) DEFAULT '&nbsp')" . PHP_EOL;
			$this->mysqli->exec($sql);
			$this->mysqli->exec("CREATE INDEX users_email_idx ON users (email);");

			$sql = "CREATE TABLE IF NOT EXISTS [sessions] (" . PHP_EOL .
				"[server_id] VARCHAR(255) PRIMARY KEY," . PHP_EOL .
				"[acc_token] VARCHAR(255) DEFAULT NULL," . PHP_EOL .
				"[ipaddr] VARCHAR(20) DEFAULT NULL," . PHP_EOL .
				"[o_time] TIMESTAMP DEFAULT CURRENT_TIMESTAMP )" . PHP_EOL;
			$this->mysqli->exec($sql);

			$sql = "CREATE TABLE IF NOT EXISTS [tokens] (" . PHP_EOL .
				"[acc_token] VARCHAR(50) PRIMARY KEY," . PHP_EOL .
				"[cli_token] VARCHAR(50) NOT NULL," . PHP_EOL .
				"[profile] VARCHAR(50) DEFAULT NULL," . PHP_EOL .
				"[ptime] TIMESTAMP DEFAULT CURRENT_TIMESTAMP," . PHP_EOL .
				"[state] INT(1) NOT NULL DEFAULT 1," . PHP_EOL .
				"[owner_uuid] VARCHAR(50) NOT NULL )" . PHP_EOL;
			$this->mysqli->exec($sql);

			$sql = "CREATE TABLE IF NOT EXISTS [vailtoken] (" . PHP_EOL .
				"[id] int(10) PRIMARY KEY," . PHP_EOL .
				"[token] varchar(255) DEFAULT NULL," . PHP_EOL .
				"[vtime] TIMESTAMP DEFAULT CURRENT_TIMESTAMP )" . PHP_EOL;
			$this->mysqli->exec($sql);
		}
	}
	function exec($sql)
	{
		$this->mysqli->exec($sql);
	}
	function query($sql)
	{
		$result = $this->mysqli->query($sql);

		return $result;
	}
	function query_change($sql)
	{
		$result = $this->mysqli->exec($sql);
		return true;  // $this->myslqi->changes() ???
	}
	function isAvailableUserName($unm)
	{
		$ret = $this->query("select * from users where username = '" . $unm . "';");
		while ($row = $ret->fetchArray()) {
			return false;
		}
		return true;
	}
	function isAvailable($email)
	{
		$ret = $this->query("select * from users where email = '" . $email . "'");
		// this doesn't work for some reason
		//return $ret->fetchArray();
		while ($row = $ret->fetchArray()) {
			return false;
		}
		return true;
	}
	function createUser($username, $password, $email, $secqu, $userip, $saltChar6 = "salty")
	{
		$encrypted = md5(md5($password) . $saltChar6);

		return $this->query("insert into [users] ([userid], [username], [password], [email], [secques], [regip], [salt]) VALUES ('" . $username . "', '" . $username . "', '" . $encrypted . "', '" . $email . "', '" . $secqu . "', '" . $userip . "', '" . $saltChar6 . "');");
	}
	function chkPasswd($email, $passwd)
	{
		$ret = $this->query("select * from users where email = '" . $email . "'");
		if ($rec = $ret->fetchArray()) {
			$ucpass = $rec["password"];
			$salt = $rec["salt"];
			$playername = $rec["userid"];
			$playeruuid = UUID::getUserUuid($playername);
			$skinuuid = "skinuuid"; // file_get_contents("https://api.zhjlfx.cn/?type=getuuid&method=email&email=".$email);

			$encrypted = md5(md5($passwd) . $salt);
			$rs = ($encrypted == $ucpass);
			if ($rs) {
				if ($skinuuid == '') {
					$this->crePlayerUuid($playeruuid, $email, $playername);
					return $rs;
				} else {
					$this->crePlayerUuid($skinuuid, $email, $playername);
					return $rs;
				}
			}
		}
		return false;
	}
	function updateUser($email, $userid)
	{
		$this->query_change("update users set lastlogintime = '" . time() . "', userid = '" . $userid . "' where email = '" . $email . "'");
	}
	function getUserid($email)
	{
		$ret = $this->query("select * from users where email = '" . $email . "'");
		if ($rec = $ret->fetchArray()) {
			return $rec[13];
		}
		return false;
	}
	function creToken($cli_token, $userid)
	{
		$acctoken = UUID::getUserUuid(uniqid() . $cli_token);
		$ret = $this->query("select * from tokens where owner_uuid = '" . $userid . "'");
		if ($rec = $ret->fetchArray()) {
			$this->query_change("update tokens set acc_token = '" . $acctoken . "', cli_token = '" . $cli_token . "', state = 1 where owner_uuid = '" . $userid . "'");
		} else {
			$this->query_change("insert into tokens (acc_token, cli_token, state, owner_uuid) values ('" . $acctoken . "', '" . $cli_token . "', 1, '" . $userid . "');");
		}
	}
	function getTokensByOwner($userid)
	{
		$ret = $this->query("select * from tokens where owner_uuid = '" . $userid . "'");
		if ($rec = $ret->fetchArray()) {
			return array($rec[0], $rec[1]);
		}
		return false;
	}
	function crePlayerUuid($playeruuid, $email, $playername)
	{
		$ret = $this->query("select * from users where email = '" . $email . "'");
		if ($rec = $ret->fetchArray()) {
			$uuid = $rec["uuid"];
			if ($uuid == "") {
				$this->query_change("update users set uuid = '" . $playeruuid . "' where email = '" . $email . "'");
				$this->addPlayerInfo($playername, $playeruuid);
				return;
			}
		}
		$playeruuid = $uuid;
		$this->addPlayerInfo($playername, $playeruuid);
	}
	function getProfileByOwner($userid)
	{
		$ret = $this->query("select * from users where userid = '" . $userid . "'");
		if ($rec = $ret->fetchArray()) {
			return new Profile($rec[1], $rec[14], $rec[15]);
		}
		return false;
	}
	function porfileToken($acctoken, $player_uuid)
	{
		$this->query_change("update tokens set profile = '" . $player_uuid . "' where acc_token = '" . $acctoken . "'");
	}
	function getUseridByAcctoken($acctoken)
	{
		$ret = $this->query("select * from tokens where acc_token = '" . $acctoken . "'");
		if ($rec = $ret->fetchArray()) {
			return $rec[5];
		}
		return false;
	}
	function isAcctokenAvailable($acctoken)
	{
		$ret = $this->query("select * from tokens where acc_token = '" . $acctoken . "'");
		if ($rec = $ret->fetchArray()) {
			return true;
		}
		return false;
	}
	function chkAcctoken($acctoken, $clitoken)
	{
		$ret = $this->query("select * from tokens where acc_token = '" . $acctoken . "'");
		if ($rec = $ret->fetchArray()) {
			return ($clitoken == $rec[1]);
		}
		return false;
	}
	function getTokenState($acctoken)
	{
		$ret = $this->query("select * from tokens where acc_token = '" . $acctoken . "'");
		if ($rec = $ret->fetchArray()) {
			return $rec[4];
		}
		return false;
	}
	function setTokenState($acctoken)
	{
		$this->query_change("update tokens set state = -1 where acc_token = '" . $acctoken . "'");
	}
	function killTokensByOwner($userid)
	{
		$this->query_change("update tokens set state = -1 where owner_uuid = '" . $userid . "'");
	}
	function updateAllTokenState()
	{
		$this->query_change("update tokens set state = 0 where ptime <= date_sub(now(),interval 120 minute);");
		$this->query_change("update tokens set state = -1 where ptime <= date_sub(now(),interval 10 days);");
		return $this->query_change("delete from tokens where state = -1");
	}
	function chkProfileToken($acctoken, $player_uuid)
	{
		$ret = $this->query("select * from tokens where acc_token = '" . $acctoken . "'");
		if ($rec = $ret->fetchArray()) {
			return ($player_uuid == $rec[2]);
		}
		return false;
	}
	function creSession($server_id, $acc_token, $ip)
	{
		$this->query_change("insert into sessions (server_id, acc_token, ipaddr, o_time) values ('" . $server_id . "','" . $acc_token . "','" . $ip . "', now())");
	}
	function chkSession($playername, $serverid, $ipaddr)
	{
		$ret = $this->query("select * from sessions where server_id = '" . $serverid . "'");
		if ($rec = $ret->fetchArray()) {
			$owner_accctoken = $rec[1];
			$owner_userid = $this->getUseridByAcctoken($owner_accctoken);
			$player = $this->getProfileByOwner($owner_userid)->name;
			return (($player == $playername) && ($ipaddr == 'NONE' || $ipaddr == $rec[2]));
		}
		return false;
	}
	function getAcctokenByServerid($serverid)
	{
		$ret = $this->query("select * from sessions where server_id = '" . $serverid . "'");
		if ($rec = $ret->fetchArray()) {
			return $rec[1];
		}
		return false;
	}
	function getProfileByUuid($playeruuid)
	{
		$ret = $this->query("select * from users where uuid = '" . $playeruuid . "'");
		if ($rec = $ret->fetchArray()) {
			return new Profile($rec[1], $rec[14], $rec[15]);
		}
		return false;
	}
	function getProfileByPlayer($playername)
	{
		$ret = $this->query("select * from users where username = '" . $playername . "'");
		if ($rec = $ret->fetchArray()) {
			return new Profile($rec[1], $rec[14], $rec[15]);
		}
		return false;
	}
	function updateAllSessionState()
	{
		$this->query_change("delete from sessions where date(o_time) <= date_sub(now(),interval 30 second);");
	}
	function updateSkinData($uuid)
	{
		$texturedata = "texturedata"; // = file_get_contents("https://api.zhjlfx.cn/?type=getjson&uuid=".$uuid);
		$this->query_change("update users set texturedata = '" . $texturedata . "' where uuid = '" . $uuid . "'");
	}
	function addPlayerInfo($playername, $playeruuid)
	{
		$ret = $this->query("select * from chkname where uuid = '" . $playeruuid . "'");
		if ($rec = $ret->fetchArray()) {
			$this->query_change("update chkname set playername = '" . $playername . "' where uuid = '" . $playeruuid . "'");
		} else {
			$this->query_change("insert into chkname (uuid, playername) values ('" . $playeruuid . "', '" . $playername . "')");
		}
	}
	function getPlayerUuidByAcctoken($acctoken)
	{
		$ret = $this->query("select * from tokens where acc_token = '" . $acctoken . "'");
		if ($rec = $ret->fetchArray()) {
			return $rec[2];
		}
		return false;
	}
	function isPlayerNameChanged($uuid)
	{
		$getname = $this->query("select * from users where uuid = '" . $uuid . "'");
		if ($recname = $getname->fetchArray()) {
			$getsavedname = $this->query("select * from chkname where uuid = '" . $uuid . "'");
			if ($recsave = $getsavedname->fetchArray()) {
				$rs = ($getsavedname[1] !== $recsave[1]);
				return $rs;
			}
		}
		return false;
	}
}
