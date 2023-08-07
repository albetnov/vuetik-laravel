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

    /**
     * return an array containing id of images
     *
     * @return array<string>
     */
    public function getImagesArray(): array
    {
        return collect($this->images)->map(fn (ImageFactory $imageFactory) => $imageFactory->id)->toArray();
    }
}
