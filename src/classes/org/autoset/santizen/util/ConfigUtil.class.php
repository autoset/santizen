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

	public static function saveConfigFile($configData) {
		file_put_contents(self::$CONFIG_FILE, serialize($configData));
	}

	public static function getVarName($varName) {
		return strtoupper($varName);
	}

	public static function getTableName($tableName) {
		return strtoupper($tableName);
	}

	public static function setVar($varName, $varValue) {
		$configData = self::readConfigFile();
		$configData['variables'][self::getVarName($varName)] = $varValue;
		self::saveConfigFile($configData);
	}

	public static function getVar($varName) {
		$varName = self::getVarName($varName);
		$configData = self::readConfigFile();
		$configVars = array_key_exists('variables', $configData) ? $configData['variables'] : array();
		return array_key_exists($varName, $configVars) ? $configVars[$varName] : null;
	}

	public static function getVarNames() {
		$configData = self::readConfigFile();
		if (array_key_exists('variables', $configData))
			return array_keys($configData['variables']);
		else
			return array();
	}

	public static function setDatabaseScheme($tableName, $scheme) {
		$configData = self::readConfigFile();
		$configData['databaseScheme'][self::getTableName($tableName)] = $scheme;
		self::saveConfigFile($configData);
	}

	public static function getDatabaseScheme($tableName) {
		$tableName = self::getTableName($tableName);
		$configData = self::readConfigFile();
		$configVars = array_key_exists('databaseScheme', $configData) ? $configData['databaseScheme'] : array();
		return array_key_exists($tableName, $configVars) ? $configVars[$tableName] : null;
	}

	public static function getDatabaseSchemes() {
		$configData = self::readConfigFile();
		if (array_key_exists('databaseScheme', $configData))
			return array_keys($configData['databaseScheme']);
		else
			return array();
	}

	public static function cleanUp() {
		if (self::isExistsConfigFile())
			unlink(self::$CONFIG_FILE);
	}


}
