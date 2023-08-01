<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Exception\NotReadableException;
use Vuetik\VuetikLaravel\EncodedImageUpload;
use Vuetik\VuetikLaravel\Models\VuetikImages;
use Vuetik\VuetikLaravel\Utils;

uses(RefreshDatabase::class);

it('successfully save image', function () {
    $image = json_decode(file_get_contents(__DIR__.'/examples/image_base64.json'), true)['content'][0]['attrs']['src'];

    Storage::fake('images');
    $encodedImageUpload = new EncodedImageUpload($image);
    $uploadedImage = $encodedImageUpload->save(
        throwOnFail: false,
        saveFormat: 'png',
        disk: 'images',
        quality: 50
    );

    expect($uploadedImage)->toBeInstanceOf(VuetikImages::class)
        ->and($uploadedImage->status)->toEqual(VuetikImages::PENDING)
        ->and($uploadedImage->file_name)->toContain('png');

    Storage::disk('images')->assertExists(Utils::parseStoragePath().$uploadedImage->file_name);
});

it('failed save image (invalid base64)', function () {
    $encodedImageUpload = new EncodedImageUpload('fake base64');
    $uploadedImage = $encodedImageUpload->save(
        throwOnFail: false,
        saveFormat: 'png',
        disk: 'images',
        quality: 50
    );

    expect($uploadedImage)->toBeFalse();
});

it('failed save image (with exception)', function () {
    $encodedImageUpload = new EncodedImageUpload('fake base64');
    $uploadedImage = $encodedImageUpload->save(
        throwOnFail: true,
        saveFormat: 'png',
        disk: 'images',
        quality: 50
    );

    expect($uploadedImage)->toThrow(NotReadableException::class);
})->throws(NotReadableException::class);

it('encoded to jpg', function () {
    $image = json_decode(file_get_contents(__DIR__.'/examples/image_base64.json'), true)['content'][0]['attrs']['src'];

    Storage::fake('images');
    $encodedImageUpload = new EncodedImageUpload($image);
    $uploadedImage = $encodedImageUpload->save(
        throwOnFail: true,
        saveFormat: 'jpg',
        disk: 'images',
        quality: 50
    );

    expect($uploadedImage->file_name)->toContain('jpg');
    Storage::disk('images')->assertExists(Utils::parseStoragePath().$uploadedImage->file_name);
});

it("failed encode image (validation fails)", function () {
    config()->set('vuetik-laravel.max_upload_size', 5); // set it really low in purpose of failing
    $image = json_decode(file_get_contents(__DIR__.'/examples/image_base64.json'), true)['content'][0]['attrs']['src'];
    $encodedImageUpload = new EncodedImageUpload($image);
    Storage::fake('images');

    $result = $encodedImageUpload->save(true, "jpg", "images", 50);
    expect($result)->toBeFalse();
});
