<?php

namespace Vuetik\VuetikLaravel;

use Illuminate\Support\Str;
use Vuetik\VuetikLaravel\Models\VuetikImages;

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

        // Windows Compatibility Path Separator
        if (Str::contains($path, '/')) {
            $path = Str::replace('/', DIRECTORY_SEPARATOR, $path);
        }

        if (Str::contains($path, '\\')) {
            $path = Str::replace('\\', DIRECTORY_SEPARATOR, $path);
        }

        if (Str::charAt($path, Str::length($path) - 1) !== DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }

        return $path;
    }

    public static function getImageValidationRules(): array
    {
        return ['required', 'image', 'max:'.config('vuetik-laravel.max_upload_size', 2048)];
    }

    public static function getBase64Image(string $encodedBase64): string
    {
        return base64_decode(Str::after($encodedBase64, 'data:image/png;base64,'));
    }

    public static function getImageUrl(VuetikImages $image): string
    {
        if (config('vuetik-laravel.glide.enable')) {
            return ImageManager::getGlideUrl($image);
        }

        return url(config('vuetik-laravel.image_vendor_route'), $image->file_name);
    }
}
