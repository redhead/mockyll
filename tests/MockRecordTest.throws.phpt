<?php

use Mockyll\MethodRecord;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


// tests throws the given exception when called
test(function() {
    $record = new MethodRecord('sayHello', array());
    $record->throws(new \InvalidArgumentException("No parameter"));
    
    Assert::exception(function() use($record) {
        $record->play(array());
    }, '\InvalidArgumentException', "No parameter");
});


// tests throws the given exception when called
test(function() {
    $record = new MethodRecord('sayHello', array());
    $record->throws(new \InvalidArgumentException("No parameter"));
    
    Assert::exception(function() use($record) {
        $record->play(array());
    }, 'InvalidArgumentException', "No parameter");
});


// tests that return value callback is not called when expection is to be thrown
test(function() {
    $record = new MethodRecord('sayHello', array());
    $record->returnWith(function() {
        Assert::fail("Return callback was called before throwing exception");
    });
    $record->throws(new \InvalidArgumentException("No parameter"));
    
    Assert::exception(function() use($record) {
        $record->play(array());
    }, 'InvalidArgumentException', "No parameter");
});