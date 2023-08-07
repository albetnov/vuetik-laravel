<?php

namespace Vuetik\VuetikLaravel\Tests;

use Vuetik\VuetikLaravel\Models\VuetikImages;

class Helpers
{
    public static function fakeVuetikImage(bool $noProps = false)
    {
        VuetikImages::insert([
            'id' => 'e4b9da63-cf1e-45d2-b967-2c8e44591c9e',
            'file_name' => 'default.jpg',
            'props' => $noProps ? null : json_encode([
                'width' => 564,
                'height' => 564,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
            'status' => VuetikImages::PENDING,
        ]);
    }

    public static function getBase64ImgSrc(): string
    {
        return json_decode(file_get_contents(__DIR__.'/src/examples/image_base64.json'), true)['content'][0]['attrs']['src'];
    }
}
