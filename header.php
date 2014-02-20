<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	session_start();
	define('DIR',dirname(__FILE__));
	$locale = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
	if(strpos(',',$locale) !== false){
		$locale = substr($locale,0,strpos(',',$locale));
	}
	define("LOCALE",$locale);
	setlocale(LC_ALL,LOCALE);
	bindtextdomain('omninet',DIR.'/lang');
	textdomain('omninet');
	require_once(DIR.'/config.php');
	require_once(DIR."/lib/irc.php");
	require_once(DIR."/lib/security.php");
	require_once(DIR."/lib/users.php");
	require_once(DIR."/lib/servers.php");
	require_once(DIR."/lib/opers.php");
	require_once(DIR."/lib/forms.php");
	require_once(DIR."/lib/configuration.php");
	if(!empty($_SERVER['HTTP_CLIENT_IP'])){
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}elseif(!empty($_SERVER['REMOTE_ADDR'])){
		$ip = $_SERVER['REMOTE_ADDR'];
	}else{
		$ip = '';
	}
	define('USER_IP',$ip);
	function get_sql(){
		static $sql;
		if(!$sql){
			$sql = new mysqli(MYSQL_SERVER,MYSQL_USER,MYSQL_PASSWORD,MYSQL_DATABASE);
			if ($sql->connect_errno) {
				echo "Failed to connect to MySQL: (" . $sql->connect_errno . ") " . $sql->connect_error;
				die();
			}
		}
		return $sql;
	}
	function query($query,$args=Array()){
		$sql = get_sql();
		for ($i=0;$i<count($args);$i++){
			if(is_string($args[$i])){
				$args[$i] = $sql->real_escape_string($args[$i]);
			}elseif(!is_numeric($args[$i])){
				return false;
			}
		}
		return $sql->query(vsprintf($query,$args));
	}
?>
