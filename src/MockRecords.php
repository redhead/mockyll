<?php

namespace Mockyll;

/**
 * Contains all method records.
 *
 * @author Radek Ježdík
 * @internal
 */
class MockRecords {

    /** @var MethodRecord[] */
    private $records = array();


    public function record($method, $arguments) {
        $record = new MethodRecord($method, $arguments);
        $this->records[$method] = $record;
        return $record;
    }


    public function play($method, $arguments) {
        if(!isset($this->records[$method])) {
            throw new UnexpectedStateException("Method $method() was not expected to be called");
        }
        return $this->records[$method]->play($arguments);
    }


    public function playPartial($mock, $method, $arguments) {
        if(!isset($this->records[$method])) {
            return $this->executeRealMethod($mock, $method, $arguments);
        }
        return $this->records[$method]->play($arguments);
    }


    private function executeRealMethod($mock, $method, $arguments) {
        $mock->__calledPartials[$method] = true;

        $returnVal = call_user_func_array(array($mock, $method), $arguments);

        unset($mock->__calledPartials[$method]);

        return $returnVal;
    }


    public function verify() {
        foreach($this->records as $rec) {
            $rec->verify();
        }
    }


    public function clear() {
        $this->records = array();
    }

}