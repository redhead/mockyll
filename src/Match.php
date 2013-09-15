<?php

namespace Mockyll;

use Tester\Assert;

/**
 * @internal use factory methods of Match class.
 */
class ArgumentMatcher {

	private $callback;


	public function __construct(callable $callback) {
		$this->callback = $callback;
	}


	public function match($arg) {
		return call_user_func_array($this->callback, array($arg));
	}

}

class Match {


	public static function is($expected) {
		return static::match(function ($arg) use($expected) {
							Assert::equal($expected, $arg);
						});
	}


	public static function isNot($expected) {
		return static::match(function ($arg) use($expected) {
							Assert::notEqual($expected, $arg);
						});
	}


	public static function has($expected) {
		return static::match(function ($arg) use ($expected) {
							Assert::contains($expected, $arg);
						});
	}


	public static function hasNot($expected) {
		return static::match(function ($arg) use ($expected) {
							Assert::notContains($expected, $arg);
						});
	}


	public static function notNull() {
		return static::match(function($arg) {
							if($arg === NULL) {
								Assert::fail('%1 should not be null', $arg);
							}
						});
	}


	public static function isOf($expected) {
		return static::match(function ($arg) use ($expected) {
							Assert::type($expected, $arg);
						});
	}


	public static function with($callback) {
		return new \Mockyll\ArgumentMatcher(function ($arg) use ($callback) {
					$retVal = $callback($arg);
					return ($retVal !== false);
				});
	}


	private static function match($callback) {
		return new \Mockyll\ArgumentMatcher(function ($arg) use ($callback) {
					try {
						$callback($arg);
						return true;
					} catch(\Tester\AssertException $e) {
						return false;
					}
				});
	}

}