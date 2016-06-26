<?php

namespace org\autoset\santizen\generator\php\santorini;

use org\autoset\santizen\util\PhpClassFileGenerator;
use org\autoset\santizen\util\StringHelper;
use org\autoset\santizen\util\ConfigUtil;

class SantoriniGenerator {

	public function start($outputDir, $namespace, $tableName, $schemes) {

		$prefixUrl = ConfigUtil::getVar('SANTORINI.PREFIX_URL');

		$config = new GeneratorConfig();
		$config->setOutputDir($outputDir);
		$config->setNamespace($namespace);
		$config->setTableName($tableName);
		$config->setSchemes($schemes);
		$config->setPrefixUrl($prefixUrl);

		$package = new PreparePackageStructure($config);

		new VoClassGenerator($package);

		new DaoClassGenerator($package);
		
		new ServiceClassGenerator($package);

		new RestControllerClassGenerator($package);
	}

}
