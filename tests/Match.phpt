<?php

use Tester\Assert;
use Mockyll\Match as m;

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/../src/Match.php';

Assert::true(m::is("foo")->match("foo"));
Assert::false(m::is("foo")->match("bar"));

Assert::true(m::isNot("foo")->match("bar"));
Assert::false(m::isNot("foo")->match("foo"));

Assert::true(m::has("bar")->match("forbarbaz"));
Assert::false(m::has("bar")->match("foo"));

Assert::true(m::hasNot("bar")->match("foo"));
Assert::false(m::hasNot("bar")->match("foobarbaz"));

Assert::true(m::notNull()->match(""));
Assert::true(m::notNull()->match(0));
Assert::true(m::notNull()->match(new stdClass()));
Assert::false(m::notNull()->match(NULL));

Assert::true(m::isOf("stdClass")->match(new stdClass()));


Assert::true(m::with(function($arg) {
					return $arg == "foo";
				})->match("foo"));

Assert::true(m::with(function($arg) {
					// tests no return
				})->match("foo"));
				
Assert::true(m::with(function($arg) {
					return true;
				})->match("foo"));
				
Assert::true(m::with(function($arg) {
					return "foo";
				})->match("foo"));
				
Assert::true(m::with(function($arg) {
					return '';
				})->match("foo"));
				
Assert::false(m::with(function($arg) {
					return false;
				})->match("foo"));