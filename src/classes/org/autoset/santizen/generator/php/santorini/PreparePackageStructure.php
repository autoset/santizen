<?php

namespace org\autoset\santizen\generator\php\santorini;


class PreparePackageStructure {

	private $config = null;
	private $packages = array('controller', 'service', 'dao', 'vo');

	public function __construct(GeneratorConfig $config) {
		
		$this->config = $config;

		foreach ($this->packages as $packageName) {
			$path = $config->getOutputDir($packageName);
			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}
		}
	}

	public function getTableName() {
		return $this->config->getTableName();
	}

	public function getSchemes() {
		return $this->config->getSchemes();
	}

	public function getVoClass() {
		return new PackageStructure($this->config, 'vo', 'Entity');
	}

	public function getDaoClass() {
		return new PackageStructure($this->config, 'dao', 'Dao');
	}

	public function getServiceClass() {
		return new PackageStructure($this->config, 'service', 'Service');
	}

}
