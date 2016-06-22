<?php

namespace org\autoset\santizen\util;

class StringHelper {

	public static function underscore2camel($name) {
		$arr = explode('_', $name);
		$arr = array_map('ucfirst', $arr);
		return lcfirst(implode('', $arr));
	}

	public static function underscore2attr($name) {
		$arr = explode('_', $name);
		$ret = array();
		foreach ($arr as $str) {
			$ret[] = substr($str,0,1);
		}
		return strtolower(implode('', $ret));
	}

	public static function appendTabByLine($text, $tabCount = 1) {
		$arr = explode("\n", $text);
		foreach ($arr as $idx=>$str) {
			if (preg_match("#\1#", $str)) {
				$arr[$idx] = str_replace("\1", "", $str);
				continue;
			}

			$arr[$idx] = str_repeat("\t", $tabCount).$str;
		}
		return implode("\n", $arr);
	}

}

