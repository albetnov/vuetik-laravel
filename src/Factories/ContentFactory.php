<?php

namespace Vuetik\VuetikLaravel\Factories;

class ContentFactory {
    public function __construct(readonly string $html, readonly ImageFactory $image)
    {
    }
}
