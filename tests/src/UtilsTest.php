<?php

use Illuminate\Support\Str;
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
    expect(Str::charAt($path, Str::length($path) - 1))->toBe('/');

    $path = Utils::parseStoragePath('test/');
    expect(Str::charAt($path, Str::length($path) - 1))->toBe('/');
});
