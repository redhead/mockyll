<?php

use Mockyll\MethodRecord;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


function createRecord() {
    return new MethodRecord('test', array());
}

function getMessage($expected, $end) {
    return "Method test() is to be called $expected, but " . $end;
}

// tests default behaviour, doesn't throw exception - call count is correct
test(function() {
    $record = createRecord();
    
    $record->play(array());
    $record->verify();
    
    $record->play(array());
    $record->verify();
});


// tests exception message when zero call count expected
test(function() {
    $record = createRecord()->never();
    
    $record->verify();
    $record->play(array());
    $record->play(array());
    
    Assert::exception(function() use($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException', getMessage('0 times', 'was 2 times'));
});


// tests exception message when zero call count expected
test(function() {
    $record = createRecord()->once();
    
    Assert::exception(function() use($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException', getMessage('1 time', "it wasn't called at all"));
});


// tests exception message when zero call count expected
test(function() {
    $record = createRecord()->times(1, 2);
    
    $record->play(array());
    $record->play(array());
    $record->play(array());
    
    Assert::exception(function() use($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException', getMessage('1 to 2 times', "was 3 times"));
});


// tests exception message when zero call count expected
test(function() {
    $record = createRecord()->atMost(2);
    
    $record->play(array());
    $record->play(array());
    $record->play(array());
    
    Assert::exception(function() use($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException', getMessage('at most 2 times', "was 3 times"));
});


// tests exception message when zero call count expected
test(function() {
    $record = createRecord()->atLeast(2);
    
    $record->play(array());
    
    Assert::exception(function() use($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException', getMessage('at least 2 times', "was 1 time"));
});