<?php

use Mockyll\MockController;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


class ClassToPartiallyMock {

    public function sayHelloWorld() {
        return $this->sayHello('world');
    }
    
    public function sayHello($str) {
        throw new \Exception("Should not execute this method");
    }
    
}


test(function() {
    $mockCtrl = new MockController();
    
    $mock = $mockCtrl->partial('ClassToPartiallyMock');
    
    $mock->sayHello('world')
            ->returns('hello world!');
    
    $mockCtrl->play(function() use($mock) {
        Assert::same('hello world!', $mock->sayHelloWorld());
    });
});


test(function() {
    $mockCtrl = new MockController();
    
    $mock = $mockCtrl->partial('ClassToPartiallyMock', array(
        'sayHello' => function($str) {
            return "hello $str!";
        }
    ));
    
    $mockCtrl->play(function() use($mock) {
        Assert::same('hello world!', $mock->sayHelloWorld());
    });
});