<?php

namespace Vuetik\VuetikLaravel\Factories;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;
use Vuetik\VuetikLaravel\Utils;

class ImageFactory
{
    /** @var array<IdImageFactory> */
    public readonly array $ids;

    /** @var array<BinaryImageFactory> */
    public readonly array $binaries;

    public function __construct(array $ids, array $binaries)
    {
        $this->ids = collect($ids)
            ->map(fn ($item) => new IdImageFactory($item['id'], $item['width'], $item['height']))
            ->toArray();

        $this->binaries = collect($binaries)
            ->filter(function (array $item) {
                try {
                    $binaryFile = base64_decode(Str::after($item['file'], 'data:image/png;base64,'));
                    $image = Image::make($binaryFile);

                    // store image at temp folder
                    $temporaryImgFilePath = tempnam(sys_get_temp_dir(), 'temp_vuetik_imgs').$image->extension;
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
                    ])->valid();

                    unlink($temporaryImgFilePath); // unlink the image upon finished validated

                    return $isValid;
                } catch (\Exception $e) {
                    return false;
                }
            })
            ->map(function (array $item) {
                $decodedData = base64_decode($item['file']);
                $fileName = Str::ulid()->toRfc4122().'.png';

                return new BinaryImageFactory(
                    $fileName,
                    $decodedData,
                    $item['width'] ?? null,
                    $item['height'] ?? null
                );
            })
            ->filter(fn (?BinaryImageFactory $item) => $item !== null)
            ->toArray();
    }
}
