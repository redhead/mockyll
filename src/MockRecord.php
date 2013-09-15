<?php

namespace Mockyll;

/**
 * Represents a method call recorded for play/verify.
 *
 * @author Radek Ježdík
 */
class MethodRecord {

	/** @var string name of the method called */
	private $methodName;

	/** @var array arguments passed */
	private $arguments = array();

	/** @var callable callback for user defined argument matching */
	private $matcher = null;

	/** @var array array of values to be returned by each call */
	private $returns = null;

	/** @var Exception exception to be thrown when method is called */
	private $exception = null;

	/**
	 * @var int|array number of times the method should be called (number or interval)
	 * Defaults to 'at least one' call
	 */
	private $expectedCount = array(1, null);

	/** @var int number of method calls in play state */
	private $playCount = 0;


	public function __construct($methodName, array $arguments) {
		$this->methodName = $methodName;
		$this->arguments = $arguments;
	}


	/**
	 * Sets the return value for this method. Multiple arguments can be passed 
	 * so that they will be returned on every subsequent call on method mock.
	 * 
	 * @return \Mockyll\MethodRecord
	 * @throws \InvalidArgumentException
	 */
	public function returns() {
		$arguments = func_get_args();

		if(count($arguments) == 0) {
			throw new \InvalidArgumentException("Method returns() called without a parameter");
		}

		$this->returns = $arguments;
		return $this;
	}


	/**
	 * Sets the callback that will be used to create dynamic return value.
	 * 
	 * @param callable $callback
	 * @return \Mockyll\MethodRecord
	 */
	public function returnWith(callable $callback) {
		$this->returns = array(new ComputedReturn($callback));
		return $this;
	}


	/**
	 * Sets the exception that will be thrown when calling the method mock.
	 * 
	 * @param \Exception $exception
	 * @return \Mockyll\MethodRecord
	 */
	public function raises(\Exception $exception) {
		$this->exception = $exception;
		return $this;
	}


	/**
	 * Sets the exception that will be thrown when calling the method mock.
	 * 
	 * @param \Exception $exception
	 * @return \Mockyll\MethodRecord
	 */
	public function throws(\Exception $exception) {
		$this->raises($exception);
		return $this;
	}


	/**
	 * Sets the expected call count to zero.
	 * @return \Mockyll\MethodRecord
	 */
	public function never() {
		$this->expectedCount = 0;
		return $this;
	}


	/**
	 * Sets the expected call count to one.
	 * @return \Mockyll\MethodRecord
	 */
	public function once() {
		$this->expectedCount = 1;
		return $this;
	}


	/**
	 * Sets the expected call count to two.
	 * @return \Mockyll\MethodRecord
	 */
	public function twice() {
		$this->expectedCount = 2;
		return $this;
	}


	/**
	 * Sets the expected call count to be greater or equal to the given number.
	 * 
	 * @param type $number 
	 * @return \Mockyll\MethodRecord
	 * @throws \InvalidArgumentException
	 */
	public function atLeast($number) {
		if(!is_numeric($number) || $number <= 0) {
			throw new \InvalidArgumentException("Invalid parameter passed to atMost() method");
		}
		$this->expectedCount = array((int) $number, null);
		return $this;
	}


	/**
	 * Sets the expected call count to be between 0 and the given number.
	 * 
	 * @param type $number
	 * @return \Mockyll\MethodRecord
	 * @throws \InvalidArgumentException
	 */
	public function atMost($number) {
		if(!is_numeric($number) || $number <= 0) {
			throw new \InvalidArgumentException("Invalid parameter passed to atMost() method");
		}
		$this->expectedCount = array(0, (int) $number);
		return $this;
	}


	/**
	 * Sets the expected call count to be the given number or when 2 numbers
	 * are passed an interval between the two.
	 * 
	 * @param int $low the exact number or the lower bound of the interval
	 * @param int $high the higher bound of the interval
	 * @return \Mockyll\MethodRecord
	 * @throws \InvalidArgumentException
	 */
	public function times() {
		$args = func_get_args();

		if(count($args) == 0) {
			throw new \InvalidArgumentException("No parameter(s) passed to times() method");
		}

		if(!is_numeric($args[0]) || (isset($args[1]) && !is_numeric($args[1]))) {
			throw new \InvalidArgumentException("Parameter(s) passed to times() method must be numeric");
		}

		if($args[0] < 0 || (isset($args[1]) && $args[1] < $args[0])) {
			throw new \InvalidArgumentException("Parameter(s) passed to times() method must be greater than zero");
		}

		if(count($args) === 1) {
			$this->expectedCount = (int) $args[0];
		} else if(count($args) === 2) {
			$this->expectedCount = array(
				(int) $args[0],
				(int) $args[1]
			);
		} else {
			throw new \InvalidArgumentException("Invalid parameter(s) passed to times() method");
		}
		return $this;
	}


