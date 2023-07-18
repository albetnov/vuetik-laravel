<?php

namespace Vuetik\VuetikLaravel\Factories;

/**
 * @property string $html
 * @property array<ImageFactory> $images
 */
class ContentFactory
{
    public function __construct(readonly string $html, readonly array $images)
    {
    }
}
