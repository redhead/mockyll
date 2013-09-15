<?php

namespace Mockyll;

/**
 * Represents a mock for no particular class or interface.
 *
 * @author Radek Ježdík
 * @internal
 */
class Mock {

	/** @var MockController */
	private $controller;


	public function __construct(MockController $controller) {
		$this->controller = $controller;
	}


	public function __call($name, $arguments) {
		return $this->controller->onCall($this, $name, $arguments);
	}

}