	/**
	 * Sets the user defined callback that will check and/or match the arguments
	 * passed to the method mock when called.
	 * 
	 * @param \Mockyll\callable $matcher
	 * @return \Mockyll\MethodRecord
	 */
	public function matchAll(callable $matcher) {
		$this->matcher = $matcher;
		return $this;
	}


	/**
	 * Sets the user defined callback that will check and/or match the arguments
	 * passed to the method mock when called.
	 * 
	 * @param \Mockyll\callable $matcher
	 * @return \Mockyll\MethodRecord
	 */
	public function match() {
		$this->arguments = func_get_args();
		return $this;
	}


	/**
	 * Marks that the method can be passed any arguments when called. 
	 * No expactations on arguments are set.
	 * 
	 * @return \Mockyll\MethodRecord
	 */
	public function passAny() {
		$this->matcher = function() {
					return true;
				};
		return $this;
	}


	/**
	 * 
	 * @internal used to count calls and match/return expactations
	 * @param array $arguments passed arguments to the method mock
	 * @return mixed
	 * @throws \Mockyll\UnexpectedStateException
	 */
	public function play(array $arguments) {
		if($this->matcher !== null) {
			$val = call_user_func_array($this->matcher, $arguments);
			if($val === false) {
				throw new UnexpectedStateException("Arguments of method $this->methodName() do not match");
			}
		} else {
			$this->validateArguments($arguments);
		}

		$return = $this->getReturnValue($arguments);

		$this->playCount++;

		if($this->exception !== null) {
			throw $this->exception;
		}

		return $return;
	}


	private function validateArguments(array $actualArguments) {
		if(count($actualArguments) != count($this->arguments)) {
			throw new UnexpectedStateException("Unexpected number of arguments passed to $this->methodName()");
		}

		$i = 0;
		foreach($this->arguments as $expectedArg) {
			$actualArg = $actualArguments[$i++];

			if($expectedArg instanceof ArgumentMatcher) {
				if(!$expectedArg->match($actualArg)) {
					throw new UnexpectedStateException("Argument $i passed to $this->methodName() was not expected");
				}
			} else {
				try {
					\Tester\Assert::equal($expectedArg, $actualArg);
				} catch(\Tester\AssertException $e) {
					throw new UnexpectedStateException("Argument $i passed to $this->methodName() was not expected");
				}
			}
		}
	}


	private function getReturnValue(array $arguments) {
		if($this->exception !== null) {
			return null;
		}

		$return = null;

		if($this->returns !== null) {
			$return = $this->returns[$this->playCount % count($this->returns)];
			if($return instanceof ComputedReturn) {
				$return = $return->computeReturn($arguments);
			}
		}
		return $return;
	}


	/**
	 * @internal used to verify expectations
	 * @throws \Mockyll\UnexpectedStateException
	 */
	public function verify() {
		if(!$this->isCountValid()) {
			$expectedCountStr = $this->expectedCountToString();

			$message = "Method $this->methodName() is to be called $expectedCountStr, "
					. "but " . $this->formatActualCount();

			throw new UnexpectedStateException($message);
		}
	}


	private function isCountValid() {
		// range
		if(is_array($this->expectedCount)) {
			if($this->expectedCount[1] === null) {
				// at least
				return $this->playCount >= $this->expectedCount[0];
			} else {
				// at most
				return $this->playCount >= $this->expectedCount[0] &&
						$this->playCount <= $this->expectedCount[1];
			}
		}

		// exact count
		return ($this->playCount == $this->expectedCount);
	}


	private function expectedCountToString() {
		$count = $this->expectedCount;

		if(is_array($count)) {
			if($count[1] === null) {
				return "at least {$count[0]} " . $this->formatPlural($count[0]);
			}
			if($count[0] === 0) {
				return "at most {$count[1]} " . $this->formatPlural($count[1]);
			}
			return $count[0] . ' to ' . $count[1] . ' times';
		}
		return "$count " . $this->formatPlural($count);
	}


	private function formatPlural($count) {
		return ($count == 1 ? 'time' : 'times');
	}


	private function formatActualCount() {
		if($this->playCount == 0) {
			return "it wasn't called at all";
		}
		return "was $this->playCount " . $this->formatPlural($this->playCount);
	}

}

class ComputedReturn {

	private $callback;


	public function __construct(callable $callback) {
		$this->callback = $callback;
	}


	public function computeReturn(array $arguments) {
		return call_user_func_array($this->callback, $arguments);
	}

}