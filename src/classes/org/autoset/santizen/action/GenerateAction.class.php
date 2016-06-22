<?php

namespace org\autoset\santizen\action;

use org\autoset\santizen\util\ConfigUtil;
use org\autoset\santizen\util\DatabaseFactory;
use org\autoset\santizen\util\GeneratorFactory;

class GenerateAction implements Action {

	public function setCommandArguments(array $args) {
	}

	public function run() {

		$namespace = ConfigUtil::getVar('PHP.NAMESPACE');

		if (empty($namespace)) {
			$namespace = 'example';
		} else {
			$namespace = str_replace('.', "\\", $namespace);
		}

		$generator = $this->getGenerator();
		
		$tableNames = ConfigUtil::getDatabaseSchemes();

		foreach ($tableNames as $tableName) {
			$generator->start(ConfigUtil::getVar('output.dir'), $namespace, $tableName, ConfigUtil::getDatabaseScheme($tableName) );
		}

	}

	public function getGenerator() {
		$language = ConfigUtil::getVar('LANG');
		$framework = ConfigUtil::getVar('FRAMEWORK');
		return GeneratorFactory::getInstance($language, $framework);
	}

}
