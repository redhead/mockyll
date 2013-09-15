<?php

use Mockyll\MethodRecord;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


// tests call with return value
test(function() {
    $record = new MethodRecord('sayHello', array('world'));
    $record->returns('hello, world!');
    
    Assert::same('hello, world!',   $record->play(array('world')));
});


// tests returning the given values with each call with overflowing
test(function() {
    $record = new MethodRecord('sayHello', array('world'));
    $record->returns('hello, world!', 'hi, world!');
    
    Assert::same('hello, world!',   $record->play(array('world')));
    Assert::same('hi, world!',      $record->play(array('world')));
    
    Assert::same('hello, world!',   $record->play(array('world')));
    Assert::same('hi, world!',      $record->play(array('world')));
});


// tests throws exception when no parameter passed to returns()
test(function() {
    Assert::exception(function() {
        $record = new MethodRecord('sayHello', array('world'));
		$record->returns();
    }, 'InvalidArgumentException');
});