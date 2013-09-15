<?php

use Mockyll\MethodRecord;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/../src/Match.php';


class TestClass {
    
}

// tests matching of single object as argument and returning false for matching
test(function() {
    $record = new MethodRecord('sayHello', array());
    $record->match('world');

    Assert::exception(function() use($record) {
        $record->play(array(new TestClass));
    }, 'Mockyll\UnexpectedStateException');
});


// tests matching multiple arguments
test(function() {
    $record = new MethodRecord('sayHello', array());
    $record->match(array('hello'), Mockyll\Match::isOf('TestClass'));

    $record->play(array(
        array('hello'),
        new TestClass()
    ));
});
