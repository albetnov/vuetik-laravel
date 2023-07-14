<?php

namespace Vuetik\VuetikLaravel\Exceptions;

class TwitterParseException extends \Exception
{
    public function __construct(
        public readonly string $url,
        string $message,
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
