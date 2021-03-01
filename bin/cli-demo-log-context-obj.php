#!/usr/bin/env php
<?php

require_once dirname(__FILE__)."/../vendor/autoload.php";

use JStormes\AWSwrapper\Logs;


$Log = new Logs([
    'profile' => 'default',
    'region' => 'us-west-2',
    'version' => 'latest',
    'logStreamPrefix' => "Context",
    'system' => 'test3'
]);

$context = new \JStormes\AWSwrapper\LogContextGeneric();
$context->test = "this is a test";

$Log->warning("context test", $context);

