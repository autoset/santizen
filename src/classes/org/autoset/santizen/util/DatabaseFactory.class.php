<?php

namespace org\autoset\santizen\util;

class DatabaseFactory {

	public static function getInstance($type) {

		try {
			$driverClassName = "org\\autoset\\santizen\\database\\".ucfirst($type)."Driver";
			$reflectionClass = new \ReflectionClass($driverClassName);
			$driverClass = $reflectionClass->newInstance();
		} catch (\ReflectionException $re) {
			echo "Unknown database type.";
			exit;
		}

		return $driverClass;
	}

}

