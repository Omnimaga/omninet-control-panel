<?php
	$locales = explode(',',$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
	for($i=count($locales)-1;$i>=0;$i--){
		$locale = str_replace('-','_',$locales[$i]);
		if(is_dir(DIR.'/lang/'.$locale)){
			break;
		}else{
			$locale = 'en';
		}
	}
	define('LOCALE',$locale);
	bindtextdomain('omninet',DIR.'/lang/'.LOCALE);
	bind_textdomain_codeset('omninet','UTF-8');
	textdomain('omninet');
?>