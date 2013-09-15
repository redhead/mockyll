<?php

use Mockyll\MethodRecord;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


function createRecord() {
    return new MethodRecord('sayHello', array());
}


// tests unspecified number of calls (default)
test(function() {
    $record = createRecord();
    
    Assert::exception(function() use ($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException');
    
    $record->play(array());
    $record->verify();
    $record->play(array());
    $record->verify();
    $record->play(array());
    $record->verify();
});


// tests expected count equals zero (never())
test(function() {
    $record = createRecord();
    $record->never();
    
    $record->verify();
    $record->play(array());
    
    Assert::exception(function() use ($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException');
});


// tests expected count equals one (once())
test(function() {
    $record = createRecord();
    $record->once();
    
    Assert::exception(function() use ($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException');
    
    $record->play(array());
    $record->verify();
    $record->play(array());
    
    Assert::exception(function() use ($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException');
});


// tests expected count equals two (twice())
test(function() {
    $record = createRecord();
    $record->twice();
    
    $record->play(array());
    
    Assert::exception(function() use ($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException');
    
    $record->play(array());
    $record->verify();
    $record->play(array());
    
    Assert::exception(function() use ($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException');
});


// tests expected count equals equals given number (times())
test(function() {
    $record = createRecord();
    $record->times(3);
    
    $record->play(array());
    $record->play(array());
    $record->play(array());
    
    $record->play(array());
    
    Assert::exception(function() use ($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException');
});


// tests expected count interval
test(function() {
    $record = createRecord();
    $record->times(1, 2);
    
    Assert::exception(function() use ($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException');
    
    $record->play(array());
    $record->verify();
    $record->play(array());
    $record->verify();
    $record->play(array());
    
    Assert::exception(function() use ($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException');
});


// tests at most
test(function() {
    $record = createRecord();
    $record->atMost(2);
    
    $record->verify();
    $record->play(array());
    $record->verify();
    $record->play(array());
    $record->verify();
    $record->play(array());
    
    Assert::exception(function() use ($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException');
});


// tests at least
test(function() {
    $record = createRecord();
    $record->atLeast(2);
    
    Assert::exception(function() use ($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException');
    
    $record->play(array());
    
    Assert::exception(function() use ($record) {
        $record->verify();
    }, 'Mockyll\UnexpectedStateException');
    
    $record->play(array());
    $record->verify();
    $record->play(array());
    $record->verify();
});


// tests invalid parameters for times() throw exception
test(function() {
    Assert::exception(function() {
        createRecord()->times();
    }, '\InvalidArgumentException');
    
    Assert::exception(function() {
        createRecord()->times(-1);
    }, '\InvalidArgumentException');
    
    Assert::exception(function() {
        createRecord()->times(-2, -1);
    }, '\InvalidArgumentException');
    
    Assert::exception(function() {
        createRecord()->times(1, 2, 3);
    }, '\InvalidArgumentException');
    
    Assert::exception(function() {
        createRecord()->times('foo');
    }, '\InvalidArgumentException');
    
    Assert::exception(function() {
        createRecord()->times('foo', 'bar');
    }, '\InvalidArgumentException');
});