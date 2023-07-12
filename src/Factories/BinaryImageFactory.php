<?php

namespace Vuetik\VuetikLaravel\Factories;

class BinaryImageFactory
{
    public function __construct(readonly string $uniqidName,readonly string $content, readonly int $width, readonly int $height)
    {

    }
}
