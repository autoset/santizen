<?php

namespace org\autoset\santizen\generator\php\santorini;

use org\autoset\santizen\util\PhpClassFileGenerator;
use org\autoset\santizen\util\StringHelper;

class DaoClassGenerator {

	private $phpFile = null;

	private $schemes = array();
	private $voClassName = null;
	private $tableName = null;
	private $tableNameToCamel = null;
	private $tableAlias = null;

	private $REGISTER_UID_COLUMN	= 'register_user_no';
	private $MODIFY_UID_COLUMN		= 'modify_user_no';
	private $DELETE_UID_COLUMN		= 'delete_user_no';
	private $REGISTER_DT_COLUMN		= 'register_ymdt';
	private $MODIFY_DT_COLUMN		= 'modify_ymdt';
	private $DELETE_DT_COLUMN		= 'delete_ymdt';
	private $DELETE_YN_COLUMN		= 'delete_yn';

	public function __construct(PreparePackageStructure $packageStructure) {
		
		$voClass = $packageStructure->getVoClass();
		$daoClass = $packageStructure->getDaoClass();

		$this->schemes = $packageStructure->getSchemes();

		$this->voClassName = $voClass->getClassName();
		$this->tableAlias = StringHelper::underscore2attr($packageStructure->getTableName());	
		$this->tableName = $packageStructure->getTableName();
		$this->tableNameToCamel = ucfirst(StringHelper::underscore2camel($this->tableName));

		$this->phpFile = new PhpClassFileGenerator();
		
		$this->phpFile->setNamespace($daoClass->getNamespace());
		$this->phpFile->setClassName($daoClass->getClassName());
		$this->phpFile->setClassDescription($daoClass->getDescription());
		$this->phpFile->addUseClass($voClass->getClassFullNamespace());
		$this->phpFile->setExtendsClassName("CommonDAO");

		$this->addSelectListMethod();

		$this->addSelectMethod();

		$this->addInsertMethod();

		$this->addUpdateMethod();

		$bHasDeleteYnColumn = false;

		foreach ($this->schemes as $idx => $scheme) {
			if ($scheme['name'] == $this->DELETE_YN_COLUMN) {
				$bHasDeleteYnColumn = true;
				break;
			}
		}

		if ($bHasDeleteYnColumn) {
			$this->addSoftDeleteMethod();
		} else {
			$this->addDeleteMethod();
		}

		$this->phpFile->saveAs($daoClass->getPath());
	}

	private function addSelectListMethod() {

		// 목록 조회용
		$methodName = 'selectList'.$this->tableNameToCamel;

		$methodIdx = $this->phpFile->addMethod($methodName, 'public', false, 'array<'.$this->voClassName.'>');
		
		$this->phpFile->setMethodDescription($methodIdx, $this->tableName.' 테이블 목록 조회');
		$this->phpFile->setMethodArguments($methodIdx, array($this->voClassName." \$paramVo"));

		$code = $this->getGeneratedSelectQuery('목록 조회', true );
		$this->phpFile->setMethodCode($methodIdx, $code);
	}

	private function addSelectMethod() {

		// 단 건 조회용
		$methodName = 'select'.$this->tableNameToCamel;
		
		$methodIdx = $this->phpFile->addMethod($methodName, 'public', false, $this->voClassName);

		$this->phpFile->setMethodDescription($methodIdx, $this->tableName.' 테이블 단 건 조회');
		$this->phpFile->setMethodArguments($methodIdx, array($this->voClassName." \$paramVo"));

		$code = $this->getGeneratedSelectQuery('단 건 조회', false );
		$this->phpFile->setMethodCode($methodIdx, $code);

	}

	private function addInsertMethod() {

		// 단 건 입력용
		$methodName = 'insert'.$this->tableNameToCamel;
		
		$methodIdx = $this->phpFile->addMethod($methodName, 'public', false, $this->voClassName);

		$this->phpFile->setMethodDescription($methodIdx, $this->tableName.' 테이블 단 건 입력');
		$this->phpFile->setMethodArguments($methodIdx, array($this->voClassName." \$paramVo"));
		
		$codes = array(	'$sql =<<<SQL',
						"\t".'INSERT INTO '.$this->tableName.' /* 단 건 입력 */');

		$codes[] = "\t(";
		foreach ($this->schemes as $idx => $scheme) {
			if ($idx == 0)
				$codes[] = "\t\t".$scheme['name'];
			else
				$codes[] = "\t\t, ".$scheme['name'];
		}
		$codes[] = "\t)";
		$codes[] = "\tVALUES";
		$codes[] = "\t(";
		foreach ($this->schemes as $idx => $scheme) {

			$bindingName = StringHelper::underscore2camel($scheme['name']);

			if ($scheme['name'] == $this->REGISTER_DT_COLUMN ||
				$scheme['name'] == $this->MODIFY_DT_COLUMN) {
				$bindingName = 'NOW()';
			} elseif ($scheme['name'] == $this->MODIFY_UID_COLUMN) {
				$bindingName = '#'.StringHelper::underscore2camel($this->REGISTER_UID_COLUMN).'#';
			} elseif ($scheme['name'] == $this->DELETE_UID_COLUMN) {
				$bindingName = 'NULL';
			} elseif ($scheme['name'] == $this->DELETE_DT_COLUMN) {
				$bindingName = 'NULL';
			} elseif ($scheme['name'] == $this->DELETE_YN_COLUMN) {
				$bindingName = "'N'";
			} else {
				$bindingName = "#".$bindingName."#";
			}

			if ($idx == 0)
				$codes[] = "\t\t".$bindingName;
			else
				$codes[] = "\t\t, ".$bindingName;
		}
		$codes[] = "\t)";

		$codes[] = "\1".'SQL;';
		$codes[] = '';
		$codes[] = '$this->insert($sql, $paramVo);';
		$codes[] = '';
		$codes[] = 'return $this->getSequenceNo();';

		$this->phpFile->setMethodCode($methodIdx, implode(PHP_EOL, $codes));

	}

