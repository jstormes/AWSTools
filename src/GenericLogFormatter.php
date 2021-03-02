<?php


namespace JStormes\AWSwrapper;


class GenericLogFormatter implements FormatterInterface
{

    public function isCogent(string $severity, string $msg, $context): bool
    {
        return true;
    }

    public function format(string $severity, string $msg, $context): string
    {

        if (is_array($context)) {
            $payload= [
                'severity' => $severity,
                'msg' => $msg,
                'context' => $context
            ];

            return json_encode($payload, JSON_PRETTY_PRINT);
        }

        if (is_subclass_of($context, LogContextAbstract::class)) {

            $payload= [
                'severity' => $severity,
                'msg' => $msg,
                'context' => $context
            ];

            return json_encode($payload, JSON_PRETTY_PRINT);
        }

        throw new \Exception("Unknown context type.");

    }
}