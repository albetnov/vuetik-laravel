<?php

namespace Vuetik\VuetikLaravel\Factories;

class ImageFactory
{
    public readonly string $id;

    public readonly string $width;

    public readonly string $height;

    public function __construct(array $image)
    {
        $this->id = $image['id'];
        $this->width = $image['width'];
        $this->height = $image['height'];
    }
}
