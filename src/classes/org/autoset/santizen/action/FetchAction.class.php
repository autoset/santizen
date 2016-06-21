<?php

namespace org\autoset\santizen\action;

use org\autoset\santizen\util\ConfigUtil;
use org\autoset\santizen\util\DatabaseFactory;

class FetchAction implements Action {

	private $dbConn = null;

	private $tables = array();

	public function setCommandArguments(array $args) {
		$this->tables = $args;
	}

	public function run() {

		$this->initDatabase();
		
		foreach ($this->tables as $tableName) {
			echo ">".$tableName.PHP_EOL;
			var_dump($this->dbConn->getFields($tableName));
		}


	}

	private function initDatabase() {

		// FIXME: DB드라이버 인터페이스 정리 필요
		$dbType = ConfigUtil::getVar('DB.TYPE');
		$dbHost = ConfigUtil::getVar('DB.HOST');
		$dbName = ConfigUtil::getVar('DB.NAME');
		$dbUsername = ConfigUtil::getVar('DB.USERNAME');
		$dbPassword = ConfigUtil::getVar('DB.PASSWORD');

		$dbUrl = $dbType."://".$dbHost."/".$dbName;

		$this->dbConn = DatabaseFactory::getInstance($dbType);
		$this->dbConn->connect($dbUrl, $dbUsername, $dbPassword);

	}
}
