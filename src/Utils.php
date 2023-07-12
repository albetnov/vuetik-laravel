<?php

namespace Vuetik\VuetikLaravel;

class Utils
{
    public static function toStringAttributes(array $attributes): array
    {
        return collect($attributes)->map(fn ($item) => (string) $item)->toArray();
    }
}
