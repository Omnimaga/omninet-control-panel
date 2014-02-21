<?php
	require_once(dirname(dirname(__FILE__))."/header.php");
	function build_server_tree(&$server,$depth=-1,$curdepth=0){
		$server['children'] = Array();
		 if($depth == -1 || $depth != $curdepth){
			$children = query("SELECT s.id,s.description,s.host,s.name,s.ip,s.user_id,c.parent_id as parent FROM children_v c JOIN servers s ON c.child_id = s.id WHERE c.parent_id = %d",Array($server['id']));
			if($children && $children->num_rows != 0 ){
				while($row = $children->fetch_assoc()){
					array_push($server['children'],$row);
				}
			}
			foreach($server['children'] as $k => $child){
				build_server_tree($server['children'][$k],$depth,$curdepth+1);
			}
		}
		$parent = null;
		if(!is_null($server['parent']) && $curdepth == 0){
			$parent = query("SELECT s.id,s.description,s.host,s.name,s.user_id,s.ip FROM servers s WHERE s.id = '%s'",Array($server['parent']));
			if($parent && $parent->num_rows == 1){
				$parent = $parent->fetch_assoc();
				$parent['opers'] = get_opers_for_server_obj($parent['id']);
				$parent['children'] = Array();
			}else{
				$parent = null;
			}
		}
		$server['parent'] = $parent;
		$server['opers'] = get_opers_for_server_obj($server['id']);
		return $server;
	}
	function get_current_server_obj(){
		return get_server_obj($_GET['server']);
	}
	function get_servers_obj(){
		$servers = Array();
		$res = query("SELECT s.id FROM servers s");
		if($res && $res->num_rows != 0){
			while($server = $res->fetch_assoc()){
				array_push($servers,get_server_for_id_obj($server['id']));
			}
		}
		return $servers;
	}
	function get_ulines(){
		$ulines = Array();
		$res = query("SELECT u.host FROM ulines_v u");
		if($res && $res->num_rows != 0){
			while($server = $res->fetch_assoc()){
				array_push($ulines,$server['host']);
			}
		}
		return $ulines;
	}
	function get_ulines_obj(){
		$ulines = Array();
		$res = query("SELECT u.id FROM ulines_v u");
		if($res && $res->num_rows != 0){
			while($server = $res->fetch_assoc()){
				array_push($ulines,get_server_for_id_obj($server['id']));
			}
		}
		return $ulines;
	}
	function get_server_obj($name){
		global $user;
		$server = query("SELECT id,description,host,name,ip,user_id,parent_id as parent FROM servers s WHERE user_id = %d AND lower(name) = lower('%s')",Array($user['id'],$name));
		if($server && $server->num_rows == 1){
			$server = $server->fetch_assoc();
			build_server_tree($server);
			return $server;
		}
		return false;
	}
	function get_server_for_id_obj($id){
		$server = query("SELECT id,description,host,name,ip,user_id,parent_id as parent FROM servers s WHERE s.id = %d",Array($id));
		if($server && $server->num_rows == 1){
			$server = $server->fetch_assoc();
			build_server_tree($server);
			return $server;
		}
		return false;
	}
	function get_servers_for_user_obj($id){
		$res = query("SELECT id,description,host,name,ip,user_id,parent_id as parent FROM servers s WHERE user_id = %d",Array($id));
		$servers = Array();
		if($res && $res->num_rows != 0){
			while($server = $res->fetch_assoc()){
				build_server_tree($server);
				array_push($servers,$server);
			}
		}
		return $servers;
	}
	function get_servers_for_current_user_obj(){
		global $user;
		return get_servers_for_user_obj($user['id']);
	}
	function get_servers_list_html($servers,$depth=0){
		global $user;
		$r = '<div class="accordion">';
		foreach($servers as $k => $server){
			$r .= "<h3>{$server['name']} ({$server['host']}) - {$server['description']}</h3><div>";
			$suser = get_user_from_id_obj($server['user_id']);
			$r .= "<h4>"._('Owner').":</h4>{$suser['nick']}<ul>";
			$r .= "<li>"._('Real Name').": {$suser['real_name']}</li><li>"._('Email').": {$suser['email']}</li></ul>";
			$id = 0;
			if(count($server['opers']) > 0){
				$r .= "<h4>"._('Opers').":</h4><table class='tree'>";
				$id++;
				$pid = $id;
				$r .= "<tr style='font-weight:bold;' class='treegrid-".($id)."'><td>"._('Nick')."</td><td>"._('Role')."</td></tr>";
				foreach($server['opers'] as $kk => $oper){
					$id++;
					$r .= "<tr class='treegrid-{$id} treegrid-parent-{$pid}'><td>{$oper['nick']}</td><td>{$oper['role']}</td></tr>";
				}
				$r .= "</table>";
			}
			if(isset($server['parent'])){
				$r .= "<h4>"._('Parent')."</h4>";
				$r .= get_servers_list_html(Array($server['parent']),$depth+1);
			}
			if(count($server['children']) > 0){
				$r .= "<h4>"._('Children')."</h4>";
				$r .= get_servers_list_html($server['children'],$depth+1);
			}
			$r .="</div>";
		}
		return $r.'</div>';
	}
?>