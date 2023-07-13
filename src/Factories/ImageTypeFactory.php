<?php

namespace Vuetik\VuetikLaravel\Factories;

abstract class ImageTypeFactory
{
    public readonly bool $hasProps;

    public function __construct(public readonly ?int $width, public readonly ?int $height)
    {
        $this->hasProps = $this->width || $this->height;
    }
}
