<?php

namespace Vuetik\VuetikLaravel\Factories;

class BinaryImageFactory extends ImageTypeFactory
{
    public function __construct(readonly string $uniqidName, readonly string $content, ?int $width, ?int $height)
    {
        parent::__construct($width, $height);
    }
}
