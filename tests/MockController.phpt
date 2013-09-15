<?php

use Mockyll\MockController;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


class ClassToMock {
    
    public function sayHello($str) {
        throw new \Exception("Should not execute this method");
    }
    
}


class TestClass {
    
    private $other;
    
    function __construct(ClassToMock $other) {
        $this->other = $other;
    }

    public function sayHelloWorld() {
        return $this->other->sayHello('world');
    }
    
}


test(function() {
    $mockCtrl = new MockController();
    
    $mock = $mockCtrl->mock('ClassToMock');
    
    $mock->sayHello('world')
            ->returns('hello world!');
    
    $mockCtrl->play(function() use($mock) {
        Assert::same('hello world!', $mock->sayHello('world'));
    });
});


test(function() {
    $mockCtrl = new MockController();
    
    $mock = $mockCtrl->mock('ClassToMock');
    
    $mock->sayHello('world')
            ->returns('hello world!');
    
    $testObj = new TestClass($mock);
    
    $mockCtrl->play(function() use($testObj) {
        Assert::same('hello world!', $testObj->sayHelloWorld());
    });
});

// a simplest test of mock
test(function() {
    $mockCtrl = new MockController();
    
    $mock = $mockCtrl->mock();
    
    
    $mock->sayHello('world')
            ->returns('hello world!');
    
    $mockCtrl->play(function() use($mock) {
        Assert::same('hello world!', $mock->sayHello('world'));
    });
    
    
    
    $mock->sayHello('world')
            ->throws(new \InvalidArgumentException("wrong"));
    
    $mockCtrl->play(function() use($mock) {
        Assert::exception(function() use($mock) {
            $mock->sayHello('world');
        }, 'InvalidArgumentException', 'wrong');
    });
});