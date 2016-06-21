<?php

define('SANTIZEN_ROOT',			dirname(__FILE__));
define('SANTIZEN_CLASS_ROOT',	SANTIZEN_ROOT.'/classes');

set_time_limit(0);
ini_set("memory_limit", "1024M");
ini_set("safe_mode", "0");

set_exception_handler('santizen_exception_handler');
spl_autoload_register('santizen_autoload');

function santizen_exception_handler($ex) {
	var_dump($ex);	
}

function santizen_autoload($className) {
	$arrClasses = array(
				SANTIZEN_CLASS_ROOT.'/'.str_replace('\\','/', $className).'.class.php',
				SANTIZEN_CLASS_ROOT.'/'.str_replace('\\','/', $className).'.php' );

	foreach ($arrClasses as $classPath) {
		if (file_exists($classPath)) {
			include_once($classPath);
			return ;
		}
	}
}

new org\autoset\santizen\SantizenMain();
