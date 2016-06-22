<?php

namespace org\autoset\santizen\action;

use org\autoset\santizen\util\ConfigUtil;

class HelpAction implements Action {

	public function setCommandArguments(array $args) {
	}

	public function run() {

		$this->displayVariables();

		echo PHP_EOL;

		$this->displayDatabaseSchemes();
	}

	public function displayVariables() {

		echo "santizen variables:".PHP_EOL;
		
		$varNames = ConfigUtil::getVarNames();
	
		foreach ($varNames as $varName) {
			echo "  - ".$varName.": ".ConfigUtil::getVar($varName).PHP_EOL;
		}
	}

	public function displayDatabaseSchemes() {

		echo "database schemes:".PHP_EOL;
		
		$tableNames = ConfigUtil::getDatabaseSchemes();
	
		foreach ($tableNames as $tableName) {
			echo "  - ".$tableName.PHP_EOL;
		}
	}
}
