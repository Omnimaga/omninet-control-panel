<?php
	$locale = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
	if(strpos(',',$locale) !== false){
		$locale = substr($locale,0,strpos(',',$locale));
	}
	$locale = strtolower($locale);
	die($locale);
	switch($locale){
		case 'en-ca':
			$lang = 'en_CA';
			setlocale(LC_ALL,'en_CA.UTF-8','en_CA','en','english');
		break;
		case 'en-us':
			$lang = 'en_US';
			setlocale(LC_ALL,'en_US.UTF-8','en_US','en','english');
		break;
		default:
			$lang = 'en';
			setlocale(LC_ALL,'en','english');
	}
	putenv("LC_ALL=".$lang);
	putenv("LANGUAGE=".$lang);
	bindtextdomain('omninet',DIR.'/lang');
	textdomain('omninet');
?>