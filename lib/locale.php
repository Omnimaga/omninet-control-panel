<?php
	require_once(DIR.'/lib/gettext.inc');
	@$locales = explode(',',$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
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
	T_setlocale(LC_MESSAGES,LOCALE);
	T_bindtextdomain('omninet',DIR.'/lang');
	T_bind_textdomain_codeset('omninet','UTF-8');
	T_textdomain('omninet');
?>