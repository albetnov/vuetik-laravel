<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\artisan;
use Vuetik\VuetikLaravel\Models\VuetikImages;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('vuetik-laravel.storage.disk', 'images');
    config()->set('vuetik-laravel.storage.path', 'imgs/');
    $images = VuetikImages::factory(5)->create();
    Storage::fake('images');

    foreach ($images as $image) {
        $image->created_at = now()->subDays(5);
        Storage::disk('images')->put(
            'imgs/'.$image->file_name,
            UploadedFile::fake()->image($image->file_name)->getContent()
        );
        $image->save();
    }
});

it('deleted all seeded images (due to expiry being -2 days and all of data is -5 days)', function () {
    artisan('purge:unused-images')
        ->expectsOutput('Unused Image Purged!')
        ->doesntExpectOutputToContain('Failed deleting file')
        ->assertExitCode(0);

    expect(VuetikImages::first())->toBeNull();
});

it('failed deleting images (not exist in storage)', function () {
    $ulid = '0189532d-ddf7-8ea0-fe08-0e324f20a10b';

    // create one image entry without the data.
    VuetikImages::insert([
        'file_name' => 'baiklah.jpg',
        'status' => VuetikImages::PENDING,
        'created_at' => now()->subDays(3),
        'id' => $ulid,
    ]);

    artisan('purge:unused-images')
        ->expectsOutputToContain("Failed deleting file (not exist) id: $ulid")
        ->assertExitCode(0);
});

it('uses argument instead of default config', function () {
    Storage::fake('fake');

    $ulid = '0189532d-ddf7-8ea0-fe08-0e324f20a10b';

    VuetikImages::insert([
        'file_name' => 'baiklah.jpg',
        'status' => VuetikImages::PENDING,
        'created_at' => now()->subDays(10),
        'id' => $ulid,
    ]);

    Storage::disk('fake')->put(
        'fake/baiklah.jpg',
        UploadedFile::fake()->image('baiklah.jpg'
        )->getContent()
    );

    artisan('purge:unused-images', [
        '--disk' => 'fake',
        '--after' => '-7 days',
        '--path' => 'fake/',
        '--show-log' => true,
    ])->expectsOutputToContain("Purged: $ulid")
        ->assertExitCode(0);

    // only one data (-7 days) should be purged. That said, the remaining in which it just -5 days should remain.
    expect(VuetikImages::first())->not->toBeNull();
});
