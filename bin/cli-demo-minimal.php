#!/usr/bin/env php
<?php

require_once dirname(__FILE__)."/../vendor/autoload.php";

use JStormes\AWSwrapper\Logs;


$Log = new Logs([
    'profile' => 'default',
    'region' => 'us-west-2',
    'version' => 'latest',
    'logStreamPrefix' => "Minimal",
    'system' => 'test3'
]);

$Log->debug("This is an Minimal debug message");
$Log->info("This is an Minimal info message");
$Log->monitor("This is an Minimal monitor message");
$Log->warning("This is an Minimal  warning message");
$Log->error("This is an Minimal error message");
$Log->critical("This is an Minimal critical message");
