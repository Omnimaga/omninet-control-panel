<?php
	require_once(dirname(dirname(__FILE__))."/header.php");
	require_once("configuration.php");
	require_once("users.php");
	switch(get_conf('2-factor-method')){
		case 'authy':
			require_once("authy-php/Authy.php");
		//break;
		case 'google-authenticator':
			require_once("GoogleAuthenticator.php");
		break;
	}
	function get_api(){
		static $api;
		if(!$api){
			switch(get_conf('2-factor-method')){
				case 'authy':
					$api = new Authy_Api(get_conf('authy-api-key'),get_conf('authy-endpoint'));
				break;
				case 'google-authenticator':
					$api = new PHPGangsta_GoogleAuthenticator();
				break;
			}
		}
		return $api;
	}
	function login($nick,$pass,$type,$effective_role=null){
		if($type == 'user'){
			$user = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$nick,$pass,'NickServ','info',Array($nick));
			if($user[0]){
				$user[2] = explode('&#10;',$user[1]);
				$user[3] = Array();
				foreach($user[2] as $k => $row){
					$row = preg_split('/\s+:\s/',$row);
					if(isset($row[1])){
						$row[2] = explode(' ',$row[1]);
					}else{
						$row[1] = '';
						$row[2] = Array();
					}
					$user[3][$row[0]] = Array($row[1],$row[2]);
				}
				$_SESSION['password'] = $pass;
				$_SESSION['key'] = uniqid();
				$_SESSION['real_name'] = $nick;
				if(isset($user[3]['Email'][0])){
					$_SESSION['email'] = $user[3]['Email'][0];
				}else{
					$_SESSION['email'] = '';
				}
				if($res = query("SELECT u.api_key, u.real_name FROM users u WHERE lower(u.nick) = lower('%s')",Array($nick))){
					if($res->num_rows == 1){
						$res = $res->fetch_assoc();
						$_SESSION['key'] = $res['api_key'];
						$_SESSION['real_name'] = $res['real_name'];
					}
				}
				setcookie('key',$_SESSION['key'],null,'/');
				setcookie('user',$nick,null,'/');
				setcookie('type','user',null,'/');
				return true;
			}else{
				return __("Could not log in: ").@$user[1].": ".@$user[3];
			}
		}elseif($type=='persona'){
			if(!$user = get_user_obj($nick,$effective_role)){
				return __("User")." {$nick} ".__("does not exist");
			}
			if(!isset($_COOKIE['personaUser'])){
				return false;
			}
			if(!in_array($_COOKIE['personaUser'],get_emails($user['id']))){
				return __("Invalid persona email");
			}
			setcookie('user',$nick,null,'/');
			setcookie('key',$user['api_key'],null,'/');
			setcookie('type',$user['type'],null,'/');
			return true;
		}else{
			if(!$user = get_user_obj($nick,$type)){
				return __("User")." {$nick} ".__("does not exist");
			}
			if($user['password'] != mkpasswd($pass,$user['salt'])){
				return __("Invalid password");
			}
			setcookie('user',$nick,null,'/');
			setcookie('key',$user['api_key'],null,'/');
			setcookie('type',$user['type'],null,'/');
			return true;
		}
	}
	function verify($token){
		$api = get_api();
		if($u = is_logged_in()){
			switch(get_conf('2-factor-method')){
				case 'authy':
					$verification = $api->verifyToken($u['secret_key'],$token);
					if($verification->ok()){
						setcookie('token',$u['secret_key'],null,'/');
						$r = true;
					}else{
						$r = __('Failed to create Authy user').': ';
						foreach($verification->errors() as $field => $message){
							$message = json_decode($message);
							$r .= $message['message'];
						}
						logout();
					}
				break;
				case 'google-authenticator':
					if($api->verifyCode($u['secret_key'],$token,2)){
						$_SESSION['secret_key'] = $u['secret_key'];
						$r = true;
					}else{
						$r = __("Token didn't match ").$u['secret_key'];
					}
				break;
				default:
					$r = true;
			}
		}else{
			$r = __("You have been logged out");
		}
		return $r;
	}
	function delete_token(){
		$r = true;
		$api = get_api();
		$u = is_logged_in();
		if($u){
			switch(get_conf('2-factor-method')){
				case 'authy':
					$deletion = $api->deleteUser($u['secret_key']);
					if($deletion->ok()){
						setcookie('secret_key','',time() - 3600,'/');
						if(!query("UPDATE users u SET u.secret_key=NULL WHERE u.id=%d",Array($u['id']))){
							$r = __('Failed to disable 2-factor authentication');
						}
					}else{
						$r = __('Failed to disable 2-factor authentication').': ';
						foreach($deletion->errors() as $field => $message){
							$message = json_decode($message);
							$r .= $message->message;
						}
					}
				break;
				case 'google-authenticator':
					setcookie('secret_key','',time() - 3600,'/');
					if(!query("UPDATE users u SET u.secret_key=NULL WHERE u.id=%d",Array($u['id']))){
						$r = __('Failed to disable 2-factor authentication');
					}
				break;
				default:
			}
		}
		return $r;
	}
	function register_token(){
		$api = get_api();
		$u = is_logged_in();
		if($u){
			switch(get_conf('2-factor-method')){
				case 'authy':
					if(isset($_GET['country-code'])){
						if(isset($_GET['cellphone'])){
							$user = $api->registerUser($u['email'],$_GET['cellphone'],$_GET['country-code']);
							if($user->ok()){
								query("UPDATE users u SET u.secret_key='%s' WHERE u.id=%d",Array($user->id(),$u['id']));
								$r = true;
							}else{
								$r = __('Failed to create Authy user').': ';
								foreach($user->errors() as $field => $message){
									$message = json_decode($message);
									$r .= $message['message'];
								}
							}
						}else{
							$r = __("No cell number set");
						}
					}else{
						$r = __("No country code set");
					}
				break;
				case 'google-authenticator':
					if(isset($_GET['token'])){
						if(isset($_SESSION['secret_key'])){
							if($api->verifyCode($_SESSION['secret_key'],$_GET['token'], 2)){
								query("UPDATE users u SET u.secret_key='%s' WHERE u.id=%d",Array($_SESSION['secret_key'],$u['id']));
								$r = true;
							}else{
								$r = __('Could not register');
							}
						}else{
							$r = __('No secret key defined');
						}
					}else{
						$r = __('No token provided');
					}
				break;
				default:
					$r = true;
			}
		}else{
			$r = __("You have been logged out");
		}
		return $r;
	}
	function is_logged_in(){
		$user = false;
		if(isset($_COOKIE['user']) && isset($_COOKIE['key']) && isset($_COOKIE['type'])){
			$user = get_user_obj($_COOKIE['user'],$_COOKIE['type']);
			if(!$user || $user['api_key'] != $_COOKIE['key']){
				$user = false;
			}
		}
		return $user;
	}
	function is_verified(){
		$api = get_api();
		$user = is_logged_in();
		$r = false;
		if($user){
			if(!isset($user['secret_key']) || is_null($user['secret_key']) || $user['secret_key'] == ''){
				$r = true;
			}else{
				switch(get_conf('2-factor-method')){
					case 'authy':
						if(isset($_COOIKE['token']) && $user['secret_key'] == $_COOKIE['token']){
							$r = true;
						}
					break;
					case 'google-authenticator':
						if(isset($_SESSION['secret_key']) && $_SESSION['secret_key'] == $user['secret_key']){
							$r = true;
						}
					break;
					default:
						$r = true;
				}
			}
		}
		return $r;
	}
	function logout(){
		setcookie('key','',time() - 3600,'/');
		setcookie('user','',time() - 3600,'/');
		setcookie('type','',time() - 3600,'/');
		setcookie('personaUser','',time() - 3600,'/');
		switch(get_conf('2-factor-method')){
			case 'authy':break;
			case 'google-authenticator':
			default:
				setcookie('token','',time() - 3600,'/');
		}
		unset($_SESSION['secret_key']);
		if (isset($_COOKIE[session_name()])){
			setcookie(session_name(),"",time()-3600,'/');
		}
		$_SESSION = array();
		session_unset();
		session_destroy();
		session_write_close();
		session_regenerate_id(true);
	}
	function mkpasswd($input,$salt=null){
		$firsthash = pack("H*", sha1($input));
		srand(time());
		if($salt === null){
			$salt = pack("c6", rand(0,255), rand(0,255), rand(0,255), rand(0,255), rand(0,255), rand(0,255));
		}else{
			$salt = base64_decode($salt);
		}
		$finalhash = pack("H*", sha1($firsthash.$salt));
		return "$".base64_encode($salt)."$".base64_encode($finalhash);
	}
?>