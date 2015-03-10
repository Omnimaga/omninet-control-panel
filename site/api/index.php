<?php
	error_reporting(E_ERROR);
	header('Content-type: application/json');
	header('Access-Control-Allow-Origin: *');
	require_once("../../header.php");
	if(!isset($_GET['action'])){
		$opts = getopt('a:',array('action:'));
		$_GET['action'] = isset($opts['action'])?$opts['action']:(isset($opts['a'])?$opts['a']:'');
	}
	$u = is_logged_in();
	switch($_GET['action']){
		case 'test':
			//$u or die();
			//print_r(atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'topic','#omnimaga'));
			//echo mkpasswd('root');
		break;
		case 'lang':
			echo file_get_contents(DIR.'/lang/'.LOCALE.'/C/LC_MESSAGES/omninet.po');
			die();
		break;
		case 'login':
			isset($_GET['username']) && isset($_GET['password']) or die('{"code":2,"message":"'.__('Missing username and/or password').'"}');
			isset($_GET['type']) or die('{"code":2,"message":"'.__('Missing user type').'"}');
			$r = login($_GET['username'],$_GET['password'],$_GET['type']);
			if($r !== true){
				die('{"code":2,"message":'.json_encode($r).'}');
			}else{
				die('{"code":0}');
			}
		break;
		case 'verify':
			isset($_GET['token']) or die('{"code":1,"message":"'.__('No token set').'"}');
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
			$u or die('{"code":1,"message":"'.__('You have been logged out').'"}');
			$u['type'] = 'user' && isset($_COOKIE['user']) && isset($_SESSION['password']) or die('{"code":0}');
			$res = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'MemoServ','list');
			if($res[0]){
				$res = explode('&#10;',$res[1]);
				$memos = array();
				foreach($res as $k => $row){
					if($k != 0 && $k != 1){
						$row = preg_split('/^-\s/',$row);
						if(isset($row[1])){
							$row = explode(' ',$row[1]);
							$memo = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'MemoServ','read',array($row[0]));
							$memo = explode('&#10;',$memo[1]);
							array_push($memos,array(
								'id'=>$row[0],
								'from'=>$row[2],
								'date'=>array(
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
				die('{"code":1,"message":"'.__('Cannot fetch memos').'"}');
			}
		break;
		case 'get-news':
			$u or die('{"code":1,"message":"'.__('You have been logged out').'"}');
			$u['type'] = 'user' && isset($_COOKIE['user']) && isset($_SESSION['password']) or die('{"code":0}');
			$res = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'InfoServ','list');
			if($res[0]){
				$res = explode('&#10;',$res[1]);
				$news = array();
				foreach($res as $k => $row){
					if($k != count($res)-1){
						array_push($news,array(
							'id'=>preg_replace('/^(\d)+:.+$/i','\1',$row),
							'title'=>preg_replace('/^\d+: \[(.+)\] .+/i','\1',$row),
							'from'=>preg_replace('/^\d+: \[.+\] by (.+) at \d\d?:\d\d? on (\d\d)\/\d\d\/\d\d\d\d: .+/i','\1',$row),
							'date'=>array(
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
				die('{"code":1,"message":"'.__('Cannot fetch news').'"}');
			}
		break;
		case 'get-channels':
			$u or die('{"code":1,"message":"'.__('You have been logged out').'"}');
			$u['type'] = 'user' && isset($_COOKIE['user']) && isset($_SESSION['password']) or die('{"code":0}');
			$res = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'NickServ','listchans');
			if($res[0]){
				$res = explode('&#10;',$res[1]);
				$channels = array();
				foreach($res as $k => $row){
					if($k != count($res)-1){
						$flags_list = str_split(preg_replace('/^Access flag\(s\) \+(.+) in .+$/i','\1',$row));
						$name = preg_replace('/^Access flag\(s\) \+.+ in (.+)$/i','\1',$row);
						$chan = array(
							'name'=>$name
						);
						if(in_array('F',$flags_list)){
							$chan['candrop'] = true;
							$chan['canaccess'] = true;
						}
						if(in_array('f',$flags_list)){
							$chan['canaccess'] = true;
						}
						$res2 = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'ChanServ','flags',array($name));
						$users = array();
						if($res2[0]){
							$res2 = explode('&#10;',$res2[1]);
							foreach($res2 as $kk => $row2){
								if($kk > 1 && $kk < count($res2)-2){
									$user = array(
										'id'=>preg_replace('/^(\d+)\b.+$/','\1',$row2),
										'name'=>trim(preg_replace('/^\d+\s+(.+)\s+\+.+/','\1',$row2)),
										'flags'=>array()
									);
									$flags_list = str_split(preg_replace('/^\d+\s+.+\s+\+(.+)\s+\[.+/i','\1',$row2));
									foreach($flags_list as $kk => $flag){
										$name = channel_flag_name($flag);
										array_push($user['flags'],array(
											'flag'=>$flag,
											'name'=>$name
										));
									}
									array_push($users,$user);
								}
							}
						}
						$chan['users'] = $users;
						array_push($channels,$chan);
					}
				}
				die('{"code":0,"channels":'.json_encode($channels).'}');
			}else{
				die('{"code":1,"message":"'.__('Cannot fetch channels').'"}');
			}
		break;
		case 'send-memo':
			$u or die('{"code":1,"message":"'.__('You have been logged out').'"}');
			isset($_GET['to']) && isset($_GET['message']) or die('{"code":1,"message":"'.__('No message or user entered').'"}');
			$res = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'MemoServ','send',array($_GET['to'],$_GET['message']));
			if($res[0]){
				if(substr($res[1],-19) == ' is not registered.'){
					die('{"code":1,"message":"'.__('User').' '.$_GET['to'].' '.__('does not exist').'"}');
				}else{
					die('{"code":0,"message":"'.__('Memo Sent').'"}');
				}
			}else{
				die('{"code":1,"message":"'.__('Cannot send memo').': '.$res[1].'"}');
			}
		break;
		case 'delete-memo':
			$u or die('{"code":1,"message":"'.__('You have been logged out').'"}');
			isset($_GET['id']) or die('{"code":1,"message":"'.__('No id given').'"}');
			$res = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'MemoServ','delete',array($_GET['id']));
			if(!$res[0]){
				die('{"code":1,"message":"'.__('Cannot delete memo').': '.$res[1].'"}');
			}
			die('{"code":0}');
		break;
		case 'delete-channel':
			$u or die('{"code":1,"message":"'.__('You have been logged out').'"}');
			isset($_GET['channel']) or die('{"code":1,"message":"'.__('No channel given').'"}');
			$res = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'ChanServ','drop',array($_GET['channel']));
			if(!$res[0]){
				die('{"code":1,"message":"'.__('Cannot drop channel').': '.$res[1].'"}');
			}
			die('{"code":0}');
		break;
		case 'channel-flags':
			$u or die('{"code":1,"message":"'.__('You have been logged out').'"}');
			isset($_GET['channel']) or die('{"code":1,"message":"'.__('No channel given').'"}');
			isset($_GET['user']) or die('{"code":1,"message":"'.__('No user given').'"}');
			if(isset($_GET['flags'])){
				$flags = $_GET['flags'];
			}else{
				$flags = array();
			}
			$flags_on = '';
			$flags_off = '';
			$flags = sanitize_channel_flags($flags);
			foreach($flags as $flag => $val){
				if($val){
					$flags_on .= ' '.$flag;
				}else{
					$flags_off .= ' '.$flag;
				}
			}
			$res = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'ChanServ','flags',array($_GET['channel'],$_GET['user'],'+'.$flags_on));
			if(!$res[0] && $res[2] != 12){
				die('{"code":1,"message":"'.__('Cannot change flag').': '.$res[1].'"}');
			}
			$res = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'ChanServ','flags',array($_GET['channel'],$_GET['user'],'-'.$flags_off));
			if(!$res[0] && $res[2] != 12){
				die('{"code":1,"message":"'.__('Cannot change flag').': '.$res[1].'"}');
			}
			die('{"code":0,"flags":'.json_encode($flags).'}');
		break;
		case 'register-channel':
			$u or die('{"code":1,"message":"'.__('You have been logged out').'"}');
			isset($_GET['channel']) or die('{"code":1,"message":"'.__('No channel given').'"}');
			$channel = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$_COOKIE['user'],$_SESSION['password'],'ChanServ','info',Array($_GET['channel']));
			if(!$channel[0]){
				$ret = irccommands(array(
					'join '.$_GET['channel'],
					'samode '.$_GET['channel'].' +o RehashServ',
					'cs register '.$_GET['channel'],
					'cs set '.$_GET['channel'].' keeptopic on',
					'cs set '.$_GET['channel'].' founder '.$_COOKIE['user']
				));
				if($ret['code'] !== 0){
					die(json_encode($ret));
				}
				$ret2 = irccommands(array(
					'join '.$_GET['channel'],
					'cs set '.$_GET['channel'].' founder '.$_COOKIE['user'],
					'cs flags '.$_GET['channel'].' RehashServ -AORafhioqrstv'
				),$_COOKIE['user']);
				if($ret2['code'] !== 0){
					$ret2['message'] = 'Failed to register channel. See log for information.';
				}
				@$ret2['log'] = $ret['log']."\r\n".$ret2['log'];
			}else{
				$ret = array(
					'code'=>1,
					'message'=>'Channel '.$_GET['channel'].' already exists.'
				);
			}
			die(json_encode($ret));
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
					die('{"code":1,"message":"'.__('Failed to add email').' '.$result->email.' '.__('to user').' '.$u['nick'].'"}');
				}elseif(!$register && !$u = get_user_for_email($result->email)){
					die('{"code":1,"message":"'.__('Email does not match any users').'"}');
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
			$u or die('{"code":1,"message":"'.__('You have been logged out').'"}');
			isset($_GET['id']) or die('{"code":1,"message":"'.__('No ID set').'"}');
			if(!remove_email($u['id'],$_GET['id'],true)){
				die('{"code":1,"message":"'.__('Could not remove email address').'"}');
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
			$u or die('{"code":1,"message":"'.__('You have been logged out').'"}');
			$r = delete_token($u['id']);
			if($r !== true){
				die('{"code":1,"message":"'.$r.'"}');
			}
			die('{"code":0,"message":"'.__('2-factor disabled.').'"}');
		break;
		case 'ping':
			$u or die('{"code":1,"message":"'.__('You have been logged out').'"}');
			die('{"code":0}');
		break;
		case 'newpass':
			$u && isset($_GET['password']) && isset($_GET['newpass']) or die('{"code":2,"message":"'.__('Make sure that everything is filled in. Try reloading if it is.').'"}');
			$u['password'] == mkpasswd($_GET['password'],$u['salt']) or die('{"code":2,"message":"'.__('Invalid password').'"}');
			$u['api_key'] == $_COOKIE['key'] or die('{"code":3,"message":"Not Logged in to use '.$u['nick'].' with key '.$u['api_key'].' != '.$_COOKIE['key'].'."}');
			if($_COOKIE['type'] == 'user'){
				$res = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,$u['nick'],$_GET['password'],'NickServ','set',array('password',trim($_GET['newpass'])));
				if($res[0] === false){
					die('{"code":2,"message":"'.__('Could not update password with nickserv').': '.$res[1].'"}');
				}else{
					$_SESSION['password'] = $_GET['newpass'];
				}
			}
			query("UPDATE users u SET u.password='%s' WHERE u.id=%d",array(mkpasswd($_GET['newpass']),$u['id']));
			die('{"code":0}');
		break;
		case 'sync-pass':
			$u && isset($_SESSION['password'])or die('{"code":2,"message":"'.__('Not logged in').'"}');
			$u['api_key'] == $_COOKIE['key'] or die('{"code":3,"message":"'.__('Not Logged in to use').' '.$u['nick'].' '.__('with key').' '.$u['api_key'].' != '.$_COOKIE['key'].'."}');
			$_COOKIE['type'] == 'user' or die('{"code":3,"message":"'.__('Must be logged in with type user to sync pass').'"}');
			$res = atheme_login(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),$u['nick'],$_SESSION['password']);
			if($res[0] === false){
				die('{"code":2,"message":"'.__('Could not verify with nickserv').': '.$res[1].'"}');
			}
			query("UPDATE users u SET u.password='%s' WHERE u.id=%d",array(mkpasswd($_SESSION['password']),$u['id']));
			die('{"code":0,"message":"'.__('Nickserv password synchronized with main account').'"}');
		break;
		case 'sync-groups':
			$u && isset($_SESSION['password'])or die('{"code":2,"message":"'.__('Make sure that everything is filled in. Try reloading if it is.').'"}');
			$u['api_key'] == $_COOKIE['key'] or die('{"code":3,"message":"'.__('Not Logged in to use').' '.$u['nick'].' '.__('with key').' '.$u['api_key'].' != '.$_COOKIE['key'].'."}');
			$res = atheme_login(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),$u['nick'],$_SESSION['password']);
			if($res[0] === false){
				die('{"code":2,"message":"'.__('Could not verify with nickserv').': '.$res[1].'"}');
			}
			require_once(get_conf('smf-path').'/SSI.php');
			$res = $smcFunc['db_query']('',"SELECT id_group,additional_groups
				FROM {db_prefix}members
				WHERE id_member = {int:id_member}
			",array(
				'id_member'=>$user_info['id']
			));
			while($row = $smcFunc['db_fetch_assoc']($res)){
				$groups = explode(',',$row['additional_groups']);
				if(!is_null($row['id_group'])){
					array_push($groups,$row['id_group']);
				}
				foreach($groups as $k => $group){
					$res2 = query("SELECT irc_name FROM smf_groups WHERE id_group = %d",array($group));
					if($res2 && $res2->num_rows > 0){
						$res2 = $res2->fetch_assoc();
						$res3 = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,'RehashServ',get_conf('rehash-pass'),'GroupServ','flags',array('!'.$res2['irc_name'],$_COOKIE['user'],'+cvi'));
						if(!$res3[0]){
							die('{"code":'.$res3[2].',"message":"'.__('Unable to sync groups').': '.$res3[1].'"}');
						}
					}
				}
				$res3 = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,'RehashServ',get_conf('rehash-pass'),'GroupServ','flags',array('!Members',$_COOKIE['user'],'+cvi'));
				if(!$res3[0]){
					die('{"code":'.$res3[2].',"message":"'.__('Unable to sync groups').': '.$res3[1].'"}');
				}
			}
			$res3 = atheme_command(get_conf('xmlrpc-server'),get_conf('xmlrpc-port'),get_conf('xmlrpc-path'),USER_IP,'RehashServ',get_conf('rehash-pass'),'NickServ','hold',array($_COOKIE['user'],'on'));
			if(!$res3[0]){
				die('{"code":'.$res3[2].',"message":"'.__('Unable to sync groups').': '.$res3[1].'"}');
			}
			die('{"code":0,"message":"'.__('Groups synced with SMF').'"}');
		break;
		case 'role':
			$u && isset($_GET['type']) or die('{"code":2,"message":"'.__('Not logged in').'"}');
			setcookie('type',$_GET['type'],null,'/');
			die('{"code":0}');
		break;
		case 'user':
			$u or die('{"code":10,"message":"'.__('Not logged in').'"}');
			isset($_GET['id']) or die('{"code":2,"message":"'.__('No user set.').'"}');
			isset($_GET['email']) or die('{"code":2,"message":"'.__('No email set.').'"}');
			isset($_GET['real_name']) or die('{"code":2,"message":"'.__('No real name set.').'"}');
			isset($_GET['nick']) or die('{"code":2,"message":"'.__('No nick set.').'"}');
			$user = get_user_from_id_obj($_GET['id']) or die('{"code":2,"message":"'.__('User with id').' '.$_GET['id'].' '.__('does not exist. You should reload the page.').'"}');
			if($u['id'] == $user['id']){
				setcookie('user',$_GET['nick'],null,'/');
			}
			query("UPDATE users u SET u.nick='%s', u.real_name='%s', u.email='%s' WHERE u.id=%d",array($_GET['nick'],$_GET['real_name'],$_GET['email'],$_GET['id'])) or die('{"code":2,"message":"'.__('Unable to update user').'"}');
			die(ircrehash());
		break;
		case 'oper':
			$u or die('{"code":10,"message":"'.__('Not logged in').'"}');
			isset($_GET['id']) or die('{"code":2,"message":"'.__('No user set.').'"}');
			isset($_GET['nick']) or die('{"code":2,"message":"'.__('No nick set.').'"}');
			isset($_GET['swhois']) or die('{"code":2,"message":"'.__('No profile set.').'"}');
			$oper = get_oper_from_id_obj($_GET['id']) or die('{"code":2,"message":"'.__('Oper with id').' '.$_GET['id'].' '.__('does not exist. You should reload the page.').'"}');
			if(isset($_GET['password']) && $_GET['password'] != ""){
				query("UPDATE opers o SET o.nick='%s', o.swhois='%s', o.password='%s', o.password_type_id=2 WHERE o.id=%d",array($_GET['nick'],$_GET['swhois'],mkpasswd($_GET['password']),$_GET['id'])) or die('{"code":2,"message":"'.__('Unable to update oper').'"}');
			}else{
				query("UPDATE opers o SET o.nick='%s', o.swhois='%s' WHERE o.id=%d",array($_GET['nick'],$_GET['swhois'],$_GET['id'])) or die('{"code":2,"message":"'.__('Unable to update oper').'"}');
			}
			die(ircrehash());
		break;
		case 'config':
			foreach($_GET as $key => $val){
				set_conf($key,$val,get_conf_type($key)) or die('{"code":1,"message":"'.__('Failed to update setting').': '.$key.' '.__('with value').': '.$val.'"}');
			}
			die('{"code":0}');
		break;
		case 'rehash':
			$u or die('{"code":10,"message":"'.__('Not logged in').'"}');
			die(ircrehash());
		break;
		default:
			die('{"code":1,"message":"'.__('Invalid Action').': '.$_GET['action'].'"}');
	}
?>
