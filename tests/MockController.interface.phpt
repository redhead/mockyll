<?php

use Mockyll\MockController;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


interface InterfaceToMock {
    
    public function sayHello($str);
    
}


class TestClass {
    
    private $other;
    
    function __construct(InterfaceToMock $other) {
        $this->other = $other;
    }

    public function sayHelloWorld() {
        return $this->other->sayHello('world');
    }
    
}


test(function() {
    $mocker = new MockController();
    
    $mock = $mocker->mock('InterfaceToMock');
    
    $mock->sayHello('world')
            ->returns('hello world!');
    
    $testObj = new TestClass($mock);
    
    $mocker->play(function() use($testObj) {
        Assert::same('hello world!', $testObj->sayHelloWorld());
    });
});