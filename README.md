Mockyll
=======
Nice and simple mocking library for PHP based on GMock for Groovy.

It offers:
 - simple usage
 - no complicated setup
 - class and interface mocking based on inheritance
 - partial mocks
 
Install
-------

Install the library using composer:

```sh
$ composer require redhead/mockyll:@dev
```

That's it!

Simple usage
------------

```php
$mockController = new Mockyll\MockController;

$myClassMock = $mockController->mock('MyClass');
$myClassMock->greet('world')->returns('hello world!');

$mockController->play(function() use ($myClassMock) {
  $myClassMock->greet('world'); // == 'hello world!'
}
```
