<?php

namespace org\autoset\santizen\action;

use org\autoset\santizen\util\ConfigUtil;

class ConfigAction implements Action {

	public function setCommandArguments(array $args) {
		for ($i=0, $size=sizeof($args); $i<$size; $i+=2) {
			ConfigUtil::setVar($args[$i], isset($args[$i+1]) ? $args[$i+1] : null);
		}
	}

	public function run() {

	}
}
