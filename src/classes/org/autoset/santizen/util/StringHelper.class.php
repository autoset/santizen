<?php

namespace org\autoset\santizen\util;

class StringHelper {

	public static function underscore2camel($name) {
		$arr = explode('_', $name);
		$arr = array_map('ucfirst', $arr);
		return lcfirst(implode('', $arr));
	}

	public static function appendTabByLine($text, $tabCount = 1) {
		$arr = explode("\n", $text);
		foreach ($arr as $idx=>$str) {
			$arr[$idx] = str_repeat("\t", $tabCount).$str;
		}
		return implode("\n", $arr);
	}

}

