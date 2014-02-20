<?php
	header('Content-type: application/json');
	header('Access-Control-Allow-Origin: *');
	require_once("../../header.php");
	if(!isset($_GET['action'])){
		$opts = getopt('a:',Array('action:'));
		$_GET['action'] = isset($opts['action'])?$opts['action']:(isset($opts['a'])?$opts['a']:'');
	}
	$u = is_logged_in();
	switch($_GET['action']){
		case 'test':
			//$u or die();
			//print_r(atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'topic','#omnimaga'));
			echo mkpasswd('root');
			die();
		break;
		case 'login':
			isset($_GET['username']) && isset($_GET['password']) or die('{"code":2,"message":"'._('Missing username and/or password').'"}');
			isset($_GET['type']) or die('{"code":2,"message":"'._('Missing user type').'"}');
			$r = login($_GET['username'],$_GET['password'],$_GET['type']);
			if($r !== true){
				die('{"code":2,"message":"'.$r.'"}');
			}else{
				die('{"code":0}');
			}
		break;
		case 'verify':
			isset($_GET['token']) or die('{"code":1,"message":"'._('No token set').'"}');
			$r = verify($_GET['token']);
			if($r !== true){
				die('{"code":2,"message":"'.$r.'"}');
			}
			die('{"code":0,"message":"'.$r.'"}');
		break;
		case 'logout':
			logout();
			die('{"code":0}');
		break;
		case 'get-memos':
			$u or die('{"code":1,"message":"'._('You have been logged out').'"}');
			$u['type'] = 'user' && isset($_COOKIE['user']) && isset($_SESSION['password']) or die('{"code":0}');
			$res = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'MemoServ','list');
			if($res[0]){
				$res = explode('&#10;',$res[1]);
				$memos = Array();
				foreach($res as $k => $row){
					if($k != 0 && $k != 1){
						$row = preg_split('/^-\s/',$row);
						if(isset($row[1])){
							$row = explode(' ',$row[1]);
							$memo = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'MemoServ','read',Array($row[0]));
							$memo = explode('&#10;',$memo[1]);
							array_push($memos,Array(
								'id'=>$row[0],
								'from'=>$row[2],
								'date'=>Array(
									'month'=>$row[4],
									'day'=>$row[5],
									'time'=>$row[6],
									'year'=>$row[7]
								),
								'body'=>$memo[2]
							));
						}
					}
				}
				die('{"code":0,"memos":'.json_encode($memos).'}');
			}else{
				die('{"code":1,"message":"'._('Cannot fetch memos').'"}');
			}
		break;
		case 'get-news':
			$u or die('{"code":1,"message":"'._('You have been logged out').'"}');
			$u['type'] = 'user' && isset($_COOKIE['user']) && isset($_SESSION['password']) or die('{"code":0}');
			$res = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'InfoServ','list');
			if($res[0]){
				$res = explode('&#10;',$res[1]);
				$news = Array();
				foreach($res as $k => $row){
					if($k != count($res)-1){
						array_push($news,Array(
							'id'=>preg_replace('/^(\d)+:.+$/i','\1',$row),
							'title'=>preg_replace('/^\d+: \[(.+)\] .+/i','\1',$row),
							'from'=>preg_replace('/^\d+: \[.+\] by (.+) at \d\d?:\d\d? on (\d\d)\/\d\d\/\d\d\d\d: .+/i','\1',$row),
							'date'=>Array(
								'time'=>preg_replace('/^\d+: \[.+\] by .+ at (\d\d?:\d\d?) on .+/','\1',$row),
								'day'=>preg_replace('/^\d+: \[.+\] by .+ at \d\d?:\d\d? on (\d\d)\/\d\d\/\d\d\d\d: .+/i','\1',$row),
								'month'=>preg_replace('/^\d+: \[.+\] by .+ at \d\d?:\d\d? on \d\d\/(\d\d)\/\d\d\d\d: .+/i','\1',$row),
								'year'=>preg_replace('/^\d+: \[.+\] by .+ at \d\d?:\d\d? on \d\d\/\d\d\/(\d\d\d\d): .+/i','\1',$row)
							),
							'body'=>preg_replace('/^\d+: \[.+\] by .+ at \d\d?:\d\d? on \d\d\/\d\d\/\d\d\d\d: (.+)/i','\1',$row)
						));
					}
				}
				die('{"code":0,"news":'.json_encode($news).'}');
			}else{
				die('{"code":1,"message":"'._('Cannot fetch news').'"}');
			}
		break;
		case 'get-channels':
			$u or die('{"code":1,"message":"'._('You have been logged out').'"}');
			$u['type'] = 'user' && isset($_COOKIE['user']) && isset($_SESSION['password']) or die('{"code":0}');
			$res = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'NickServ','listchans');
			if($res[0]){
				$res = explode('&#10;',$res[1]);
				$channels = Array();
				foreach($res as $k => $row){
					if($k != count($res)-1){
						$flags_list = str_split(preg_replace('/^Access flag\(s\) \+(.+) in .+$/i','\1',$row));
						$flags = array();
						foreach($flags_list as $kk => $flag){
							switch($flag){
								case 'v':$name=_('Voice');break;
								case 'V':$name=_('Automatic voice');break;
								case 'h':$name=_('Halfop');break;
								case 'H':$name=_('Automatic Halfop');break;
								case 'o':$name=_('Op');break;
								case 'O':$name=_('Automatic Op');break;
								case 'a':$name=_('Admin');break;
								case 'q':$name=_('Owner');break;
								case 's':$name=_('Set');break;
								case 'i':$name=_('Invite/Getkey');break;
								case 'r':$name=_('Kick/Ban');break;
								case 'R':$name=_('Recover/Clear');break;
								case 'f':$name=_('Modify access lists');break;
								case 't':$name=_('Topic');break;
								case 'A':$name=_('View access lists');break;
								case 'F':$name=_('Founder');break;
								case 'b':$name=_('Banned');break;
								default:$name=$flag;
							}
							array_push($flags,array(
								'flag'=>$flag,
								'name'=>$name
							));
						}
						$name = preg_replace('/^Access flag\(s\) \+.+ in (.+)$/i','\1',$row);
						array_push($channels,Array(
							'name'=>$name,
							'flags'=>$flags
						));
					}
				}
				die('{"code":0,"channels":'.json_encode($channels).'}');
			}else{
				die('{"code":1,"message":"'._('Cannot fetch channels').'"}');
			}
		break;
		case 'send-memo':
			$u or die('{"code":1,"message":"'._('You have been logged out').'"}');
			isset($_GET['to']) && isset($_GET['message']) or die('{"code":1,"message":"'._('No message or user entered').'"}');
			$res = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'MemoServ','send',Array($_GET['to'],$_GET['message']));
			if($res[0]){
				if(substr($res[1],-19) == ' is not registered.'){
					die('{"code":1,"message":"'._('User').' '.$_GET['to'].' '._('does not exist').'"}');
				}else{
					die('{"code":0,"message":"'._('Memo Sent').'"}');
				}
			}else{
				die('{"code":1,"message":"'._('Cannot send memo').': '.$res[1].'"}');
			}
		break;
		case 'delete-memo':
			$u or die('{"code":1,"message":"'._('You have been logged out').'"}');
			isset($_GET['id']) or die('{"code":1,"message":"'._('No id given').'"}');
			$res = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'MemoServ','delete',Array($_GET['id']));
			if(!$res[0]){
				die('{"code":1,"message":"'._('Cannot send memo').': '+$res[1]+'"}');
			}
			die('{"code":0}');
		break;
		case 'persona-login':
			if($u){
				$register = true;
			}else{
				$register = false;
			}
			$url = get_conf('persona-endpoint');
			$assert = filter_input(
				INPUT_POST,
				'assertion',
				FILTER_UNSAFE_RAW,
				FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH
			);
			$params = 'assertion='.urlencode($assert).'&audience='.urlencode(get_conf('persona-audience'));
			$ch = curl_init();
				$options = array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_POST => 2,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_SSL_VERIFYHOST => 2,
				CURLOPT_POSTFIELDS => $params
			);
			curl_setopt_array($ch, $options);
			$result = curl_exec($ch);
			curl_close($ch);
			$result = json_decode($result);
			if($result->status == 'okay'){
				if($register && !add_email($u['id'],$result->email)){
					die('{"code":1,"message":"'._('Failed to add email').' '.$result->email.' '._('to user').' '.$u['nick'].'"}');
				}elseif(!$register && !$u = get_user_for_email($result->email)){
					die('{"code":1,"message":"'._('Email does not match any users').'"}');
				}
				setcookie('personaUser',$result->email,null,'/');
				$pass = null;
				if(isset($_SESSION['password']) && !is_null($_SESSION['password']) && $_SESSION['password'] != ''){
					$pass = $_SESSION['password'];
				}
				$types = get_user_types($u['id']);
				$r = login($u['nick'],$pass,'persona',$types[0]);
				if($r !== true){
					if($r){
						die('{"code":2,"message":"'.$r.'"}');
					}else{
						die('{"code":2}');
					}
				}else{
					die('{"code":0,"assertion":'.json_encode($result).'}');
				}
			}else{
				die('{"code":1,"message":"'.$result->reason.'"}');
			}
		break;
		case 'persona-remove':
			$u or die('{"code":1,"message":"'._('You have been logged out').'"}');
			isset($_GET['id']) or die('{"code":1,"message":"'._('No ID set').'"}');
			if(!remove_email($u['id'],$_GET['id'],true)){
				die('{"code":1,"message":"'._('Could not remove email address').'"}');
			}
			die('{"code":0}');
		break;
		case '2-factor-register':
			$r = register_token();
			if($r !== true){
				die('{"code":1,"message":"'.$r.'"}');
			}
			die('{"code":0}');
		break;
		case '2-factor-delete':
			$u or die('{"code":1,"message":"'._('You have been logged out').'"}');
			$r = delete_token($u['id']);
			if($r !== true){
				die('{"code":1,"message":"'.$r.'"}');
			}
			die('{"code":0,"message":"'._('2-factor disabled.').'"}');
		break;
		case 'ping':
			$u or die('{"code":1,"message":"'._('You have been logged out').'"}');
			die('{"code":0}');
		break;
		case 'newpass':
			$u && isset($_GET['password']) && isset($_GET['newpass']) or die('{"code":2,"message":"'._('Make sure that everything is filled in. Try reloading if it is.').'"}');
			$u['password'] == mkpasswd($_GET['password'],$u['salt']) or die('{"code":2,"message":"'._('Invalid password').'"}');
			$u['api_key'] == $_COOKIE['key'] or die('{"code":3,"message":"Not Logged in to use '.$u['nick'].' with key '.$u['api_key'].' != '.$_COOKIE['key'].'."}');
			if($_COOKIE['type'] == 'user'){
				$res = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$u['nick'],$_GET['password'],'NickServ','set',Array('password',trim($_GET['newpass'])));
				if($res[0] === false){
					die('{"code":2,"message":"'._('Could not update password with nickserv').': '.$res[1].'"}');
				}else{
					$_SESSION['password'] = $_GET['newpass'];
				}
			}
			query("UPDATE users u SET u.password='%s' WHERE u.id=%d",Array(mkpasswd($_GET['newpass']),$u['id']));
			die('{"code":0}');
		break;
		case 'sync-pass':
			$u && isset($_SESSION['password'])or die('{"code":2,"message":"'._('Make sure that everything is filled in. Try reloading if it is.').'"}');
			$u['api_key'] == $_COOKIE['key'] or die('{"code":3,"message":"'._('Not Logged in to use').' '.$u['nick'].' '._('with key').' '.$u['api_key'].' != '.$_COOKIE['key'].'."}');
			$_COOKIE['type'] == 'user' or die('{"code":3,"message":"'._('Must be logged in with type user to sync pass').'"}');
			$res = atheme_login(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),$u['nick'],$_SESSION['password']);
			if($res[0] === false){
				die('{"code":2,"message":"'._('Could not verify with nickserv').': '.$res[1].'"}');
			}
			query("UPDATE users u SET u.password='%s' WHERE u.id=%d",Array(mkpasswd($_SESSION['password']),$u['id']));
			die('{"code":0,"message":"'._('Nickserv password synchronized with main account').'"}');
		break;
		case 'role':
			$u && isset($_GET['type']) or die('{"code":2,"message":"'._('Make sure that everything is filled in. Try reloading if it is.').'"}');
			setcookie('type',$_GET['type'],null,'/');
			die('{"code":0}');
		break;
		case 'user':
			$u or die('{"code":10,"message":"'._('Not logged in').'"}');
			isset($_GET['id']) or die('{"code":2,"message":"'._('No user set.').'"}');
			isset($_GET['email']) or die('{"code":2,"message":"'._('No email set.').'"}');
			isset($_GET['real_name']) or die('{"code":2,"message":"'._('No real name set.').'"}');
			isset($_GET['nick']) or die('{"code":2,"message":"'._('No nick set.').'"}');
			$user = get_user_from_id_obj($_GET['id']) or die('{"code":2,"message":"'._('User with id').' '.$_GET['id'].' '._('does not exist. You should reload the page.').'"}');
			if($u['id'] == $user['id']){
				setcookie('user',$_GET['nick'],null,'/');
			}
			query("UPDATE users u SET u.nick='%s', u.real_name='%s', u.email='%s' WHERE u.id=%d",Array($_GET['nick'],$_GET['real_name'],$_GET['email'],$_GET['id'])) or die('{"code":2,"message":"'._('Unable to update user').'"}');
			die(ircrehash());
		break;
		case 'oper':
			$u or die('{"code":10,"message":"'._('Not logged in').'"}');
			isset($_GET['id']) or die('{"code":2,"message":"'._('No user set.').'"}');
			isset($_GET['nick']) or die('{"code":2,"message":"'._('No nick set.').'"}');
			isset($_GET['swhois']) or die('{"code":2,"message":"'._('No profile set.').'"}');
			$oper = get_oper_from_id_obj($_GET['id']) or die('{"code":2,"message":"'._('Oper with id').' '.$_GET['id'].' '._('does not exist. You should reload the page.').'"}');
			if(isset($_GET['password']) && $_GET['password'] != ""){
				query("UPDATE opers o SET o.nick='%s', o.swhois='%s', o.password='%s', o.password_type_id=2 WHERE o.id=%d",Array($_GET['nick'],$_GET['swhois'],mkpasswd($_GET['password']),$_GET['id'])) or die('{"code":2,"message":"'._('Unable to update oper').'"}');
			}else{
				query("UPDATE opers o SET o.nick='%s', o.swhois='%s' WHERE o.id=%d",Array($_GET['nick'],$_GET['swhois'],$_GET['id'])) or die('{"code":2,"message":"'._('Unable to update oper').'"}');
			}
			die(ircrehash());
		break;
		case 'config':
			foreach($_GET as $key => $val){
				set_conf($key,$val,get_conf_type($key)) or die('{"code":1,"message":"'._('Failed to update setting').': '.$key.' ',_('with value').': '.$val.'"}');
			}
			die('{"code":0}');
		break;
		case 'rehash':
			$u or die('{"code":10,"message":"'._('Not logged in').'"}');
			die(ircrehash());
		break;
		default:
			die('{"code":1,"message":"'._('Invalid Action').': '.$_GET['action'].'"}');
	}
?>
