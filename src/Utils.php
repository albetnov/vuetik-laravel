<?php

namespace Vuetik\VuetikLaravel;

use Illuminate\Support\Str;

class Utils
{
    public static function toStringAttributes(array $attributes): array
    {
        return collect($attributes)->map(fn ($item) => (string) $item)->toArray();
    }

    public static function parseStoragePath(string $path = null): string
    {
        if (! $path) {
            $path = config('vuetik-laravel.storage.path');
        }

        if (Str::charAt($path, Str::length($path) - 1) !== DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }

        return $path;
    }
}
