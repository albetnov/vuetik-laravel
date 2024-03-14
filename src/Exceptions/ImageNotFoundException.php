<?php

namespace Vuetik\VuetikLaravel\Exceptions;

class ImageNotFoundException extends \Exception
{
    public function __construct(public readonly string $id, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct("Image with $id not found. Please retry re-upload.", $code, $previous);
    }
}
