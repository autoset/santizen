<?php

namespace org\autoset\santizen\action;

interface Action {

	public function setCommandArguments(array $args);

	public function run();
}
