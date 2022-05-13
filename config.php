<?php
//Basic configuration of external login server//
$servername = "Portland Softworks LLC"; //External login server name
$impname = "portland-minecraft-auth";
$impver = "0.1"; //version number
$skinurl = array(
    ".zhjlfx.cn",//Skin station link, you can fill in multiple
    ".minecraft.net"
);
$homepage = "https://portlandsoft.works"; //website homepage
$regurl = "https://portlandsoft.works/registration"; //Player registration address
//External login server key configuration//
$publickey = file_get_contents($_SERVER['DOCUMENT_ROOT'] ."/keys/yggdrasil-public-key.pem"); //public key file
$privatekey = file_get_contents($_SERVER['DOCUMENT_ROOT'] ."/keys/yggdrasil-private-key.pem"); //private key

//DATABASE//
$host = 'host'; //database address
$port = 3306; //database port
$user = 'user'; //database username
$pass = 'pass'; //database password
$dbname = 'database'; //Database name
//Genuine direct login support
$mojanglogin = true;
