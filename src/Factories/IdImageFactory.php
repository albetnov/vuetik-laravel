<?php

namespace Vuetik\VuetikLaravel\Factories;

class IdImageFactory extends ImageTypeFactory
{
    public function __construct(readonly string $id, ?int $width, ?int $height)
    {
        parent::__construct($width, $height);
    }
}
