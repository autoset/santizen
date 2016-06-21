<?php

namespace org\autoset\santizen\util;

class ConfigUtil {

	public static $CONFIG_FILE = SANTIZEN_ROOT.DIRECTORY_SEPARATOR.".config";

	public static function isExistsConfigFile() {
		return file_exists(self::$CONFIG_FILE);
	}

	public static function readConfigFile() {
		if (self::isExistsConfigFile()) {
			return unserialize(file_get_contents(self::$CONFIG_FILE));
		} else {
			return array();
		}
	}

	public static function saveConfigFile($configVars) {
		file_put_contents(self::$CONFIG_FILE, serialize($configVars));
	}

	public static function getVarName($varName) {
		return strtoupper($varName);
	}

	public static function setVar($varName, $varValue) {
		$configVars = self::readConfigFile();
		$configVars[self::getVarName($varName)] = $varValue;
		self::saveConfigFile($configVars);
	}

	public static function getVar($varName) {
		$varName = self::getVarName($varName);
		$configVars = self::readConfigFile();
		return array_key_exists($varName, $configVars) ? $configVars[$varName] : null;
	}

	public static function getVarNames() {
		return array_keys(self::readConfigFile());
	}

	public static function cleanUp() {
		if (self::isExistsConfigFile())
			unlink(self::$CONFIG_FILE);
	}


}
