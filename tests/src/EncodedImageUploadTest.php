<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Exception\NotReadableException;
use Vuetik\VuetikLaravel\EncodedImageUpload;
use Vuetik\VuetikLaravel\Models\VuetikImages;
use Vuetik\VuetikLaravel\Tests\Helpers;
use Vuetik\VuetikLaravel\Utils;

uses(RefreshDatabase::class);

it('successfully save image', function () {
    $image = Helpers::getBase64ImgSrc();

    Storage::fake('images');
    $encodedImageUpload = new EncodedImageUpload($image);
    $uploadedImage = $encodedImageUpload->save(
        throwOnFail: false,
        saveFormat: 'png',
        disk: 'images',
        quality: 50,
        autoSave: true
    );

    expect($uploadedImage)->toBeInstanceOf(VuetikImages::class)
        ->and($uploadedImage->status)->toEqual(VuetikImages::ACTIVE)
        ->and($uploadedImage->file_name)->toContain('png');

    Storage::disk('images')->assertExists(Utils::parseStoragePath().$uploadedImage->file_name);
});

it('image should stay pending when autoSave is off', function () {
    $image = Helpers::getBase64ImgSrc();

    Storage::fake('images');
    $encodedImageUpload = new EncodedImageUpload($image);
    $uploadedImage = $encodedImageUpload->save(
        throwOnFail: false,
        saveFormat: 'png',
        disk: 'images',
        quality: 50,
        autoSave: false
    );

    expect($uploadedImage->status)->toEqual(VuetikImages::PENDING);
});

it('failed save image (invalid base64)', function () {
    $encodedImageUpload = new EncodedImageUpload('fake base64');
    $uploadedImage = $encodedImageUpload->save(
        throwOnFail: false,
        saveFormat: 'png',
        disk: 'images',
        quality: 50,
        autoSave: true
    );

    expect($uploadedImage)->toBeFalse();
});

it('failed save image (with exception)', function () {
    $encodedImageUpload = new EncodedImageUpload('fake base64');
    $uploadedImage = $encodedImageUpload->save(
        throwOnFail: true,
        saveFormat: 'png',
        disk: 'images',
        quality: 50,
        autoSave: true
    );

    expect($uploadedImage)->toThrow(NotReadableException::class);
})->throws(NotReadableException::class);

it('encoded to jpg', function () {
    $image = Helpers::getBase64ImgSrc();

    Storage::fake('images');
    $encodedImageUpload = new EncodedImageUpload($image);
    $uploadedImage = $encodedImageUpload->save(
        throwOnFail: true,
        saveFormat: 'jpg',
        disk: 'images',
        quality: 50,
        autoSave: true
    );

    expect($uploadedImage->file_name)->toContain('jpg');
    Storage::disk('images')->assertExists(Utils::parseStoragePath().$uploadedImage->file_name);
});

it('failed encode image (validation fails)', function () {
    config()->set('vuetik-laravel.max_upload_size', 5); // set it really low in purpose of failing
    $image = Helpers::getBase64ImgSrc();
    $encodedImageUpload = new EncodedImageUpload($image);
    Storage::fake('images');

    $result = $encodedImageUpload->save(true, 'jpg', 'images', 50, true);
    expect($result)->toBeFalse();
});
