<?php
	require_once(dirname(dirname(__FILE__))."/header.php");
	function channel_flag_name($flag){
		switch($flag){
			case 'v':$name=__('Voice');break;
			case 'V':$name=__('Automatic voice');break;
			case 'h':$name=__('Halfop');break;
			case 'H':$name=__('Automatic Halfop');break;
			case 'o':$name=__('Op');break;
			case 'O':$name=__('Automatic Op');break;
			case 'a':$name=__('Admin');break;
			case 'q':$name=__('Owner');break;
			case 's':$name=__('Set');break;
			case 'i':$name=__('Invite/Getkey');break;
			case 'r':$name=__('Kick/Ban');break;
			case 'R':$name=__('Recover/Clear');break;
			case 'f':$name=__('Modify access lists');break;
			case 't':$name=__('Topic');break;
			case 'A':$name=__('View access lists');break;
			case 'F':$name=__('Founder');break;
			case 'b':$name=__('Banned');break;
			default:$name=$flag;
		}
		return $name;
	}
	function channel_flag_obj($flag,$selected=false){
		$ret = array(
			'value'=>$flag,
			'label'=>channel_flag_name($flag)
		);
		if($selected){
			$ret['attributes'] = array(
				'selected'=>'selected'
			);
		}
		return $ret;
	}
	function sanitize_channel_flags($flags){
		$possible_flags = array('v','V','h','H','o','O','a','q','s','i','r','R','f','t','A','F','b');
		foreach($possible_flags as $k => $flag){
			if(!isset($flags[$flag])){
				$flags[$flag] = false;
			}else{
				if($flags[$flag] == 'on'){
					$flags[$flag] = true;
				}else{
					$flags[$flag] = false;
				}
			}
		}
		return $flags;
	}
?>