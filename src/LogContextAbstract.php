<?php


namespace JStormes\AWSwrapper;


abstract class LogContextAbstract
{
    public $__type;

    function __construct(array $config) {
        $this->__type = __CLASS__;
    }
}