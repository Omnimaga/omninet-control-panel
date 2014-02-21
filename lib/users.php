<?php
	require_once(dirname(dirname(__FILE__))."/header.php");
	function add_email($id,$email){
		if(!in_array($email,get_emails($id))){
			$res=query("INSERT INTO emails (user_id,email) VALUES (%d,'%s')",array($id,$email));
			if(!$res){
				return false;
			}
		}
		return true;
	}
	function remove_email($user_id,$email,$is_id=false){
		if($is_id){
			if($res = query("DELETE FROM emails WHERE user_id = %d AND id = %d",array($user_id,$email))){
				return true;
			}
		}else{
			if($res = query("DELETE FROM emails WHERE user_id = %d AND email = '%s'",array($user_id,$email))){
				return true;
			}
		}
		return false;
	}
	function get_emails($id,$include_ids=false){
		$emails = array();
		if($res = query("SELECT e.email,e.id FROM emails e WHERE e.user_id = %d",array($id))){
			while($email = $res->fetch_assoc()){
				if($include_ids){
					array_push($emails,$email);
				}else{
					array_push($emails,$email['email']);
				}
			}
		}
		return $emails;
	}
	function get_user_types($id){
		$types = array();
		if($res = query("SELECT t.name FROM user_roles r JOIN user_role_types t ON t.id = r.user_role_id WHERE r.user_id = %d GROUP BY r.user_role_id",array($id))){
			while($type = $res->fetch_assoc()){
				array_push($types,$type['name']);
			}
		}
		array_push($types,'user');
		return $types;
	}
	function get_user_for_email($email){
		if($res = query("SELECT u.id FROM users u JOIN emails e ON e.user_id = u.id WHERE lower(e.email) = '%s'",Array($email))){
			if($res->num_rows == 1){
				$res = $res->fetch_assoc();
				return get_user_from_id_obj($res['id']);
			}
		}
		return false;
	}
	function get_current_user_obj($type){
		$user = get_user_obj($_GET['user'],$type);
		if($user && $user['api_key'] == $_GET['key']){
			return $user;
		}
		return false;
	}
	function get_user_obj($nick,$type){
		if($type == 'user' && isset($_SESSION['key']) && isset($_SESSION['password'])){
			$user = Array(
				'api_key'=>$_SESSION['key'],
				'nick'=>$nick,
				'password'=>$_SESSION['password'],
				'flags'=>'u',
				'id'=>'0',
				'email'=>$_SESSION['email'],
				'real_name'=>$_SESSION['real_name']
			);
			if($res = query("SELECT u.api_key,u.id,u.nick,u.real_name,u.email,u.password FROM users u WHERE lower(u.nick) = lower('%s')",Array($nick))){
				if($res->num_rows == 1){
					$res = $res->fetch_assoc();
					foreach($res as $k => $attr){
						if($k !== 'flags'){
							$user[$k] = $attr;
						}
					}
					$user['salt'] = substr($user['password'],1,strpos($user['password'],'$',1)-1);
				}
			}
			return $user;
		}else{
			$user = query("SELECT u.api_key,u.id,u.nick,u.real_name,u.email,u.password,t.name AS type,t.flags AS flags,u.secret_key FROM ircd.users u JOIN ircd.user_roles r ON u.id = r.user_id JOIN ircd.user_role_types t ON r.user_role_id = t.id WHERE u.nick = '%s' AND t.name = '%s';",Array($nick,$type));
			if($user && $user->num_rows == 1){
				$user = $user->fetch_assoc();
				$user['salt'] = substr($user['password'],1,strpos($user['password'],'$',1)-1);
				return $user;
			}
		}
		return false;
	}
	function get_user_nick($id){
		$user = get_user_from_id_obj($id);
		return $user['nick'];
	}
	function get_user_from_id_obj($id){
		if($id === 0 && isset($_SESSION['key']) && isset($_SESSION['password'])){
			$user = Array(
				'api_key'=>$_SESSION['key'],
				'nick'=>$_COOKIE['username'],
				'password'=>$_SESSION['password'],
				'flags'=>'u',
				'id'=>'0',
				'email'=>$_SESSION['email'],
				'real_name'=>$_SESSION['real_name']
			);
			if($res = query("SELECT u.api_key,u.id,u.nick,u.real_name,u.email,u.password FROM users u WHERE lower(u.nick) = lower('%s')",Array($nick))){
				if($res->num_rows == 1){
					$res = $res->fetch_assoc();
					foreach($res as $k => $attr){
						if($k !== 'flags'){
							$user[$k] = $attr;
						}
					}
					$user['salt'] = substr($user['password'],1,strpos($user['password'],'$',1)-1);
				}
			}
			return $user;
		}else{
			$user = query("SELECT u.api_key,u.id,u.nick,u.real_name,u.email,u.password,u.secret_key FROM ircd.users u where id = %d;",Array($id));
			if($user && $user->num_rows == 1){
				$user = $user->fetch_assoc();
				$user['salt'] = substr($user['password'],1,strpos($user['password'],'$',1)-1);
				return $user;
			}
		}
		return false;
	}
	function get_user_html($user){
		return get_form_html('user-form-'.$user['id'],Array(
			Array(
				'name'=>'real_name',
				'label'=>__('Real Name'),
				'type'=>'text',
				'value'=>$user['real_name']
			),
			Array(
				'name'=>'nick',
				'label'=>__('Nick'),
				'type'=>'text',
				'value'=>$user['nick']
			),
			Array(
				'name'=>'email',
				'label'=>__('Email'),
				'type'=>'text',
				'value'=>$user['email']
			),
			Array(
				'name'=>'id',
				'type'=>'hidden',
				'value'=>$user['id']
			),
			Array(
				'name'=>'action',
				'type'=>'hidden',
				'value'=>'user'
			)
		),__('Save'));
	}
	function has_flag($user,$flag){
		return strpos($user['flags'],$flag)!==false;
	}
?>