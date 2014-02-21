<?php
	require_once(dirname(dirname(__FILE__))."/header.php");
	function get_opers_for_current_user_obj(){
		global $user;
		return get_opers_for_user_obj($user['id']);
	}
	function get_opers_obj(){
		$opers = Array();
		$res = query("SELECT o.id FROM opers o");
		if($res && $res->num_rows != 0){
			while($oper = $res->fetch_assoc()){
				array_push($opers,get_oper_from_id_obj($oper['id']));
			}
		}
		return $opers;
	}
	function get_opers_for_user_obj($id){
		$opers = Array();
		$res = query("SELECT o.id FROM opers o WHERE o.manager_id = %d OR o.user_id = %d",Array($id,$id));
		if($res && $res->num_rows != 0){
			while($oper = $res->fetch_assoc()){
				array_push($opers,get_oper_from_id_obj($oper['id']));
			}
		}
		return $opers;
	}
	function get_oper_from_id_obj($id){
		$opers = Array();
		$res = query("SELECT o.id,o.nick,o.password,o.password_type,o.swhois,o.flags,o.user_id,o.manager_id FROM opers_v o WHERE o.id = %d",Array($id));
		if($res && $res->num_rows == 1){
			$oper = $res->fetch_assoc();
			$hosts = query("SELECT h.host FROM hosts h WHERE oper_id = %d",Array($oper['id']));
			if($hosts->num_rows != 0){
				$oper['hosts'] = Array();
				while($host = $hosts->fetch_assoc()){
					array_push($oper['hosts'],$host['host']);
				}
			}else{
				$oper['hosts'] = Array('*@*');
			}
			if(!isset($oper['user_id'])){
				$oper['user_id'] = '-';
			}
			return $oper;
		}
		return $opers;
	}
	function get_opers_html($opers){
		global $u;
		global $user;
		if(!isset($u)){
			$u = $user;
		}
		$ret = "";
		foreach($opers as $k => $oper){
			$ret .= "<h3>".($u['id'] != $oper['user_id']?_("Managed Oper").":":_("Personal Oper").":")."</h3>".get_form_html('oper-form-'.$oper['id'],Array(
				Array(
					'name'=>'nick',
					'label'=>_('Nick'),
					'type'=>'text',
					'value'=>$oper['nick']
				),
				Array(
					'name'=>'swhois',
					'label'=>_('Omnimaga Profile'),
					'type'=>'text',
					'value'=>$oper['swhois']
				),
				Array(
					'name'=>'password',
					'label'=>_('New Password'),
					'type'=>'password',
					'value'=>''
				),
				Array(
					'name'=>'id',
					'type'=>'hidden',
					'value'=>$oper['id']
				),
				Array(
					'name'=>'action',
					'type'=>'hidden',
					'value'=>'oper'
				)
			),'Save')."<hr/>";
		}
		return $ret;
	}
	function get_opers_for_server_obj($id){
		$opers = Array();
		$res = query("SELECT o.id,o.nick,o.password,o.password_type,o.swhois,o.flags,o.role FROM opers_v o WHERE o.server_id = %d OR o.server_id IS NULL",Array($id));
		if($res && $res->num_rows != 0){
		while($oper = $res->fetch_assoc()){
			$hosts = query("SELECT h.host FROM hosts h WHERE oper_id = %d",Array($oper['id']));
			if($hosts->num_rows != 0){
				$oper['hosts'] = Array();
				while($host = $hosts->fetch_assoc()){
					array_push($oper['hosts'],$host['host']);
				}
			}else{
				$oper['hosts'] = Array('*@*');
			}
				array_push($opers,$oper);
			}
		}
		return $opers;
	}
?>