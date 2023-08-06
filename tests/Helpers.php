<?php

namespace Vuetik\VuetikLaravel\Tests;

use Vuetik\VuetikLaravel\Models\VuetikImages;

class Helpers
{
    public static function fakeVuetikImage()
    {
        VuetikImages::insert([
            'id' => 'e4b9da63-cf1e-45d2-b967-2c8e44591c9e',
            'file_name' => 'default.jpg',
            'props' => json_encode([
                'width' => 564,
                'height' => 564
            ]),
            'created_at' => now(),
            'updated_at' => now(),
            'status' => VuetikImages::PENDING
        ]);
    }
}
