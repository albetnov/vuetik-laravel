<?php

namespace Vuetik\VuetikLaravel;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

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

    /**
     * @throws Exception
     */
    public static function validateBufferImage(string $buffer, bool $throwOnFail = false): bool
    {
        try {
            $format = config('vuetik-laravel.base64_save_format', 'png');

            $image = Image::make($buffer)
                ->encode($format); // force to png in order to encode the iamge to a png file.

            // store image at temp folder
            $temporaryImgFilePath = tempnam(sys_get_temp_dir(), 'temp_vuetik_imgs').".$format";
            $image->save($temporaryImgFilePath);

            $payload = new UploadedFile(
                path: $temporaryImgFilePath,
                originalName: $image->basename,
                mimeType: $image->mime(),
                test: true // test has to be set to true to avoid is_uploaded_file validation.
            );

            $isValid = Validator::make([
                'image' => $payload,
            ], [
                'image' => Utils::getImageValidationRules(),
            ])->fails();

            unlink($temporaryImgFilePath); // unlink the image upon finished validated

            return $isValid;
        } catch (\Exception $e) {
            if ($throwOnFail) {
                throw $e;
            } // rethrow if on fail set to true.

            return true;
        }
    }
}
