<?php
class database
{
	public $mysqli;
	function __construct() {
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
			
			// uid			NOT USED
			// username		Entered by user on registration page
			// userid		Same as username
			// myidkey		NOT USED
			// uuid			Created in chkPassword().  UUID::getUserUuid(rec[userid])
			//					Only set once, but attempts to create every logon

			$sql = "CREATE TABLE IF NOT EXISTS [users] (" . PHP_EOL .
				"[uid] INTEGER PRIMARY KEY," . PHP_EOL .
				"[username] NVARCHAR(32) NOT NULL UNIQUE," . PHP_EOL .
				"[password] NVARCHAR(32) NOT NULL," . PHP_EOL .
				"[email] NVARCHAR(32) UNIQUE," . PHP_EOL .
				"[myid] VARCHAR(32) NOT NULL DEFAULT ''," . PHP_EOL .
				"[myidkey] VARCHAR(16) NOT NULL DEFAULT ''," . PHP_EOL .
				"[regip] VARCHAR(40) NOT NULL DEFAULT ''," . PHP_EOL .
				"[regdate] INTEGER(10) NOT NULL DEFAULT 0," . PHP_EOL .
				"[lastloginip] INTEGER NOT NULL DEFAULT 0," . PHP_EOL .
				"[lastlogintime] INTEGER(10) NOT NULL DEFAULT 0," . PHP_EOL .
				"[salt] CHAR(6) NOT NULL DEFAULT 'salty'," . PHP_EOL .
				"[secques] NVARCHAR(32) NOT NULL DEFAULT ''," . PHP_EOL .
				"[vtime] INTEGER(11) NOT NULL DEFAULT 0," . PHP_EOL .
				"[userid] VARCHAR(32) UNIQUE," . PHP_EOL .
				"[uuid] VARCHAR(30) DEFAULT NULL" . PHP_EOL .
				")" . PHP_EOL;
			$this->mysqli->exec($sql);
			$this->mysqli->exec("CREATE INDEX users_email_idx ON users (email);");

			$sql = "CREATE TABLE IF NOT EXISTS [sessions] (" . PHP_EOL .
				"[server_id] VARCHAR(128) NOT NULL," . PHP_EOL .
				"[acc_token] VARCHAR(50) NOT NULL," . PHP_EOL .
				"[ipaddr] VARCHAR(40) DEFAULT NULL," . PHP_EOL .
				"[o_time] TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ". PHP_EOL .
				"PRIMARY KEY([server_id], [acc_token]) )" . PHP_EOL;
			$this->mysqli->exec($sql);

			$sql = "CREATE TABLE IF NOT EXISTS [tokens] (" . PHP_EOL .
				"[acc_token] VARCHAR(50) PRIMARY KEY," . PHP_EOL .
				"[cli_token] VARCHAR(50) NOT NULL," . PHP_EOL .
				"[profile] VARCHAR(50) DEFAULT NULL," . PHP_EOL .
				"[ptime] TIMESTAMP DEFAULT CURRENT_TIMESTAMP," . PHP_EOL .
				"[state] INT(1) NOT NULL DEFAULT 1," . PHP_EOL .
				"[owner_uuid] VARCHAR(50) NOT NULL )" . PHP_EOL;
			$this->mysqli->exec($sql);

			$sql = "CREATE TABLE IF NOT EXISTS [servers] (" . PHP_EOL .
				"[server_id] VARCHAR(80) NOT NULL PRIMARY KEY," . PHP_EOL .
				"[created_ip] INTEGER NOT NULL," . PHP_EOL .
				"[o_time] TIMESTAMP DEFAULT CURRENT_TIMESTAMP,". PHP_EOL .
				"[name] VARCHAR(80) NOT NULL," . PHP_EOL .
				"[server_token] VARCHAR(50) NOT NULL," . PHP_EOL .
				"[salt] VARCHAR(16) NOT NULL," . PHP_EOL .
				"[host] VARCHAR(80) NOT NULL," . PHP_EOL .
				"[port] INT NOT NULL," . PHP_EOL .
				"[version] INT NOT NULL," . PHP_EOL .
				"[ispublic] CHAR(1) NOT NULL," . PHP_EOL .
				"[max_users] INT NOT NULL," . PHP_EOL .
				"[cur_users] INT NOT NULL DEFAULT(0)," . PHP_EOL .
				"[region] CHAR(2) DEFAULT('NA')," . PHP_EOL .
				"[lang] CHAR(2) DEFAULT('EN')," . PHP_EOL .
				"[updated_dts] TIMESTAMP DEFAULT CURRENT_TIMESTAMP" . PHP_EOL .
				" )" . PHP_EOL;
			$this->mysqli->exec($sql);

			$sql = "CREATE TABLE IF NOT EXISTS [ips] (" . PHP_EOL .
				"[ip] INTEGER PRIMARY KEY," . PHP_EOL .
				"[bad_req] INTEGER DEFAULT(0)," . PHP_EOL .
				"[auth_fail] INTEGER DEFAULT(0)," . PHP_EOL .
				"[heart_fail] INTEGER DEFAULT(0)," . PHP_EOL .
				"[reg_count] INTEGER DEFAULT(0)," . PHP_EOL .
				"[srv_count] INTEGER DEFAULT(0)," . PHP_EOL .
				"[strikes] INTEGER DEFAULT(0)," . PHP_EOL .
				"[is_banned] CHAR(1) DEFAULT('N')," . PHP_EOL .
				"[was_banned] CHAR(1) DEFAULT('N')," . PHP_EOL .
				"[note] VARCHAR(16) DEFAULT NULL," . PHP_EOL .
				"[last_dts] TIMESTAMP DEFAULT CURRENT_TIMESTAMP )" . PHP_EOL;
			$this->mysqli->exec($sql);

			/*$sql = "CREATE TABLE IF NOT EXISTS [iplog] (" . PHP_EOL .
				"[id] int(10) AUTO INCREMENT PRIMARY KEY," . PHP_EOL .
				"[ip] INTEGER NOT NULL," . PHP_EOL .
				"[message] varchar(80) DEFAULT NULL," . PHP_EOL .
				"[created_dts] TIMESTAMP DEFAULT CURRENT_TIMESTAMP )" . PHP_EOL;
			$this->mysqli->exec($sql);*/
		}
	}
	function exec($sql) {
		$this->mysqli->exec($sql);
	}
	function query($sql) {
		$result = $this->mysqli->query($sql);
		return $result;
	}
	function query_change($sql) {
		$result = $this->mysqli->exec($sql);
		return true;  // $this->myslqi->changes() ???
	}
	function creIpRec($iplong) {
		$ret = $this->query("SELECT * FROM ips WHERE ip = ".$iplong.";");
		if ($ret && $row = $ret->fetchArray()) {
			$this->query_change("UPDATE ips SET last_dts = CURRENT_TIMESTAMP WHERE ip = ".$iplong.";");
			return;
		}
		$this->query_change("INSERT INTO ips (ip) VALUES (".$iplong.");");
	}
	function allowIp($iplong) {
		$ret = $this->query("SELECT * FROM ips WHERE ip = ".$iplong.";");
		if ($rec = $ret->fetchArray()) {
			if ($rec["is_banned"] != 'N') {
				return false;
			}
		}
		return true;
	}
	function updIp404($iplong) {
		$this->query_change("UPDATE ips SET bad_req = bad_req + 1, last_dts = CURRENT_TIMESTAMP WHERE ip = ".$iplong.";");
	}
	function updIpStrikes($iplong) {
		$this->query_change("UPDATE ips SET strikes = strikes + 1,  last_dts = CURRENT_TIMESTAMP WHERE ip = ".$iplong.";");
	}
	function updIpAuthFail($iplong) {
		$this->query_change("UPDATE ips SET auth_fail = auth_fail + 1,  last_dts = CURRENT_TIMESTAMP WHERE ip = ".$iplong.";");
	}
	function getServerById($server_id) {
		$ret = $this->query("SELECT * FROM servers WHERE server_id = '" . $server_id . "';");
		if ($rec = $ret->fetchArray()) {
			return $rec;
		}
		return false;
	}
	function getServer($server_id, $server_token) {
		$ret = $this->query("SELECT * FROM servers WHERE server_id = '" . $server_id . "' AND server_token='".$server_token."';");
		if ($rec = $ret->fetchArray()) {
			return $rec;
		}
		return false;
	}
	function getServers() {
		$ret = $this->query("SELECT server_id, [name], host, port, [version], max_users, cur_users, lang, region, updated_dts FROM servers ORDER BY cur_users DESC;");
		return $ret;
	}
	function creOrUpdServer($server_id, $server_token, $host, $str_name, $str16_salt, $int_port, $int_prot_ver, $char_is_public, $int_max_users) {
		global $client_ip_int;

		if ($this->getServer($server_id, $server_token)) {
			$this->query_change("UPDATE servers SET host='".$host."', name='".$str_name."', salt='".$str16_salt."', port=".$int_port.", version=".$int_prot_ver.", ispublic='".$char_is_public."', max_users=".$int_max_users.", updated_dts=CURRENT_TIMESTAMP WHERE server_id='".$server_id."';");
			return true;
		} else {
			if ($this->getServerById($server_id)) {
				// server exists, but token doesn't match.  this means the server changed their salt or someone is tying to use the same name
				$this->query_change("UPDATE ips SET auth_fail = auth_fail + 1, last_dts=CURRENT_TIMESTAMP WHERE ip=".$client_ip_int.";");
			} else {
				$this->query_change("INSERT INTO servers (server_id, server_token, host, [name], salt, port, [version], ispublic, max_users, created_ip) VALUES ('".$server_id."', '".$server_token."', '".$host."', '".$str_name."', '".$str16_salt."', ".$int_port.", ".$int_prot_ver.", '".$char_is_public."', ".$int_max_users.", ".$client_ip_int.");");
				$this->query_change("UPDATE ips SET srv_count = srv_count + 1, last_dts=CURRENT_TIMESTAMP WHERE ip=".$client_ip_int.";");
				return true;
			}
		}
		return false;
	}
	function updServerHeartbeat($server_id, $server_token, $host, $str_name, $int_port, $int_prot_ver, $char_is_public, $int_max_users, $cur_users) {
		global $client_ip_int;

		if ($this->getServer($server_id, $server_token)) {
			$this->query_change("UPDATE servers SET host='".$host."', name='".$str_name."', port=".$int_port.", version=".$int_prot_ver.", ispublic='".$char_is_public."', max_users='".$int_max_users."', cur_users='".$cur_users."', updated_dts=CURRENT_TIMESTAMP WHERE server_id='".$server_id."';");
		} else {
			$this->query_change("UPDATE ips SET heart_fail = heart_fail + 1, last_dts=CURRENT_TIMESTAMP WHERE ip=".$client_ip_int.";");
		}
	}
	function isAvailableUserName($unm) {
		$ret = $this->query("select * from users where username = '" . $unm . "';");
		while ($row = $ret->fetchArray()) {
			return false;
		}
		return true;
	}
	function isAvailable($email) {
		$ret = $this->query("select * from users where email = '" . $email . "'");
		// this doesn't work for some reason
		//return $ret->fetchArray();
		while ($row = $ret->fetchArray()) {
			return false;
		}
		return true;
	}
	function createUser($username, $password, $email, $secqu, $userip, $saltChar6 = "salty") {
		$encrypted = md5(md5($password) . $saltChar6);

		return $this->query("insert into [users] ([userid], [username], [password], [email], [secques], [regip], [salt]) VALUES ('" . $username . "', '" . $username . "', '" . $encrypted . "', '" . $email . "', '" . $secqu . "', '" . $userip . "', '" . $saltChar6 . "');");
	}
	function chkPasswd($username, $passwd) {
		$ret = $this->query("select * from users where username = '" . $username . "'");
		if ($rec = $ret->fetchArray()) {
			$ucpass = $rec["password"];
			$salt = $rec["salt"];
			$playername = $rec["userid"];
			$playeruuid = UUID::getUserUuid($playername);
			$skinuuid = ""; // file_get_contents("https://api.zhjlfx.cn/?type=getuuid&method=email&email=".$email);

			$encrypted = md5(md5($passwd) . $salt);
			$rs = ($encrypted == $ucpass);
			if ($rs) {
				if ($skinuuid == '') {
					$this->crePlayerUuid($playeruuid, $username, $playername);
					return $rs;
				} else {
					$this->crePlayerUuid($skinuuid, $username, $playername);
					return $rs;
				}
			}
		}
		return false;
	}
	function updateUser($username, $userid) {
		$this->query_change("update users set lastlogintime = '" . time() . "', userid = '" . $userid . "' where username = '" . $username . "';");
	}
	function getUserid($username) {
		$ret = $this->query("select * from users where username = '" . $username . "'");
		if ($rec = $ret->fetchArray()) {
			return $rec[13];
		}
		return false;
	}
	function creToken($cli_token, $userid) {
		$acctoken = UUID::getUserUuid(uniqid() . $cli_token);
		$ret = $this->query("select * from tokens where owner_uuid = '" . $userid . "';");
		if ($rec = $ret->fetchArray()) {
			$this->query_change("update tokens set acc_token = '" . $acctoken . "', cli_token = '" . $cli_token . "', state = 1 where owner_uuid = '" . $userid . "';");
		} else {
			$this->query_change("insert into tokens (acc_token, cli_token, state, owner_uuid) values ('" . $acctoken . "', '" . $cli_token . "', 1, '" . $userid . "');");
		}
	}
	function getTokensByOwner($user_uuid) {
		$ret = $this->query("select * from tokens where owner_uuid = '" . $user_uuid . "';");
		if ($rec = $ret->fetchArray()) {
			return array($rec[0], $rec[1]);
		}
		return false;
	}
	function crePlayerUuid($playeruuid, $username, $playername) {
		$ret = $this->query("select * from users where username = '" . $username . "';");
		if ($rec = $ret->fetchArray()) {
			$uuid = $rec["uuid"];
			if ($uuid == "") {
				$this->query_change("update users set uuid = '" . $playeruuid . "' where username = '" . $username . "';");
				$this->addPlayerInfo($playername, $playeruuid);
				return;
			}
		}
		$playeruuid = $uuid;
		$this->addPlayerInfo($playername, $playeruuid);
	}
	function getProfileByOwner($userid) {
		$ret = $this->query("select * from users where userid = '" . $userid . "'");
		if ($rec = $ret->fetchArray()) {
			return new Profile($rec[1], $rec[14], $rec[15]);
		}
		return false;
	}
	function profileToken($acctoken, $player_uuid) {
		$this->query_change("update tokens set profile = '" . $player_uuid . "' where acc_token = '" . $acctoken . "';");
	}
	function getUseridByAcctoken($acctoken) {
		$ret = $this->query("select * from tokens where acc_token = '" . $acctoken . "';");
		if ($rec = $ret->fetchArray()) {
			return $rec[5];
		}
		return false;
	}
	function isAcctokenAvailable($acctoken) {
		$ret = $this->query("select * from tokens where acc_token = '" . $acctoken . "';");
		if ($rec = $ret->fetchArray()) {
			return true;
		}
		return false;
	}
	function chkAcctoken($acctoken, $clitoken) {
		$ret = $this->query("select * from tokens where acc_token = '" . $acctoken . "';");
		if ($rec = $ret->fetchArray()) {
			return ($clitoken == $rec[1]);
		}
		return false;
	}
	function getTokenState($acctoken) {
		$ret = $this->query("select * from tokens where acc_token = '" . $acctoken . "';");
		if ($rec = $ret->fetchArray()) {
			return $rec[4];
		}
		return false;
	}
	function setTokenState($acctoken) {
		$this->query_change("update tokens set state = -1 where acc_token = '" . $acctoken . "';");
	}
	function killTokensByOwner($userid) {
		$this->query_change("update tokens set state = -1 where owner_uuid = '" . $userid . "';");
	}
	function updateAllTokenState() {
		$this->query_change("update tokens set state = 0 where ptime <= DATETIME('now', '-120 minutes');");
		$this->query_change("update tokens set state = -1 where ptime <= DATETIME('now', '-10 days');");
		return $this->query_change("delete from tokens where state = -1");
	}
	function chkProfileToken($acctoken, $player_uuid) {
		$ret = $this->query("select * from tokens where acc_token = '" . $acctoken . "';");
		if ($rec = $ret->fetchArray()) {
			return ($player_uuid == $rec[2]);
		}
		return false;
	}
	function creSession($server_id, $acc_token, $ip) {
		$this->query_change("insert into sessions (server_id, acc_token, ipaddr, o_time) values ('" . $server_id . "','" . $acc_token . "','" . $ip . "', now());");
	}
	function chkSession($playername, $serverid, $ipaddr) {
		$ret = $this->query("select * from sessions where server_id = '" . $serverid . "'");
		if ($rec = $ret->fetchArray()) {
			$owner_accctoken = $rec[1];
			$owner_userid = $this->getUseridByAcctoken($owner_accctoken);
			$player = $this->getProfileByOwner($owner_userid)->name;
			return (($player == $playername) && ($ipaddr == 'NONE' || $ipaddr == $rec[2]));
		}
		return false;
	}
	function getAcctokenByServerid($serverid) {
		$ret = $this->query("select * from sessions where server_id = '" . $serverid . "';");
		if ($rec = $ret->fetchArray()) {
			return $rec[1];
		}
		return false;
	}
	function getProfileByUuid($playeruuid) {
		$ret = $this->query("select * from users where uuid = '" . $playeruuid . "';");
		if ($rec = $ret->fetchArray()) {
			return new Profile($rec[1], $rec[14], $rec[15]);
		}
		return false;
	}
	function getProfileByPlayer($playername) {
		$ret = $this->query("select * from users where username = '" . $playername . "';");
		if ($rec = $ret->fetchArray()) {
			return new Profile($rec[1], $rec[14], $rec[15]);
		}
		return false;
	}
	function updateAllSessionState() {
		//$this->query_change("delete from sessions where date(o_time) <= date_sub(now(),interval 30 second);");
		$this->query_change("delete from sessions where o_time <= DATETIME('now', '-120 minutes');");
	}
	function updateSkinData($uuid) {
		//$texturedata = "texturedata for".$uuid; // = file_get_contents("https://api.zhjlfx.cn/?type=getjson&uuid=".$uuid);
		//$this->query_change("update users set texturedata = '" . $texturedata . "' where uuid = '" . $uuid . "';");
	}
	function addPlayerInfo($playername, $playeruuid) {
		$ret = $this->query("select * from chkname where uuid = '" . $playeruuid . "'");
		if ($rec = $ret->fetchArray()) {
			$this->query_change("update chkname set playername = '" . $playername . "' where uuid = '" . $playeruuid . "';");
		} else {
			$this->query_change("insert into chkname (uuid, playername) values ('" . $playeruuid . "', '" . $playername . "');");
		}
	}
	function getPlayerUuidByAcctoken($acctoken) {
		$ret = $this->query("select * from tokens where acc_token = '" . $acctoken . "';");
		if ($rec = $ret->fetchArray()) {
			return $rec[2];
		}
		return false;
	}
	function isPlayerNameChanged($uuid) {
		$getname = $this->query("select * from users where uuid = '" . $uuid . "';");
		if ($recname = $getname->fetchArray(SQLITE3_NUM)) {
			$getsavedname = $this->query("select * from chkname where uuid = '" . $uuid . "';");
			if ($recsave = $getsavedname->fetchArray(SQLITE3_NUM)) {
				$rs = ($recname[1] !== $recsave[1]);
				return $rs;
			}
		}
		return false;
	}
}
