<?php

namespace org\autoset\santizen\generator\php\santorini;

use org\autoset\santizen\util\PhpClassFileGenerator;
use org\autoset\santizen\util\StringHelper;

class RestControllerClassGenerator {

	private $phpFile = null;
	
	private $tableName = null;
	private $tableNameToCamel = null;

	public function __construct(PreparePackageStructure $packageStructure) {

		$restControllerClass = $packageStructure->getRestControllerClass();
		$serviceClass = $packageStructure->getServiceClass();
		$voClass = $packageStructure->getVoClass();

		$this->tableName = $packageStructure->getTableName();
		$this->tableNameToCamel = ucfirst(StringHelper::underscore2camel($this->tableName));

		$this->phpFile = new PhpClassFileGenerator();
		
		$this->phpFile->setNamespace($restControllerClass->getNamespace());
		$this->phpFile->setClassName($restControllerClass->getClassName());
		$this->phpFile->setClassDescription($restControllerClass->getDescription());
		$this->phpFile->setExtendsClassName("Controller");

		$this->phpFile->addUseClass("Exception");
		$this->phpFile->addUseClass();
		$this->phpFile->addUseClass("org\\autoset\\santorini\\Controller");
		$this->phpFile->addUseClass();
		$this->phpFile->addUseClass("org\\autoset\\santorini\\http\\HttpServletRequest");
		$this->phpFile->addUseClass("org\\autoset\\santorini\\http\\HttpServletResponse");
		$this->phpFile->addUseClass("org\\autoset\\santorini\\http\\HttpSession");
		$this->phpFile->addUseClass("org\\autoset\\santorini\\util\\ModelMap");
		$this->phpFile->addUseClass("org\\autoset\\santorini\\ModelAndView");
		$this->phpFile->addUseClass("org\\autoset\\santorini\\ApplicationContext");
		$this->phpFile->addUseClass();
		$this->phpFile->addUseClass($serviceClass->getClassFullNamespace());
		$this->phpFile->addUseClass();
		$this->phpFile->addUseClass($voClass->getClassFullNamespace());

		$this->addProperty("sysPropService", "SysPropService");
		
		$this->addProperty(lcfirst($serviceClass->getClassName()), $serviceClass->getClassName());

		$this->addInitMethod($serviceClass);

		$tableNameToUrl = str_replace('_','-',strtolower($this->tableName));

		$this->addMethod(	"get".$this->tableNameToCamel."s"
							, "retrieveList"
							, $packageStructure->getConfig()->getPrefixUrl()."/".$tableNameToUrl."s"
							, "GET"
							, "목록 조회"
							, true);

		$this->phpFile->saveAs($restControllerClass->getPath());
		
	}

	private function addProperty($propName, $propClassName) {
		$propertyIdx = $this->phpFile->addProperty($propName, 'private', false, $propClassName, null);
		$this->phpFile->setPropertyDescription($propertyIdx, $propClassName);
	}

	private function addInitMethod($serviceClass) {
		// init 메서드 추가
		$methodIdx = $this->phpFile->addMethod('init', 'public', false, '');
		$this->phpFile->setMethodDescription($methodIdx, '컨트롤러 초기화');
		$codes = array(
				'$this->sysPropService = ApplicationContext::getBean("sysPropService");',
				'',
				'$this->'.lcfirst($serviceClass->getClassName()).' = getClassNewInstance(new '.$serviceClass->getClassName().'());',
				'',
				'parent::init();'
			);		
		$this->phpFile->setMethodCode($methodIdx, implode(PHP_EOL, $codes));
	}

	private function addMethod($methodPrefix, $serviceMethodPrefix, $url, $httpdMethod, $title, $returnSyntax) {

		$methodName = $methodPrefix;
		$methodIdx = $this->phpFile->addMethod($methodName, 'public', false, 'ModelAndView');
		
		$this->phpFile->setMethodDescription($methodIdx, $this->tableName.' 테이블 '.$title);
		$this->phpFile->setMethodRemarks($methodIdx, "@PagePolicy(requiredLogin=false)\n@RequestMapping(value='".$url."',method='".$httpdMethod."')");

		$this->phpFile->setMethodArguments($methodIdx, array('HttpServletRequest $request', 'HttpServletResponse $response'));

		$codes = array();


		$this->phpFile->setMethodCode($methodIdx, implode(PHP_EOL, $codes));
	}
}
