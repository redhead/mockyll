<?php

namespace Mockyll;

use Mockista\ClassGenerator\ClassGenerator;
use Mockista\ClassGenerator\MethodFinder;

/**
 * MockController creates mocks and verifies their expectations.
 * 
 * <p>Controller operates in two states: record and play.</p>
 * <p>In the record state, it stores expectations about the mocks.</p>
 * <p>In the play state, every call is verified that it matches expectations 
 * from the record state.<br/>
 * The play state is active while executing function passed to play()
 * method.</p>
 *
 * @author Radek Ježdík
 */
class MockController {

    /** @var mixed[] */
    private $mocks = array();

    /** @var MockRecords[] */
    private $mockRecords = array();

    /** @var mixed[] */
    private $partialMocks = array();

    /** @var bool */
    private $isRecording = true;


    /**
     * Creates a new mock based on method mock definitions passed in array 
     * and/or by extending or implementing the given class or interface.
     * 
     * The following creates an "anonymous" mock with method sayHelloWorld() 
     * which returns string:
     * <code><pre>$controller->mock(array(
     *                   'sayHelloWorld' => 'hello world!'
     * ));</pre></code>
     * 
     * The following mocks class Class with method sayHelloWorld() which 
     * returns string:
     * <code><pre>$controller->mock('Class', array(
     *      'sayHelloWorld' => 'hello world!'
     * ));</pre></code>
     * 
     * @param string|array $class class or interface to mock or array of method mocks
     * @param array $methods array of method mocks
     * @return mixed
     */
    public function mock($class = null, $methods = array()) {
        if(!is_array($class) && $methods === null) {
            $methods = $class;
            $class = null;
        }

        if($class != null) {
            $mock = $this->createClassMock($class);
        } else {
            $mock = new Mock($this);
        }

        $this->mocks[] = $mock;
        $this->mockRecords[] = new MockRecords();

        $this->recordMethods($methods, $mock);

        return $mock;
    }


    /**
     * Create a partial mock for the given class. This method can instantiate
     * the class using it's original constructor. Arguments can be passed to the 
     * constructor in the $constructorArgs parameter. Pass null if constructor
     * should not be called.
     * 
     * @param string $class the name of the class to partially mock
     * @param array $methods array of method mocks
     * @param array|null $constructorArgs array of arguments to pass to the
     * constructor or null if constructor should not be called
     * @return type
     */
    public function partial($class, $methods = array(), $constructorArgs = null) {
        $mock = $this->createClassMock($class, $constructorArgs);

        $this->mocks[] = $mock;
        $this->partialMocks[] = $mock;
        $this->mockRecords[] = new MockRecords();

        $this->recordMethods($methods ?: array(), $mock);

        return $mock;
    }


    private function createClassMock($class, $constructorArgs = null) {
        $newClass = str_replace("\\", "_", $class) . '_' . uniqid();

        $generator = new ClassGenerator();
        $generator->setMethodFinder(new MethodFinder);
        $code = $generator->generate($class, $newClass);

        eval($code);

        $mock = new $newClass(false);
        if($constructorArgs !== null) {
            call_user_func_array(array($mock, '__construct'), array(true, $constructorArgs));
        }
        $mock->__mockController = $this;

        return $mock;
    }


    private function recordMethods(array $methods, $mock) {
        foreach($methods as $methodName => $return) {
            $record = $this->onCall($mock, $methodName, array());
            
            $record->passAny();
            
            if(is_callable($return)) {
                $record->returnWith($return);
            } else {
                $record->returns($return);
            }
        }
    }


    /**
     * Activates the play state, runs the given function and verifies 
     * the expectations.
     * @param callable $fn function or closure
     */
    public function play(callable $fn) {
        $this->start();
        $fn();
        $this->finish();
    }


    /**
     * Starts the play state. In this state, every call to the mocks invokes 
     * mocked methods.
     */
    public function start() {
        $this->isRecording = false;
    }


    /**
     * Finishes the play state, verifies the expectations, clears all the 
     * methods recorded so the controller can be used to record new methods
     */
    public function finish() {
        $this->isRecording = true;

        foreach($this->mocks as $mock) {
            $this->getRecords($mock)->verify();
        }

        foreach($this->mockRecords as $rec) {
            $rec->clear();
        }
    }


    /**
     * Internal method - do not use. Records or plays the given method with
     * arguments based on the state the controller is in.
     * 
     * @param \Mockyll\Mock $mock
     * @param string $method
     * @param array $arguments
     * @return mixed
	 * @internal
     */
    public function onCall($mock, $method, $arguments) {
        $records = $this->getRecords($mock);

        if($this->isRecording) {
            return $records->record($method, $arguments);
        } else {
            if($this->isPartial($mock)) {
                return $records->playPartial($mock, $method, $arguments);
            } else {
                return $records->play($method, $arguments);
            }
        }
    }


    private function getRecords($mock) {
        $index = array_search($mock, $this->mocks);
        return $this->mockRecords[$index];
    }


    private function isPartial($mock) {
        return in_array($mock, $this->partialMocks);
    }

}