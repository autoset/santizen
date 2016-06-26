<?php

namespace org\autoset\santizen\generator\php\santorini;

use org\autoset\santizen\util\StringHelper;

class GeneratorConfig {

	private $outputDir = null;
	private $setNamespace = null;
	private $tableName = null;
	private $setSchemes = null;

	public function setOutputDir($dir) {
		$this->outputDir = $dir;
	}

	public function setNamespace($namespace) {
		$this->namespace = $namespace;
	}

	public function setTableName($tableName) {
		$this->tableName = $tableName;
	}

	public function setSchemes($schemes) {
		$this->schemes = $schemes;
	}

	public function getOutputDir($suffix = '') {
		if ($suffix == '') {
			return $this->outputDir;
		} else {
			$tableDirName = StringHelper::underscore2camel($this->getTableName());
			return $this->outputDir.DIRECTORY_SEPARATOR.$tableDirName.DIRECTORY_SEPARATOR.$suffix;
		}
	}

	public function getNamespace($suffix = '') {
		if ($suffix == '') {
			return $this->namespace;
		} else {
			$tableDirName = StringHelper::underscore2camel($this->getTableName());
			return $this->namespace."\\".$tableDirName."\\".$suffix;
		}
	}

	public function getTableName() {
		return $this->tableName;
	}

	public function getTableNameForClassName() {
		return ucfirst(StringHelper::underscore2camel($this->tableName));
	}

	public function getSchemes() {
		return $this->schemes;
	}
}
