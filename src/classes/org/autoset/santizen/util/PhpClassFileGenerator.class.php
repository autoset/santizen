<?php

namespace org\autoset\santizen\util;

use org\autoset\santizen\util\StringHelper;

class PhpClassFileGenerator {

	private $namespace = null;
	private $useClasses = array();

	private $className = null;
	private $classDescription = null;
	private $extendsClassName = null;
	private $implementsInterfaceName = array();

	private $properties = array();
	private $methods = array();

	public function setNamespace($namespace) {
		$this->namespace = $namespace;
	}

	public function setClassName($className) {
		$this->className = $className;
	}

	public function setClassDescription($classDescription) {
		$this->classDescription = $classDescription;
	}

	public function setExtendsClassName($extendsClassName) {
		$this->extendsClassName = $extendsClassName;
	}

	public function addImplementsInterfaceName($implementsInterfaceName) {
		$this->implementsInterfaceName[] = $implementsInterfaceName;
	}

	public function addUseClass($classPath = "") {
		$this->useClasses[] = $classPath;
	}

	public function addProperty($propertyName, $accessModifier = 'public', $isStatic = false, $returnType = '', $value = null) {
		$this->properties[] = array(
			'accessModifier'	=> $accessModifier,
			'isStatic'			=> $isStatic,
			'returnType'		=> $returnType,
			'name'				=> $propertyName,
			'value'				=> $value,
			'description'		=> ''
		);

		return sizeof($this->properties) - 1;
	}

	public function setPropertyDescription($propertyIndex, $description) {
		$this->properties[$propertyIndex]['description'] = $description;
	}

	public function addMethod($methodName, $accessModifier = 'public', $isStatic = false, $returnType = '') {
		$this->methods[] = array(
			'accessModifier'	=> $accessModifier,
			'isStatic'			=> $isStatic,
			'returnType'		=> $returnType,
			'name'				=> $methodName,
			'arguments'			=> array(),
			'throws'			=> '',
			'description'		=> '',
			'code'				=> ''
		);

		return sizeof($this->methods) - 1;
	}

	public function setMethodThrows($methodIndex, $throws) {
		$this->methods[$methodIndex]['throws'] = $throws;
	}

	public function setMethodDescription($methodIndex, $description) {
		$this->methods[$methodIndex]['description'] = $description;
	}

	public function setMethodCode($methodIndex, $code) {
		$this->methods[$methodIndex]['code'] = $code;
	}

	public function setMethodArguments($methodIndex, $arguments = array()) {
		$this->methods[$methodIndex]['arguments'] = $arguments;
	}

	public function saveAs($fileName) {

		$contents = array();

		$contents[] = '<?php';

		if ($this->namespace != '') {
			$contents[] = '';
			$contents[] = 'namespace '.$this->namespace.';';
		}

		if (sizeof($this->useClasses) > 0) {
			$contents[] = '';
			foreach ($this->useClasses as $classPath) {
				if ($classPath == "")
					$contents[] = '';
				else
					$contents[] = 'use '.$classPath.';';
			}
		}

		if ($this->classDescription != '') {
			$contents[] = '';
			$contents[] = '/**';
			$contents[] = ' * <pre>';
			$contents[] = ' * '.$this->classDescription;
			$contents[] = ' * </pre>';
			$contents[] = ' */';
		}

		$classHeads = array('class',$this->className);
		if ($this->extendsClassName != '') {
			$classHeads[] = 'extends';
			$classHeads[] = $this->extendsClassName;
		}
		if (sizeof($this->implementsInterfaceName) > 0) {
			$classHeads[] = 'implements';
			$classHeads[] = implode(', ', $this->implementsInterfaceName);
		}

		$contents[] = implode(' ', $classHeads).' {';

		foreach ($this->properties as $property) {

			$propertyHeads = array($property['accessModifier']);

			if ($property['isStatic']) {
				$propertyHeads[] = 'static';
			}

			$propertyHeads[] = '$'.$property['name'];

			if ($property['value'] != null) {
				$propertyHeads[] = '=';

				if (is_string($property['value'])) {
					$propertyHeads[] = '"'.$property['value'].'"';
				} elseif (is_bool($property['value'])) {
					$propertyHeads[] = $property['value'] ? 'true' : 'false';
				} else {
					$propertyHeads[] = $property['value'];
				}
			}

			$contents[] = '';

			if ($property['description'] != '') {
				$contents[] = '';
				$contents[] = "\t".'/**';
				$contents[] = "\t".' * <pre>';
				$contents[] = "\t".' * '.$property['description'];
				$contents[] = "\t".' * </pre>';
				$contents[] = "\t".' */';
			}
			$contents[] = "\t".implode(' ', $propertyHeads).";";
		}

		foreach ($this->methods as $method) {

			$methodHeads = array($method['accessModifier']);

			if ($method['isStatic']) {
				$methodHeads[] = 'static';
			}

			$methodHeads[] = 'function';
			$methodHeads[] = $method['name'].'('.implode(', ', $method['arguments']).')';
			$methodHeads[] = '{';

			$contents[] = '';

			if ($method['description'] != '') {
				$contents[] = "\t".'/**';
				$contents[] = "\t".' * <pre>';
				$contents[] = "\t".' * '.$method['description'];
				$contents[] = "\t".' * </pre>';
				$contents[] = "\t".' * ';

				if (sizeof($method['arguments']) > 0) {
					foreach ($method['arguments'] as $argument) {
						if (($pos = strpos($argument, ' ')) !== false) {
							$contents[] = "\t".' * @param '.substr($argument, $pos + 1);
						} else {
							$contents[] = "\t".' * @param '.$argument;
						}
					}
				}

				if ($method['returnType'] != '') {
					$contents[] = "\t".' * @return '.$method['returnType'];
				}

				$contents[] = "\t".' */';
			}

			$contents[] = "\t".implode(' ', $methodHeads);
			$contents[] = StringHelper::appendTabByLine($method['code'], 2);
			$contents[] = "\t".'}';
		}

		$contents[] = '}';

		file_put_contents($fileName, implode(PHP_EOL, $contents));
	}

}

