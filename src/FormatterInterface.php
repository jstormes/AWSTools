<?php


namespace JStormes\AWSwrapper;


interface FormatterInterface
{
    public function isCogent(string $severity, string $msg, $context): bool;
    public function format(string $severity, string $msg, $context) : string;
}