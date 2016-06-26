<?php

namespace org\autoset\santizen\generator\php\santorini;

use org\autoset\santizen\util\PhpClassFileGenerator;
use org\autoset\santizen\util\StringHelper;

class VoClassGenerator {

	private $phpFile = null;

	public function __construct(PreparePackageStructure $packageStructure) {

		$voClass = $packageStructure->getVoClass();
		$schemes = $packageStructure->getSchemes();

		$this->phpFile = new PhpClassFileGenerator();
		
		$this->phpFile->setNamespace($voClass->getNamespace());
		$this->phpFile->setClassName($voClass->getClassName());
		$this->phpFile->setClassDescription($voClass->getDescription());

		foreach ($schemes as $scheme) {

			$propertyName = StringHelper::underscore2camel($scheme['name']);

			$this->addProperty($propertyName, $scheme);

			$this->addGetterMethod($propertyName, $scheme);

			$this->addSetterMethod($propertyName, $scheme);
		}

		$this->phpFile->saveAs($voClass->getPath());

	}

	private function addProperty($propertyName, $scheme) {
		// property
		$propertyIdx = $this->phpFile->addProperty($propertyName, 'private', false, $scheme['type'], null);
		$this->phpFile->setPropertyDescription($propertyIdx, $scheme['name'].' 컬럼');
	}

	private function addGetterMethod($propertyName, $scheme) {
		// getter
		$methodName = StringHelper::underscore2camel($scheme['name']);
		$methodIdx = $this->phpFile->addMethod('get'.ucfirst($methodName), 'public', false, $scheme['type']);
		$this->phpFile->setMethodCode($methodIdx, 'return $this->'.$propertyName.';');
	}

	private function addSetterMethod($propertyName, $scheme) {
		// setter
		$methodName = StringHelper::underscore2camel($scheme['name']);
		$methodIdx = $this->phpFile->addMethod('set'.ucfirst($methodName), 'public', false);
		$this->phpFile->setMethodArguments($methodIdx, array("\$".StringHelper::underscore2camel($scheme['name'])));
		$this->phpFile->setMethodCode($methodIdx, '$this->'.$propertyName.' = $'.$propertyName.';');
	}
}
