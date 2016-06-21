<?php

namespace org\autoset\santizen\action;

use org\autoset\santizen\util\ConfigUtil;

class HelpAction implements Action {

	public function setCommandArguments(array $args) {
	}

	public function run() {

		echo "santizen variables:".PHP_EOL;
		
		$varNames = ConfigUtil::getVarNames();
	
		foreach ($varNames as $varName) {
			echo "  - ".$varName.": ".ConfigUtil::getVar($varName).PHP_EOL;
		}
	}
}
