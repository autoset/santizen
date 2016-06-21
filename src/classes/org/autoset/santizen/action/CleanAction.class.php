<?php

namespace org\autoset\santizen\action;

use org\autoset\santizen\util\ConfigUtil;

class CleanAction implements Action {

	public function setCommandArguments(array $args) {
	}

	public function run() {
		ConfigUtil::cleanUp();
	}
}
