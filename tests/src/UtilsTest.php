<?php

use Illuminate\Support\Str;
use Intervention\Image\Exception\NotReadableException;
use Vuetik\VuetikLaravel\Utils;

it('Parse all attributes to a string', function () {
    $attrs = Utils::toStringAttributes([
        'a' => 1,
        'b' => 2,
        'c' => 3,
    ]);

    collect($attrs)->values()
        ->each(fn ($val) => expect($val)->toBeString());
});

it('Append slash on string', function () {
    config()->set('vuetik-laravel.storage.path', 'test');

    $path = Utils::parseStoragePath();
    expect(Str::charAt($path, Str::length($path) - 1))->toBe(DIRECTORY_SEPARATOR);

    $path = Utils::parseStoragePath('test/');
    expect(Str::charAt($path, Str::length($path) - 1))->toBe(DIRECTORY_SEPARATOR);
});

it('Decoded html src base64 format successfully', function () {
    $imgExample = json_decode(file_get_contents(__DIR__.'/examples/image_base64.json'), true);
    $src = $imgExample['content'][0]['attrs']['src'];

    $decodedString = Utils::getBase64Image($src);
    $encodedString = 'data:image/png;base64,'.base64_encode($decodedString);

    expect($encodedString)->toEqual($src);
});

it('validated image successfully', function () {
    $imgExample = json_decode(file_get_contents(__DIR__.'/examples/image_base64.json'), true);
    $src = $imgExample['content'][0]['attrs']['src'];

    $decodedString = Utils::getBase64Image($src);

    expect(Utils::validateBufferImage($decodedString))->toBeFalse();
});

it('failed validating invalid image', function () {
    // this is a json containing "data": "test"
    $base64 = 'ewoiZGF0YSI6ICJ0ZXN0Igp9';

    expect(Utils::validateBufferImage(base64_decode($base64), true))
        ->toThrow(NotReadableException::class);
})->throws(NotReadableException::class);
