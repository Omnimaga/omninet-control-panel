<?php
	require_once(dirname(dirname(__FILE__))."/header.php");
	require_once('xmlrpc.php');
	function atheme_login($hostname,$port, $path, $username, $password){
		$message = new xmlrpcmsg("atheme.login");
		$message->addParam(new xmlrpcval($username, "string"));
		$message->addParam(new xmlrpcval($password, "string"));
		$client = new xmlrpc_client($path, $hostname, $port);
		$response = $client->send($message);
		if(!$response->faultCode()){
			$session = explode("<string>", $response->serialize());
			$session = explode("</string", $session[1]);
			$session = $session[0];
			return array(true,$session);
		}else{
			return array(
				false,
				'['.$response->faultCode().'] '.$response->faultString()
			);
		}
	}
	function atheme_command($hostname, $port, $path, $sourceip, $username, $password, $service, $command, $params=NULL){
		$message = new xmlrpcmsg("atheme.login");
		$message->addParam(new xmlrpcval($username, "string"));
		$message->addParam(new xmlrpcval($password, "string"));
		$client = new xmlrpc_client($path, $hostname, $port);
		$response = $client->send($message);

		$session = NULL;
		if(!$response->faultCode()){
			$session = explode("<string>", $response->serialize());
			$session = explode("</string", $session[1]);
			$session = $session[0];
		}else{
			switch($response->faultCode()){
				case 1:
					$m = __('Insufficient Parameters to login');
				break;
				case 3:
					$m = __("Account is not registered");
				break;
				case 5:
					$m = __("Invalid Username/Password");
				break;
				case 6:
					$m = __("Account is frozen");
				break;
				default:
					$m = __("Could not log in");
			}
			return array(false,$m,$response->faultCode());
		}
		$message = new xmlrpcmsg("atheme.command");
		$message->addParam(new xmlrpcval($session, "string"));
		$message->addParam(new xmlrpcval($username, "string"));
		$message->addParam(new xmlrpcval($sourceip, "string"));
		$message->addParam(new xmlrpcval($service, "string"));
		$message->addParam(new xmlrpcval($command, "string"));
		if($params != NULL){
			foreach($params as $param){
				$message->addParam(new xmlrpcval($param, "string"));
			}
		}
		$response = $client->send($message);
		if(!$response->faultCode()){
			$response = explode("<string>", $response->serialize());
			$response = explode("</string", $response[1]);
			$response = $response[0];
			return array(true,$response,0);
		}else{
			return array(false,"Command failed: " . $response->faultString(),$response->faultCode());
		}
	}
	$ircret = "";
	function ircputs($line){
		global $msg;
		global $irc;
		$msg .= str_replace(get_conf('rehash-pass','string'),'**********',$line);
		try{
			error_reporting(0);
			$r = fputs($irc,$line);
			error_reporting(E_ALL);
		}catch(Exception $e){
			$r = false;
			ircclose($e->code,$e->message);
		}
		return $r;
	}
	function ircclose($code=0,$message=null,$ret_type='string'){
		global $msg;
		global $irc;
		global $ircret;
		try{
			error_reporting(0);
			$msg .= 'QUIT :'.$message;
			fputs($irc,'QUIT :'.$message);
			error_reporting(E_ALL);
		}catch(Exception $e){}
		while(!feof($irc) && $line = fgets($irc,128)){
			if(is_string($line)){
				$msg .= $line;
			}
		}
		fclose($irc);
		if($ret_type == 'string'){
			$ircret = '{"code":'.$code.',"message":"'.$message.'","log":'.json_encode($msg).'}';
		}else{
			$ircret = array(
				'code'=>$code,
				'message'=>$message,
				'log'=>$msg
			);
		}
		return $ircret;
	}
	function isval($src,$prop,$val){
		return isset($src[$prop]) && $src[$prop] == $val;
	}
	function ircrehash(){
		global $msg;
		global $irc;
		global $ircret;
		global $u;
		global $user;
		if(!isset($u)){
			$u = $user;
		}
		$msg = '';
		if(!$irc = fsockopen(get_conf('irc-server'),get_conf('irc-port'))){return ircclose(1,__("Could not connect."));}
		stream_set_timeout($irc,1) or ircclose(2,__("Could not set timeout."));
		while(!feof($irc)&&!$msg = fgets($irc,128)){}
		if(!ircputs("NICK RehashServ\r\n")){return $ircret;}
		if(!ircputs("USER RehashServ omni.irc.omnimaga.org RehashServ :RehashServ\r\n")){return $ircret;}
		while(!feof($irc)){
			$line = fgets($irc,128);
			if(is_string($line)){
				$msg .= $line;
				$data = explode(' ',$line);
				if(isval($data,1,'433')){
					return ircclose(4,__("RehashServ is already running."));
				}elseif(strrpos($line,'ERROR :Closing Link:') !== false){
					return ircclose(3,__("IRC Server refused the connection."));
				}elseif($data[0] == 'PING'){
					if(!ircputs("PONG {$data[1]}")){return $ircret;}
				}elseif(isval($data,1,'001')){
					break;
				}
			}
		}
		if(!ircputs("IDENTIFY ".get_conf('rehash-pass','string')."\r\n")){return $ircret;}
		while(!feof($irc)){
			$line = fgets($irc,128);
			if(is_string($line)){
				$msg .= $line;
				$data = explode(' ',$line);
				if(isval($data,1,'433')){
					return ircclose(4,__("RehashServ is already running."));
				}elseif(strrpos($line,'ERROR :Closing Link:') !== false){
					return ircclose(3,__("IRC Server refused the connection."));
				}elseif(strrpos($line,":You are now identified for") !== false){
					break;
				}elseif(strrpos($line,'Password incorrect.') !== false){
					return ircclose(5,__("Failed to authenticate with NickServ"));
				}
			}
		}
		if(!ircputs("HS ON\r\n")){return $ircret;}
		while(!feof($irc)){
			$line = fgets($irc,128);
			if(is_string($line)){
				$msg .= $line;
				$data = explode(' ',$line);
				if(isval($data,1,'433')){
					return ircclose(4,__("RehashServ is already running."));
				}elseif(strrpos($line,'ERROR :Closing Link:') !== false){
					return ircclose(3,__("IRC Server refused the connection."));
				}elseif(strrpos($line,':Your vhost of') !== false && strrpos($line,'is now activated') !== false){
					break;
				}elseif(strrpos($line,"Please contact an Operator to get a vhost assigned to this nick") !== false){
					return ircclose(6,__("vhost not set."));
				}
			}
		}
		if(!ircputs("OPER RehashServ ".get_conf('rehash-pass','string')."\r\n")){return $ircret;}
		if(!ircputs("REHASH -global\r\n")){return $ircret;}
		if(!ircputs("WALLOPS :{$u['nick']} has rehashed the server\r\n")){return $ircret;}
		try{
			error_reporting(0);
			$msg .= 'QUIT :'.$message;
			fputs($irc,'QUIT :'.$message);
			error_reporting(E_ALL);
		}catch(Exception $e){}
		while(!feof($irc) && $line = fgets($irc,128)){
			if(is_string($line)){
				$msg .= $line;
			}
		}
		fclose($irc);
		if(strrpos($msg,':*** Notice -- Configuration loaded without any problems ..') === false){
			return '{"code":6,"message":"'.__('There is an error in the config. See console for output.').'","log":'.json_encode($msg).'}';
		}
		return '{"code":0,"message":"'.__('Rehashed. View console for output.').'","log":'.json_encode($msg).'}';
	}
	function irccommands($commands,$runas="RehashServ",$nick='RunServ',$isreg=true){
		global $msg;
		global $irc;
		global $ircret;
		global $u;
		global $user;
		$ircret = array(
			'code'=>1
		);
		if(!isset($u)){
			$u = $user;
		}
		$msg = '';
		if(!$irc = fsockopen(get_conf('irc-server'),get_conf('irc-port'))){return ircclose(1,__("Could not connect."),'array');}
		stream_set_timeout($irc,1) or ircclose(2,__("Could not set timeout."),'array');
		while(!feof($irc)&&!$msg = fgets($irc,128)){}
		if($runas == 'RehashServ'){
			if(!ircputs("NICK RehashServ\r\n")){return $ircret;}
			if(!ircputs("USER RehashServ omni.irc.omnimaga.org RehashServ :RehashServ\r\n")){return $ircret;}
		}else{
			if(!ircputs("NICK {$nick}\r\n")){return $ircret;}
			if(!ircputs("USER {$runas} omni.irc.omnimaga.org {$runas} :{$runas}\r\n")){return $ircret;}
		}
		while(!feof($irc)){
			$line = fgets($irc,128);
			if(is_string($line)){
				$msg .= $line;
				$data = explode(' ',$line);
				if(isval($data,1,'433')){
					return ircclose(4,__("RunServ is already running."),'array');
				}elseif(strrpos($line,'ERROR :Closing Link:') !== false){
					return ircclose(3,__("IRC Server refused the connection."),'array');
				}elseif($data[0] == 'PING'){
					if(!ircputs("PONG {$data[1]}")){return $ircret;}
				}elseif(isval($data,1,'001')){
					break;
				}
			}
		}
		if($runas == 'RehashServ'){
			if(!ircputs("OPER RehashServ ".get_conf('rehash-pass','string')."\r\n")){return $ircret;}
			if(!ircputs("IDENTIFY RehashServ ".get_conf('rehash-pass','string')."\r\n")){return $ircret;}
		}else if($isreg){
			if(!ircputs("IDENTIFY {$runas} ".$_SESSION['password']."\r\n")){return $ircret;}
		}
		while(!feof($irc)){
			$line = fgets($irc,128);
			if(is_string($line)){
				$msg .= $line;
				$data = explode(' ',$line);
				if(isval($data,1,'433')){
					return ircclose(4,__("RunServ is already running."),'array');
				}elseif(isval($data,1,'375')){
					while(!feof($irc)){
						$line = fgets($irc,128);
						if(is_string($line)){
							$msg .= $line;
							$data = explode(' ',$line);
							if(isval($data,1,'376')){
								break;
							}
						}
					}
					if(!$isreg){
						break;
					}
				}elseif(strrpos($line,'ERROR :Closing Link:') !== false){
					return ircclose(3,__("IRC Server refused the connection."),'array');
				}elseif(strrpos($line,":You are now identified for") !== false){
					break;
				}elseif(strrpos($line,'Password incorrect.') !== false){
					return ircclose(5,__("Failed to authenticate with NickServ"),'array');
				}
			}
		}
		if($runas == 'RehashServ'){
			if(!ircputs("HS ON\r\n")){return $ircret;}
			while(!feof($irc)){
				$line = fgets($irc,128);
				if(is_string($line)){
					$msg .= $line;
					$data = explode(' ',$line);
					if(isval($data,1,'433')){
						return ircclose(4,__("RunServ is already running."),'array');
					}elseif(strrpos($line,'ERROR :Closing Link:') !== false){
						return ircclose(3,__("IRC Server refused the connection."),'array');
					}elseif(strrpos($line,':Your vhost of') !== false && strrpos($line,'is now activated') !== false){
						break;
					}elseif(strrpos($line,"Please contact an Operator to get a vhost assigned to this nick") !== false){
						return ircclose(6,__("vhost not set."),'array');
					}
				}
			}
		}
		foreach($commands as $k => $command){
			if(!ircputs($command."\r\n")){return $ircret;}
			time_nanosleep(0, 250000000);
		}
		try{
			error_reporting(0);
			$msg .= 'QUIT :'.$message;
			fputs($irc,'QUIT :'.$message);
			error_reporting(E_ALL);
		}catch(Exception $e){}
		while(!feof($irc) && $line = fgets($irc,128)){
			if(is_string($line)){
				$msg .= $line;
			}
		}
		$msg .= 'QUIT :Done';
		fputs($irc,'QUIT :Done');
		fclose($irc);
		return array(
			'code'=>0,
			'log'=>$msg
		);
	}
?>