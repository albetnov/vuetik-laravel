<?php

namespace Vuetik\VuetikLaravel\Factories;

use Illuminate\Http\File;
use Illuminate\Support\Str;

class ImageFactory
{
    /** @var array<IdImageFactory> */
    public readonly array $ids;
    /** @var array<BinaryImageFactory> */
    public readonly array $binaries;

    public function __construct(array $ids, array $binaries)
    {
        $this->ids = collect($ids)
            ->map(fn($item) => new IdImageFactory($item['id'], $item['width'], $item['height']))
            ->toArray();

        $this->binaries = collect($binaries)
            ->map(function($item) {
                $decodedData = base64_decode($item['file']);

                $fileName = Str::ulid()->toRfc4122() . ".png";

                return new BinaryImageFactory($fileName, $decodedData, $item['width'], $item['height']);
            })
            ->toArray();
    }
}
