#!/usr/bin/env php
<?php

require_once dirname(__FILE__)."/../vendor/autoload.php";

use JStormes\AWSwrapper\LazyLoad;
use JStormes\AWSwrapper\Logs;


$Logs = new Logs([
    'profile' => 'default',
    'region' => 'us-west-2',
    'version' => 'latest',
    'logGroup' => "testGroup3",
    'logStreamPrefix' => "testStream3",
    'system' => 'system',
    'application' => str_replace('.php','',basename(__FILE__))
]);

$Logs->debug("This is an stock debug message");
$Logs->info("This is an stock info message");
$Logs->monitor("This is an stock monitor message");
$Logs->warning("This is an stock  warning message");
$Logs->error("This is an stock error message");
$Logs->critical("This is an stock critical message");

// Dynamic severity demo.
$Logs->test("this is dynamic severity message");
$Logs->jstomres("This is also a dynamic severity message");