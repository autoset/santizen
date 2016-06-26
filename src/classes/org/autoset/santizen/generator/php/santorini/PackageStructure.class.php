<?php

namespace org\autoset\santizen\generator\php\santorini;


class PackageStructure {

	private $config = null;
	private $packageName = null;
	private $classSuffix = null;

	public function __construct(GeneratorConfig $config, $packageName, $classSuffix = '') {
		$this->config = $config;
		$this->packageName = $packageName;
		$this->classSuffix = $classSuffix;
	}

	public function getNamespace() {
		return $this->config->getNamespace($this->packageName);
	}

	public function getClassName() {
		return $this->config->getTableNameForClassName().$this->classSuffix;
	}

	public function getClassFullNamespace() {
		return $this->getNamespace()."\\".$this->getClassName();
	}

	public function getDescription() {
		return $this->config->getTableName().'의 '.$this->packageName.' 클래스';
	}

	public function getPath() {
		return $this->config->getOutputDir($this->packageName.DIRECTORY_SEPARATOR.$this->getClassName().'.class.php');
	}

}
