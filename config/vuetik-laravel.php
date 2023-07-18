<?php

return [
    'max_upload_size' => 2048,
    'storage' => [
        'disk' => 'local',
        'path' => 'images/',
    ],
    'table' => 'vuetik_images',
    'image_vendor_route' => '/img',
    'glide' => [
        'enable' => true,
        'sign_key' => env('APP_KEY'),
        'img_modifiers' => [],
    ],
    'purge_after' => '-2 days',
    'base64_to_storage' => [
        'enable' => true,
        'save_format' => 'png',
        'quality' => 100,
    ],
];
