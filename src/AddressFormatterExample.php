<?php


namespace JStormes\AWSwrapper;


class AddressFormatterExample implements FormatterInterface
{
    public function isCogent(string $severity, string $msg, $context): bool {

        $valid = true;
        if (!isset($context['address'])) $valid = false;
        if (!isset($context['city'])) $valid = false;
        if (!isset($context['state'])) $valid = false;
        if (!isset($context['zip'])) $valid = false;

        return $valid;
    }

    public function format(string $severity, string $msg, $context) : string {

        $payload= [
            'severity' => $severity,
            'msg' => $msg,
            'context' => $context
        ];

        return json_encode($payload, JSON_PRETTY_PRINT);
    }
}