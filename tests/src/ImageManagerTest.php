<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Vuetik\VuetikLaravel\ImageManager;
use Vuetik\VuetikLaravel\Models\VuetikImages;
use Vuetik\VuetikLaravel\Utils;
use Vuetik\VuetikLaravel\VuetikLaravel;

uses(RefreshDatabase::class);

it('store preupload from give array of id', function () {
    $results = VuetikImages::factory(10)->create();

    $ids = collect($results)->pluck('id')->merge(['invalid-id', 'fake-id'])->toArray();

    ImageManager::storePreuploads($ids);

    $results = $results->fresh();

    foreach ($results as $result) {
        expect($result->status)->toBe(VuetikImages::ACTIVE);
    }
});

it('store all binaries photos', function () {
    $payload = file_get_contents(__DIR__.'/examples/image_base64.json');

    $content = VuetikLaravel::parseJson($payload);

    Storage::fake('images');

    ImageManager::store($content, function (string $fileName, string $fileContent) {
        Storage::disk('images')->put($fileName, $fileContent);
    });

    Storage::disk('images')->assertExists($content->image->binaries[0]->uniqidName);

    $db = VuetikImages::first();

    expect($db->file_name)->toEqual($content->image->binaries[0]->uniqidName)
        ->and($db->status)->toBe(VuetikImages::ACTIVE);
});

it('store all binaries photos from config disk', function () {
    $payload = file_get_contents(__DIR__.'/examples/image_base64.json');

    $content = VuetikLaravel::parseJson($payload);

    Storage::fake('images');

    config()->set('vuetik-laravel.storage.disk', 'images');
    config()->set('vuetik-laravel.storage.path', 'img');

    ImageManager::store($content);

    Storage::disk('images')->assertExists(Utils::parseStoragePath().$content->image->binaries[0]->uniqidName);
});

it('store all pre-upload photo', function () {
    $payload = file_get_contents(__DIR__.'/examples/image.json');

    VuetikImages::insert([
        'id' => 'e4b9da63-cf1e-45d2-b967-2c8e44591c9e',
        'file_name' => 'example.png',
        'created_at' => now(),
        'updated_at' => now(),
        'status' => VuetikImages::PENDING,
    ]);

    $content = VuetikLaravel::parseJson($payload);

    ImageManager::store($content);

    $img = VuetikImages::find($content->image->ids[0]->id);

    expect($img->file_name)->toBe('example.png')
        ->and($img->status)->toBe(VuetikImages::ACTIVE);
});

it('Can generate glide url (with props)', function () {
    $img = VuetikImages::create([
        'file_name' => 'example.png',
        'status' => VuetikImages::ACTIVE,
        'props' => [
            'width' => 500,
            'height' => 700,
        ],
    ]);

    config()->set('vuetik-laravel.glide.sign_key', 'abc');

    $url = ImageManager::getGlideUrl(image: $img, additionalProps: [
        'blur' => 5,
    ]);

    expect($url)->toContain($img->file_name, 'w=500', 'h=700', 's=', 'blur=5');
});

it('can generate glide url (without props)', function () {
    $img = VuetikImages::create([
        'file_name' => 'example.png',
        'status' => VuetikImages::ACTIVE,
    ]);

    config()->set('vuetik-laravel.glide.sign_key', 'abc');
    $url = ImageManager::getGlideUrl($img, '/custom');

    expect($url)->toContain('custom', $img->file_name, 's=');
});
