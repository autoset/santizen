<?php

namespace org\autoset\santizen;

use org\autoset\santizen\exception\ParameterInvalidException;

class SantizenMain {

	private $actionName = null;

	private $commandArgs = array();

	public function __construct() {

		array_shift($_SERVER['argv']);
		$this->commandArgs = $_SERVER['argv'];

		if (sizeof($this->commandArgs) < 1) {
			throw new ParameterInvalidException();
		}

		$this->actionName = array_shift($this->commandArgs);

		try {
			$action = $this->getActionInstance($actionName);
		} catch (\ReflectionException $re) {
			echo "Invalid santizen action.";
			exit;
		}

		$action->setCommandArguments($this->commandArgs);
		$action->run();
	}

	private function getActionInstance() {
		$actionClassName = "org\\autoset\\santizen\\action\\".ucfirst($this->actionName)."Action";
		$reflectionClass = new \ReflectionClass($actionClassName);
		return $reflectionClass->newInstance();
	}

}
