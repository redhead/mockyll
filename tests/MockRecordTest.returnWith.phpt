<?php

use Mockyll\MethodRecord;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


// tests returning value computed in the callback function
test(function() {
    $record = new MethodRecord('square', array(3));
    $record->returnWith(function($arg) {
        return $arg * $arg;
    });
    
    Assert::same(9, $record->play(array(3)));
});