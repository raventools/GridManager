<?php

namespace RavenTools\GridManager;

abstract class Singleton {

	final public static function getInstance() {

		static $instance;

		if(is_null($instance)) {
			$instance = new static();
		}

		return $instance;
	}

	// enforce singleton-ness
	private function __clone() { }
	public function __wakeup() { throw new \Exception("cannot unserialize singleton"); }
	protected function __construct() { }
}
