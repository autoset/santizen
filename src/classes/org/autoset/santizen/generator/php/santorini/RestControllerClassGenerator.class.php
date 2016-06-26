<?php

namespace org\autoset\santizen\generator\php\santorini;

use org\autoset\santizen\util\PhpClassFileGenerator;
use org\autoset\santizen\util\StringHelper;

class RestControllerClassGenerator {

	private $phpFile = null;

	public function __construct(PreparePackageStructure $packageStructure) {

		$restControllerClass = $packageStructure->getRestControllerClass();
		$serviceClass = $packageStructure->getServiceClass();
		$voClass = $packageStructure->getVoClass();

		$this->phpFile = new PhpClassFileGenerator();
		
		$this->phpFile->setNamespace($restControllerClass->getNamespace());
		$this->phpFile->setClassName($restControllerClass->getClassName());
		$this->phpFile->setClassDescription($restControllerClass->getDescription());
		$this->phpFile->setExtendsClassName("Controller");

		$this->phpFile->addUseClass("Exception");
		$this->phpFile->addUseClass();
		$this->phpFile->addUseClass("org\\autoset\\santorini\\Controller");
		$this->phpFile->addUseClass();
		$this->phpFile->addUseClass("use org\\autoset\\santorini\\http\\HttpServletRequest");
		$this->phpFile->addUseClass("use org\\autoset\\santorini\\http\\HttpServletResponse");
		$this->phpFile->addUseClass("use org\\autoset\\santorini\\http\\HttpSession");
		$this->phpFile->addUseClass("use org\\autoset\\santorini\\util\\ModelMap");
		$this->phpFile->addUseClass("use org\\autoset\\santorini\\ModelAndView");
		$this->phpFile->addUseClass("use org\\autoset\\santorini\\ApplicationContext");
		$this->phpFile->addUseClass();
		$this->phpFile->addUseClass($serviceClass->getClassFullNamespace());
		$this->phpFile->addUseClass();
		$this->phpFile->addUseClass($voClass->getClassFullNamespace());

		$this->addProperty("sysPropService", "SysPropService");
		
		$mainServiceName = lcfirst($serviceClass->getClassName());
		$this->addProperty($mainServiceName, $serviceClass->getClassName());

		// init 메서드 추가
		$methodIdx = $this->phpFile->addMethod('init', 'public', false, '');
		$this->phpFile->setMethodDescription($methodIdx, '컨트롤러 초기화');
		$codes = array(
				'$this->sysPropService = ApplicationContext::getBean("sysPropService");',
				'',
				'$this->'.$mainServiceName.' = getClassNewInstance(new '.$serviceClass->getClassName().'());',
				'',
				'parent::init();'
			);		
		$this->phpFile->setMethodCode($methodIdx, implode(PHP_EOL, $codes));

		$this->phpFile->saveAs($restControllerClass->getPath());
		
	}

	private function addProperty($propName, $propClassName) {
		$propertyIdx = $this->phpFile->addProperty($propName, 'private', false, $propClassName, null);
		$this->phpFile->setPropertyDescription($propertyIdx, $propClassName);
	}
}
