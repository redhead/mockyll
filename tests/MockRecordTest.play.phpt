<?php

use Mockyll\MethodRecord;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


// tests simple call with arguments
test(function() {
    $record = new MethodRecord('sayHello', array('world'));
	$record->play(array('world'));
});


// tests call with unexpected arguments throws exception
test(function() {
    Assert::exception(function() {
		$record = new MethodRecord('sayHello', array('world'));
		$record->play(array('underworld'));
	}, 'Mockyll\UnexpectedStateException');
});


// tests call with unexpected number arguments throws exception
test(function() {
    Assert::exception(function() {
		$record = new MethodRecord('sayHello', array('world'));
		$record->play(array('from', 'hell'));
	}, 'Mockyll\UnexpectedStateException');
});