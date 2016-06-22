<?php

namespace org\autoset\santizen\generator\php\santorini;

use org\autoset\santizen\util\PhpClassFileGenerator;
use org\autoset\santizen\util\StringHelper;

class SantoriniGenerator {

	private $tableName = null;
	private $schemes = array();

	private $outputDir = null;
	private $namespace = null;

	public function start($outputDir, $namespace, $tableName, $schemes) {

		$this->outputDir = $outputDir;
		$this->namespace = $namespace;
		$this->tableName = $tableName;
		$this->schemes = $schemes;

		$this->makeDirectory();

		$this->makeVoClassFile();

		$this->makeDaoClassFile();
	}

	private function makeDirectory() {

		$dirs = array(
					'controller',
					'service',
					'dao',
					'vo'
				);

		foreach ($dirs as $dir) {
			
			$path = $this->getOutputDir($dir);

			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}

		}
	}

	private function makeVoClassFile() {

		$className = ucfirst($this->tableName).'Entity';
		
		$phpFile = new PhpClassFileGenerator();
		
		$phpFile->setNamespace($this->getNamespace('vo'));
		$phpFile->setClassName($className);
		$phpFile->setClassDescription($this->tableName.' 테이블에 대한 VO');
		$phpFile->setExtendsClassName("");
		$phpFile->setImplementsInterfaceName("");

		foreach ($this->schemes as $scheme) {

			// property
			$propertyName = StringHelper::underscore2camel($scheme['name']);
			$propertyIdx = $phpFile->addProperty($propertyName, 'private', false, $scheme['type'], null);
			$phpFile->setPropertyDescription($propertyIdx, $scheme['name'].' 컬럼');

			// getter
			$methodName = StringHelper::underscore2camel($scheme['name']);
			$methodIdx = $phpFile->addMethod('get'.ucfirst($methodName), 'public', false, $scheme['type']);
			$phpFile->setMethodDescription($methodIdx, $this->tableName.' 테이블의 '.$scheme['name'].' 컬럼에 대한 Getter');
			$phpFile->setMethodCode($methodIdx, 'return $this->'.$propertyName.';');

			// setter
			$methodName = StringHelper::underscore2camel($scheme['name']);
			$methodIdx = $phpFile->addMethod('set'.ucfirst($methodName), 'public', false);
			$phpFile->setMethodDescription($methodIdx, $this->tableName.' 테이블의 '.$scheme['name'].' 컬럼에 대한 Getter');
			$phpFile->setMethodArguments($methodIdx, array("\$".StringHelper::underscore2camel($scheme['name'])));
			$phpFile->setMethodCode($methodIdx, '$this->'.$propertyName.' = $'.$propertyName.';');
		}

		$classFilePath = $this->getOutputDir('vo/'.$className.'.class.php');
		$phpFile->saveAs($classFilePath);

	}

	private function makeDaoClassFile() {

		$voClassName = ucfirst($this->tableName).'Entity';

		$className = ucfirst($this->tableName).'Dao';
		$tableAlias = StringHelper::underscore2attr($this->tableName);
		
		$phpFile = new PhpClassFileGenerator();
	
		$phpFile->setNamespace($this->getNamespace('dao'));
		$phpFile->addUseClass($this->getNamespace("vo\\".$voClassName));
		$phpFile->setClassName($className);
		$phpFile->setClassDescription($this->tableName.' 테이블에 대한 DAO');
		$phpFile->setExtendsClassName("CommonDAO");
		$phpFile->setImplementsInterfaceName("");

		// 목록 조회용
		$methodName = StringHelper::underscore2camel($this->tableName);
		$methodIdx = $phpFile->addMethod('selectList'.ucfirst($methodName), 'public', false, 'array<'.$voClassName.'>');
		$phpFile->setMethodDescription($methodIdx, $this->tableName.' 테이블 목록 조회');
		$phpFile->setMethodArguments($methodIdx, array($voClassName." \$paramVo"));

		$codes = array(	'$sql =<<<SQL',
						"\t".'SELECT /* '.$this->tableName.' 테이블 목록 조회'.' */');

		$hasDeleteYnColumn = false;
		$pkColumns = array();
		$columns = array();
		foreach ($this->schemes as $scheme) {
			$columns[] = $tableAlias.'.'.$scheme['name'];

			if ($scheme['isPk']) {
				$pkColumns[] = $tableAlias.'.'.$scheme['name'].' DESC';
			}

			if ($scheme['name'] == 'delete_yn') {
				$hasDeleteYnColumn = true;
			}
		}

		$codes[] = "\t\t".implode(PHP_EOL . "\t\t".', ' , $columns);

		$codes[] = "\t".'FROM '.$this->tableName.' AS '.$tableAlias;

		if ($hasDeleteYnColumn) {
			$codes[] = "\t".'WHERE '.$tableAlias.'.delete_yn = \'N\'';
		}

		$codes[] = "\t".'ORDER BY '.implode(', ', $pkColumns);

		$codes[] = "\1".'SQL;';
		$codes[] = '';
		$codes[] = 'return $this->selectList($sql, $paramVo, new '.$voClassName.'());';


		$phpFile->setMethodCode($methodIdx, implode(PHP_EOL, $codes));


		$classFilePath = $this->getOutputDir('dao/'.$className.'.class.php');
		$phpFile->saveAs($classFilePath);
	}

	private function getOutputDir($dir) {
		$path = $this->outputDir.DIRECTORY_SEPARATOR.$this->tableName.DIRECTORY_SEPARATOR.$dir;
		return $path;
	}

	private function getNamespace($dir) {
		$path = $this->namespace."\\".$this->tableName."\\".$dir;
		return $path;
	}

}
