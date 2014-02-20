<?php
	require_once(dirname(dirname(__FILE__))."/header.php");
	function get_conf($key){
		$res = query("SELECT c.value FROM configuration c WHERE c.key = '%s'",array($key));
		if(!$res || $res->num_rows != 1 ){
			return false;
		}
		$res = $res->fetch_assoc();
		return $res['value'];
	}
	function set_conf($key,$val){
		return query("UPDATE configuration c SET c.value = '%s' WHERE c.key = '%s'",array($val,$key));
	}
	function get_conf_type($key){
		$res = query("SELECT c.type FROM configuration c WHERE c.key = '%s'",array($key));
		if($res && $row = $res->fetch_assoc()){
			return $row['type'];
		}
		return 'string';
	}
	function get_conf_values($key,$labels=false){
		$ret = array();
		switch(get_conf_type($key)){
			case 'list':
				$res = query("SELECT cl.value,cl.label FROM configuration_lists cl WHERE cl.key='%s'",array($key));
				if($res){
					while($row = $res->fetch_assoc()){
						if(!$labels){
							$row = $row['value'];
						}
						array_push($ret,$row);
					}
				}else{
					array_push($ret,get_conf($key));
				}
			break;
			case 'lookup':
				$res = query("SELECT cl.table,cl.column,cl.label_column,cl.enabled_column FROM configuration_lookups cl WHERE cl.key='%s'",array($key));
				if($res && $res->num_rows == 1){
					$lookup = $res->fetch_assoc();
					if(isset($lookup['enabled_column']) && !is_null($lookup['enabled_column']) && $lookup['enabled_column'] != ''){
						$eq = 1;
						if(substr($lookup['enabled_column'],0,1) == '!'){
							$eq = 0;
							$lookup['enabled_column'] = substr($lookup['enabled_column'],1);
						}
						$res = query("SELECT t.%s AS value, t.%s AS label FROM %s t WHERE t.%s = {$eq}",array($lookup['column'],$lookup['label_column'],$lookup['table'],$lookup['enabled_column']));
					}else{
						$res = query("SELECT t.%s AS value, t.%s AS label FROM %s t",array($lookup['column'],$lookup['label_column'],$lookup['table']));
					}
					if($res){
						while($row = $res->fetch_assoc()){
							if(!$labels){
								$row = $row['value'];
							}
							array_push($ret,$row);
						}
					}else{
						array_push($ret,get_conf($key));
					}
				}else{
					array_push($ret,get_conf($key));
				}
			break;
		}
		return $ret;
	}
	function get_conf_list(){
		$conf = array();
		$res = query("SELECT c.key,c.description,c.value,c.type FROM configuration c");
		if($res){
			while($row = $res->fetch_assoc()){
				$item = array(
					'key'=>$row['key'],
					'type'=>get_conf_type($row['key']),
					'label'=>isset($row['description'])?$row['description']:''
				);
				$item['value'] = $row['value'];
				if(!isset($item['value'])){
					$item['value'] = '';
				}
				if(isset($item['type'])){
					switch($item['type']){
						case 'list':case 'lookup':
							$item['type'] = 'select';
							$values = get_conf_values($item['key'],true);
							$item['values'] = array();
							foreach($values as $value){
								if(isset($item['value']) && $value['value'] == $item['value']){
									$value['attributes'] = array(
										'selected'=>'selected'
									);
								}
								array_push($item['values'],$value);
							}
						break;
					}
					array_push($conf,$item);
				}
			}
		}
		return $conf;
	}
	function render_configuration_table(){
		$items = array(
			array(
				'name'=>'action',
				'type'=>'hidden',
				'value'=>'config'
			)
		);
		$config = get_conf_list();
		foreach($config as $k => $conf){
			switch($conf['type']){
				case 'select':
					$item = array(
						'name'=>$conf['key'],
						'values'=>$conf['values'],
						'label'=>$conf['label'],
						'type'=>'select'
					);
				break;
				default:
					$item = array(
						'name'=>$conf['key'],
						'value'=>$conf['value'],
						'label'=>$conf['label'],
						'type'=>$conf['type']
					);
			}
			array_push($items,$item);
		}
		return get_form_html('configuration',$items,'Save');
	}
?>