	private function addUpdateMethod() {

		// 단 건 수정용
		$methodName = 'update'.$this->tableNameToCamel;
		
		$methodIdx = $this->phpFile->addMethod($methodName, 'public', false, $this->voClassName);

		$this->phpFile->setMethodDescription($methodIdx, $this->tableName.' 테이블 단 건 수정');
		$this->phpFile->setMethodArguments($methodIdx, array($this->voClassName." \$paramVo"));
		
		$codes = array(	'$sql =<<<SQL',
						"\t".'UPDATE '.$this->tableName.' /* 단 건 수정 */');

		$codes[] = "\tSET";

		$bStart = false;

		foreach ($this->schemes as $idx => $scheme) {

			$bindingName = StringHelper::underscore2camel($scheme['name']);

			if ($scheme['name'] == $this->REGISTER_UID_COLUMN ||
				$scheme['name'] == $this->REGISTER_DT_COLUMN ||
				$scheme['name'] == $this->DELETE_UID_COLUMN ||
				$scheme['name'] == $this->DELETE_DT_COLUMN ||
				$scheme['name'] == $this->DELETE_YN_COLUMN) {
				continue;
			} elseif ($scheme['name'] == $this->MODIFY_DT_COLUMN) {
				$bindingName = 'NOW()';
			} else {
				$bindingName = "#".$bindingName."#";
			}

			if (!$bStart) {
				$codes[] = "\t\t".$scheme['name']." = ".$bindingName;
				$bStart = true;
			} else {
				$codes[] = "\t\t, ".$scheme['name']." = ".$bindingName;
			}
		}
		
		$conditions = array();
		foreach ($this->schemes as $scheme) {
			if (!$scheme['isPk'])
				continue;

			$bindingName = StringHelper::underscore2camel($scheme['name']);
			$conditions[] = $scheme['name']." = #".$bindingName."#";
		}

		if (sizeof($conditions) > 0) {
			$codes[] = "\tWHERE ".implode(' AND ', $conditions);
		
		}

		$codes[] = "\1".'SQL;';
		$codes[] = '';
		$codes[] = '$this->update($sql, $paramVo);';

		$this->phpFile->setMethodCode($methodIdx, implode(PHP_EOL, $codes));

	}

	private function addSoftDeleteMethod() {

		// 단 건 삭제용
		$methodName = 'delete'.$this->tableNameToCamel;
		
		$methodIdx = $this->phpFile->addMethod($methodName, 'public', false, $this->voClassName);

		$this->phpFile->setMethodDescription($methodIdx, $this->tableName.' 테이블 단 건 소프트 삭제');
		$this->phpFile->setMethodArguments($methodIdx, array($this->voClassName." \$paramVo"));
		
		$codes = array(	'$sql =<<<SQL',
						"\t".'UPDATE '.$this->tableName.' /* 단 건 소프트 삭제 */');

		$codes[] = "\tSET";

		$bStart = false;

		foreach ($this->schemes as $idx => $scheme) {

			$bindingName = StringHelper::underscore2camel($scheme['name']);

			if ($scheme['name'] == $this->DELETE_UID_COLUMN) {
				$bindingName = "#".$bindingName."#";
			} elseif ($scheme['name'] == $this->DELETE_DT_COLUMN) {
				$bindingName = "NOW()";
			} elseif ($scheme['name'] == $this->DELETE_YN_COLUMN) {
				$bindingName = "'Y'";
			} else {
				continue;
			}

			if (!$bStart) {
				$codes[] = "\t\t".$scheme['name']." = ".$bindingName;
				$bStart = true;
			} else {
				$codes[] = "\t\t, ".$scheme['name']." = ".$bindingName;
			}
		}
		
		$conditions = array();
		foreach ($this->schemes as $scheme) {
			if (!$scheme['isPk'])
				continue;

			$bindingName = StringHelper::underscore2camel($scheme['name']);
			$conditions[] = $scheme['name']." = #".$bindingName."#";
		}

		if (sizeof($conditions) > 0) {
			$codes[] = "\tWHERE ".implode(' AND ', $conditions);
		
		}

		$codes[] = "\1".'SQL;';
		$codes[] = '';
		$codes[] = '$this->update($sql, $paramVo);';

		$this->phpFile->setMethodCode($methodIdx, implode(PHP_EOL, $codes));

	}

