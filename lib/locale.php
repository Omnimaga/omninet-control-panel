<?php
	$locales = explode(',',$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
	$langs = scandir(DIR.'/lang');
	$found = false;
	foreach($locales as $k => $l){
		$locale = str_replace('-','_',$l);
		foreach($langs as $lang){
			if($lang != '.' && $lang != '..' && strtolower($lang) == strtolower($locale)){
				$locale = $lang;
				$found = true;
				break;
			}
		}
		if($found){
			break;
		}
		$locale = 'en';
	}
	define('LOCALE',$locale);
	bindtextdomain('omninet',DIR.'/lang/'.LOCALE);
	bind_textdomain_codeset('omninet','UTF-8');
	textdomain('omninet');
?>