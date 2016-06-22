<?php

namespace org\autoset\santizen\database;

use Exception;
use org\autoset\santizen\exception\DataAccessException;
use mysqli;

class MysqlDriver
{
	private $_conn = null;
	private $_connected = false;
	private	$_resultMode		= null;

	private $_transMode			= false;
	private $_transFailedCnt	= 0;

	public	$debug				= false;

	private $_host = null;

	public function __construct()
	{
		
	}

	public function __destruct()
	{
		if ($this->_connected)
			$this->disconnect();
	}

	public function connect($url, $username, $password)
	{
		$tmp = parse_url($url);

		if (!array_key_exists('scheme',$tmp) || $tmp['scheme'] != 'mysql')
			throw new DataAccessException("데이터베이스 스키마가 드라이버와 일치하지 않습니다.");

		$this->_host = 'localhost';
		$dbname = 'temp';
		$port = 3306;

		if (array_key_exists('host',$tmp))
			$this->_host = $tmp['host'];

		if (array_key_exists('port',$tmp))
		{
			$this->_host .= ':'.$tmp['port'];
			$port = $tmp['port'];
		}

		if (array_key_exists('path',$tmp))
			$dbname = substr($tmp['path'],1);

		try
		{
			$this->_conn = new mysqli($tmp['host'], $username, $password, $dbname, $port);
		}
		catch (Exception $ex)
		{
			throw $ex;
		}

		$this->_conn->set_charset("utf8");

		$this->_connected = $this->_conn ? true : false;

		return $this->_connected;
	}

	public function disconnect()
	{
		$this->_conn->close();

		$this->_connected = false;
	}

	public function isConnected()
	{
		return $this->_connected;
	}

	public function getVersion()
	{
		return $this->_conn->server_version;
	}

	private function _smartFreeResult(&$result)
	{
		if ($result && $this->_resultMode == MYSQLI_USE_RESULT)
			$result->close();
	}

	private function _prepareSql($sql, $params)
	{
		// 바인딩하기 전의 쿼리에서 탭, 공백 제거 
		// (문제가 될 수 있는데, 운영해보고 결정)
		$sql = preg_replace("#\s{1,}#s"," ",$sql);

		$arrTmp = explode('?', $sql);
		$nTmpCnt = sizeof($arrTmp);
		$arrDat = array();

		foreach ($arrTmp as $idx => $tmp)
		{
			if (!isset($params[$idx]))
				$arrDat[] = $tmp.($idx < $nTmpCnt - 1 && $params[$idx] === NULL ? 'NULL' : '');
			elseif (is_bool($params[$idx]))
				$arrDat[] = $tmp."'".($params[$idx] ? 1 : 0)."'";
			elseif (is_string($params[$idx]))
				$arrDat[] = $tmp."'".$this->escapeString($params[$idx])."'";
			elseif (is_array($params[$idx]) || is_object($params))
				continue;
			else
				$arrDat[] = $tmp.$this->escapeString($params[$idx]);
		}

		unset($arrTmp);
		
		return implode('',$arrDat);
	}

	public function escapeString($s)
	{
		return $this->_conn->real_escape_string($s);
	}

	public function startTrans()
	{
		$this->_conn->autocommit(FALSE);

		$this->_transMode		= true;
		$this->_transFailedCnt	= 0;

		$this->Logging("++ StartTrans");
	}

	public function completeTrans($autoComplete = true)
	{
		if ($autoComplete && $this->_transFailedCnt === 0)
			$this->commitTrans();
		else
			$this->rollbackTrans();
	}

	public function commitTrans($bCommit=true)
	{
		if ($bCommit)
			$this->_conn->commit();
		else
			$this->_conn->rollback();

		$this->_transMode		= false;
		$this->_transFailedCnt	= 0;

		$this->_conn->autocommit(TRUE);

		$this->Logging("++ CommitTrans");
	}

	public function rollbackTrans()
	{
		$this->_conn->rollback();

		$this->_transMode		= false;
		$this->_transFailedCnt	= 0;

		$this->_conn->autocommit(TRUE);

		$this->Logging("++ RollbackTrans");
	}

	public function hasFailedTrans()
	{
		return $this->_transFailedCnt > 0;
	}