	private function addDeleteMethod() {

		// 단 건 삭제용
		$methodName = 'delete'.$this->tableNameToCamel;
		
		$methodIdx = $this->phpFile->addMethod($methodName, 'public', false, $this->voClassName);

		$this->phpFile->setMethodDescription($methodIdx, $this->tableName.' 테이블 단 건 삭제');
		$this->phpFile->setMethodArguments($methodIdx, array($this->voClassName." \$paramVo"));
		
		$codes = array(	'$sql =<<<SQL',
						"\t".'DELETE FROM '.$this->tableName.' /* 단 건 삭제 */');

		$conditions = array();
		foreach ($this->schemes as $scheme) {
			if (!$scheme['isPk'])
				continue;

			$bindingName = StringHelper::underscore2camel($scheme['name']);
			$conditions[] = $scheme['name']." = #".$bindingName."#";
		}

		if (sizeof($conditions) > 0) {
			$codes[] = "\tWHERE ".implode(' AND ', $conditions);
		
		}

		$codes[] = "\1".'SQL;';
		$codes[] = '';
		$codes[] = '$this->delete($sql, $paramVo);';

		$this->phpFile->setMethodCode($methodIdx, implode(PHP_EOL, $codes));

	}

	private function getGeneratedSelectQuery($queryTitle, $isList = false) {

		$codes = array(	'$sql =<<<SQL',
						"\t".'SELECT /* '.$queryTitle.' */');

		$hasDeleteYnColumn = false;
		$pkColumns = array();
		$columns = array();
		foreach ($this->schemes as $scheme) {
			$columns[] = $this->tableAlias.'.'.$scheme['name'];

			if ($scheme['isPk']) {
				$pkColumns[] = $scheme['name'];
			}

			if ($scheme['name'] == $this->DELETE_YN_COLUMN) {
				$hasDeleteYnColumn = true;
			}
		}

		$codes[] = "\t\t".implode(PHP_EOL . "\t\t".', ' , $columns);
		$codes[] = "\t".'FROM '.$this->tableName.' AS '.$this->tableAlias;

		$exposuredWhere = false;

		if ($hasDeleteYnColumn) {
			$codes[] = "\t".'WHERE '.$this->tableAlias.'.'.$this->DELETE_YN_COLUMN.' = \'N\'';
			$exposuredWhere = true;
		}

		if ($isList) {

			if (!$exposuredWhere) {
				$codes[] = "\t".'WHERE 1 = 1';
			}
			
			$codes[] = "\1".'SQL;';
			$codes[] = '';

			foreach ($this->schemes as $scheme) {

				if ($scheme['name'] == $this->REGISTER_UID_COLUMN ||
					$scheme['name'] == $this->REGISTER_DT_COLUMN ||
					$scheme['name'] == $this->MODIFY_UID_COLUMN ||
					$scheme['name'] == $this->MODIFY_DT_COLUMN ||
					$scheme['name'] == $this->DELETE_UID_COLUMN ||
					$scheme['name'] == $this->DELETE_DT_COLUMN ||
					$scheme['name'] == $this->DELETE_YN_COLUMN) {
					continue;
				}

				$bindingName = StringHelper::underscore2camel($scheme['name']);

				$codes[] = 'if (trim($paramVo->get'.ucfirst($bindingName).'()) != \'\') {';
				$codes[] = "\t".'$sql .= " AND '.$this->tableAlias.'.'.$scheme['name'].' = #'.$bindingName.'#";';
				$codes[] = '}';
				$codes[] = '';

			}

			$codes[] = '';
			$codes[] = '$sql =<<<SQL';

			$orders = array();
			foreach ($pkColumns as $pkCol) {
				$orders[] = $this->tableAlias.'.'.$pkCol.' DESC';
			}
			$codes[] = "\t".'ORDER BY '.implode(', ', $orders);

			$codes[] = "\t".'LIMIT #startIdx#, #pageView#';
		} else {

			$wheres = array();

			foreach ($pkColumns as $pkCol) {
				$wheres[] = $this->tableAlias.'.'.$pkCol.' = #'.StringHelper::underscore2camel($pkCol).'#';
			}

			if ($exposuredWhere) {
				$codes[] = "\t".'AND '.implode(' AND ', $wheres);
			} else {
				$codes[] = "\t".'WHERE '.implode(' AND ', $wheres);
			}

			$codes[] = "\t".'LIMIT 0, 1';
		}

		$codes[] = "\1".'SQL;';
		$codes[] = '';

		if ($isList) {
			$codes[] = 'return $this->selectList($sql, $paramVo, new '.$this->voClassName.'());';
		} else {
			$codes[] = 'return $this->selectByPk($sql, $paramVo, new '.$this->voClassName.'());';
		}

		return implode(PHP_EOL, $codes);
	}
}
