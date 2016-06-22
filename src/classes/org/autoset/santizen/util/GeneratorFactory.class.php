<?php

namespace org\autoset\santizen\util;

class GeneratorFactory {

	public static function getInstance($language, $framework) {

		try {
			$generatorClassName = "org\\autoset\\santizen\\generator\\".lcfirst($language)."\\".lcfirst($framework)."\\".ucfirst($framework)."Generator";
			$reflectionClass = new \ReflectionClass($generatorClassName);
			$generatorClass = $reflectionClass->newInstance();
		} catch (\ReflectionException $re) {
			echo "Unknown generator type.";
			exit;
		}

		return $generatorClass;
	}

}

