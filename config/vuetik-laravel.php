<?php

return [
    'max_upload_size' => 2048,
    'storage' => [
        'disk' => 'local',
        'path' => storage_path("public/vuetik-laravel")
    ],
    'table' => 'vuetik_images',
    'image_vendor_route' => '/img',
    'glide' => [
        'enable' => true,
        'sign_key' => env("APP_KEY"),
        'img_modifiers' => [],
    ]
];
