<?php

namespace org\autoset\santizen\action;

use org\autoset\santizen\util\ConfigUtil;

class FetchAction implements Action {

	public function setCommandArguments(array $args) {
	}

	public function run() {
		echo ConfigUtil::getVar('DB.TYPE');
	}
}
