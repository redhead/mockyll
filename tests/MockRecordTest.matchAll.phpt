<?php

use Mockyll\MethodRecord;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


class TestClass {
    
}

// tests matching of single object as argument and returning false for matching
test(function() {
	$testObj = new TestClass;
	
    $record = new MethodRecord('sayHello', array('world'));
    $record->matchAll(function(TestClass $obj) use($testObj) {
		Assert::same($testObj, $obj);
        return false;
    });

    Assert::exception(function() use($record, $testObj) {
        $record->play(array($testObj));
    }, 'Mockyll\UnexpectedStateException');
});


// tests matching multiple arguments
test(function() {
    $record = new MethodRecord('sayHello', array('world'));
    $record->matchAll(function($array, $obj) {
        Assert::same(array('hello'), $array);
        Assert::true($obj instanceof TestClass);
    });

    $record->play(array(
        array('hello'),
        new TestClass()
    ));
});
