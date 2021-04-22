<?php

require(dirname(__DIR__) . '/vendor/autoload.php');
include_once(dirname(__DIR__) . '/src/support/helpers.php');

$kernel = new App\Kernel();
$kernel->bootstrap();
