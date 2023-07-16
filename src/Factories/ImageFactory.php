<?php

namespace Vuetik\VuetikLaravel\Factories;

use Illuminate\Support\Str;
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
            ->map(function (array $item) {
                $decodedImage = Utils::getBase64Image($item['file']);

                if (Utils::validateBufferImage($decodedImage)) {
                    return null;
                }

                $fileName = Str::ulid()->toRfc4122().'.png';

                return new BinaryImageFactory(
                    uniqidName: $fileName,
                    content: $decodedImage,
                    width: $item['width'] ?? null,
                    height: $item['height'] ?? null
                );
            })
            ->filter(fn (?BinaryImageFactory $item) => $item !== null)
            ->toArray();
    }
}
