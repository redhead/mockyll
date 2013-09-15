<?php

require __DIR__ . '/../vendor/autoload.php';


if(extension_loaded('xdebug')) {
    Tester\CodeCoverage\Collector::start(__DIR__ . '/coverage.dat');
}


function test(\Closure $function) {
    $function();
}