	private function _execute($sql, $params = array())
	{
		if (!$this->isConnected())
		{
			throw new \org\autoset\santorini\exception\DataAccessException("데이터베이스에 연결되어 있지 않습니다.");
			return false;
		}

		$sql = trim($sql);

		$this->_resultMode = MYSQLI_STORE_RESULT;//(strtolower(substr($sql,0,6)) == 'select') ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT;

		if (sizeof($params) > 0)
			$sql = $this->_prepareSql($sql, $params);

		if ($this->debug)
			echo '<fieldset><legend><b>(mysqli) :</b></legend><xmp>'.$sql.'</xmp>';

		$this->logging($sql);

		$result = $this->_conn->query($sql, $this->_resultMode);

		if ($this->getErrorNo() > 0)
			$result = false;

		if ($this->debug && !$result)
			echo '<fieldset style="margin:5px;"><legend><b><span style="color:red">Error '.$this->GetErrorNo().'</span></b></legend><xmp> '.$this->GetError().'</xmp></fieldset>';

		if ($this->debug)
			echo '</fieldset>';

		if ($this->isTransMode() && !$result)
			$this->_transFailedCnt++;

		if (!$result)
		{
			$this->logging("!!ERROR!!\t".$this->getError());
			throw new \Exception($this->getError());
		}

		return $result;
	}

	public function execute($sql, $params = array())
	{
		$result = $this->_execute($sql, $params);
		$this->_smartFreeResult($result);
	}
	
	function getFoundRows()
	{
		return $this->getOne("SELECT FOUND_ROWS()");
	}

	public function getInsertId()
	{
		return $this->_conn->insert_id;
	}

	public function getAffectedRows()
	{
		return $this->_conn->affected_rows;
	}

	public function getAll($sql, $params = array())
	{
		$result = $this->_Execute($sql, $params);

		if (!$result)
			return array();

		if ($result->num_rows < 1)
			return array();

		$arrDat = array();

		while ($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			$arrDat[] = $row;
		}

		$this->_smartFreeResult($result);

		return $arrDat;
	}

	public function getOne($sql, $params = array())
	{
		$result = $this->_Execute($sql, $params);

		if (!$result)
			return null;

		if ($result->num_rows < 1)
			return null;

		$dat = $result->fetch_array(MYSQLI_NUM);

		$this->_smartFreeResult($result);

		return $dat[0];
	}

	public function getCol($sql, $params = array())
	{
		$result = $this->_Execute($sql, $params);

		if (!$result)
			return array();

		if ($result->num_rows < 1)
			return array();

		$arrDat = array();

		while ($row = $result->fetch_array(MYSQLI_NUM))
		{
			$arrDat[] = $row[0];
		}

		$this->_smartFreeResult($result);

		return $arrDat;
	}

	public function getRow($sql, $params = array())
	{
		$result = $this->_execute($sql, $params);

		if (!$result)
			return null;

		if ($result->num_rows < 1)
			return null;

		$arrDat = $result->fetch_array(MYSQLI_ASSOC);

		$this->_smartFreeResult($result);

		return $arrDat;
	}

	public function getErrorNo()
	{
		return $this->_conn->errno;
	}

	public function getError()
	{
		return $this->_conn->error;
	}

	public function isTransMode()
	{
		return $this->_transMode;
	}

	public function getFields($tableName)
	{
		$result = $this->_execute('SELECT * FROM `'.$tableName.'` LIMIT 1');
		$fields = $result->fetch_fields();
		$result = array();
		foreach ($fields as $field) {
			$result[] = array("name" => $field->name, "type" => $this->getDataType($field->type));
		}
		return $result;
	}

	public function getDataType($typeNo)
	{
		switch ($typeNo) {
			case 1:
			case 2:
			case 3:
				return "int";
			case 4:
			case 5:
				return "float";
			case 8:
			case 9:
				return "long";
			case 10:
			case 11:
			case 12:
				return "date";
		}

		return "string";
	}

	private function logging($sql)
	{
		return ;

		$path = SANTIZEN_ROOT;

		if (!file_exists($path))
			mkdir($path,0777,true);

		$fp = fopen($path.'/MySQLDriver.log','a+');
		fwrite($fp, '['.$this->_host."]\t".date('Y-m-d H:i:s')."\t".($this->IsTransMode() ? 'TM' : 'NT')."\t".preg_replace("#\s{1,}#", " ", $sql).PHP_EOL);
		fclose($fp);
	}
} 

?>