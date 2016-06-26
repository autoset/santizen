<?php

namespace org\autoset\santizen\generator\php\santorini;

use org\autoset\santizen\util\PhpClassFileGenerator;
use org\autoset\santizen\util\StringHelper;

class ServiceClassGenerator {

	private $phpFile = null;

	private $tableName = null;
	private $tableNameToCamel = null;
	private $daoInstanceName = null;
	private $voClassName = null;

	public function __construct(PreparePackageStructure $packageStructure) {
		
		$voClass = $packageStructure->getVoClass();
		$daoClass = $packageStructure->getDaoClass();
		$serviceClass = $packageStructure->getServiceClass();

		$this->voClassName = $voClass->getClassName();
		$this->tableName = $packageStructure->getTableName();
		$this->tableNameToCamel = ucfirst(StringHelper::underscore2camel($this->tableName));

		$this->phpFile = new PhpClassFileGenerator();
		
		$this->phpFile->setNamespace($serviceClass->getNamespace());
		$this->phpFile->setClassName($serviceClass->getClassName());
		$this->phpFile->setClassDescription($serviceClass->getDescription());
		$this->phpFile->addUseClass($daoClass->getClassFullNamespace());

		$this->addDaoProperty($daoClass);

		$this->addMethod("retrieveList", "selectList", "목록 조회", true);
		$this->addMethod("retrieveCount", "selectCountUser", "총 건 수 조회", true);
		$this->addMethod("retrieve", "select", "단 건 조회", true);
		$this->addMethod("register", "insert", "단 건 등록", true);
		$this->addMethod("modify", "update", "단 건 수정", false);
		$this->addMethod("registerOrModify", "upsert", "단 건 수정/등록", false);
		$this->addMethod("delete", "delete", "단 건 삭제", false);

		$this->phpFile->saveAs($serviceClass->getPath());
	}

	private function addDaoProperty($daoClass) {

		$this->daoInstanceName = lcfirst($daoClass->getClassName());

		$propertyIdx = $this->phpFile->addProperty($this->daoInstanceName, 'private', false, $daoClass->getClassName(), null);
		$this->phpFile->setPropertyDescription($propertyIdx, $daoClass->getClassName());
	}

	private function addMethod($methodPrefix, $daoMethodPrefix, $title, $returnSyntax) {

		$methodName = $methodPrefix.$this->tableNameToCamel;
		$daoMethodName = $daoMethodPrefix.$this->tableNameToCamel;

		$methodIdx = $this->phpFile->addMethod($methodName, 'public', false, 'array<'.$this->voClassName.'>');
		
		$this->phpFile->setMethodDescription($methodIdx, $this->tableName.' 테이블 '.$title);
		$this->phpFile->setMethodArguments($methodIdx, array($this->voClassName." \$paramVo"));

		$codes = array();

		if ($returnSyntax) {
			$codes[] = 'return $this->'.$this->daoInstanceName.'->'.$daoMethodName.'($paramVo);';
		} else {
			$codes[] = '$this->'.$this->daoInstanceName.'->'.$daoMethodName.'($paramVo);';
		}

		$this->phpFile->setMethodCode($methodIdx, implode(PHP_EOL, $codes));
	}

}
