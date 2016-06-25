<?php

namespace org\autoset\santizen\generator\php\santorini;

use org\autoset\santizen\util\PhpClassFileGenerator;
use org\autoset\santizen\util\StringHelper;

class SantoriniGenerator {

	public function start($outputDir, $namespace, $tableName, $schemes) {

		$config = new GeneratorConfig();
		$config->setOutputDir($outputDir);
		$config->setNamespace($namespace);
		$config->setTableName($tableName);
		$config->setSchemes($schemes);

		$package = new PreparePackageStructure($config);

		new VoClassGenerator($package);

		new DaoClassGenerator($package);

		
	}

}
