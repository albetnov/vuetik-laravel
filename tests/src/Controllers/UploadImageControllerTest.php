<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use League\Glide\Signatures\SignatureFactory;
use Symfony\Component\HttpFoundation\Response;
use Vuetik\VuetikLaravel\Models\VuetikImages;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Make a fake routing
    \Illuminate\Support\Facades\Route::get('/', function () {
    })->name('img');

    config()->set('vuetik-laravel.max_upload_size', 2048);
    config()->set('vuetik-laravel.storage.disk', 'fake');
    config()->set('vuetik-laravel.storage.path', 'images');
    config()->set('vuetik-laravel.image_vendor_route', 'img');

    // Register the VueTik Routes
    \Vuetik\VuetikLaravel\VuetikLaravel::routes();
});

it('failed uploading image (validation: required, string, image, 2mb)', function () {
    $this->postJson(route('upload-img'))->assertUnprocessable();

    $this->postJson(route('upload-img'), [
        'image' => 'string',
    ])->assertUnprocessable();

    $this->post(route('upload-img'), [
        'image' => UploadedFile::fake()->create('fake.json', 1024, 'application/json'),
    ], [
        'Accept' => 'application/json',
    ])->assertUnprocessable();

    $this->post(route('upload-img'), [
        'image' => UploadedFile::fake()->image('test.jpg')->size(9999),
    ], [
        'Accept' => 'application/json',
    ])->assertUnprocessable();
});

it('uploaded image successfully (without glide)', function () {
    config()->set('vuetik-laravel.glide.enable', false);
    Storage::fake('fake');

    $file = UploadedFile::fake()->image('fake.jpg');
    $result = $this->post(route('upload-img'), [
        'image' => $file,
    ])->assertJson(fn (AssertableJson $json) => $json
        ->where('status', Response::HTTP_OK)
        ->where('error', false)
        ->has('image', fn (AssertableJson $json) => $json->hasAll(['url', 'id'])
            ->etc())
        ->etc())
        ->json();

    $image = VuetikImages::first();

    expect($image)->not->toBeNull()
        ->and($image->id)->toEqual($result['image']['id']);

    Storage::disk('fake')->assertExists('images/'.$file->hashName());
});

it('uploaded image successfully (with glide)', function () {
    $signKey = 'abcdefg';

    config()->set('vuetik-laravel.glide.enable', true);
    config()->set('vuetik-laravel.glide.sign_key', $signKey);
    config()->set('vuetik-laravel.glide.img_modifiers', [
        'blur' => '5',
    ]);

    Storage::fake('fake');

    $file = UploadedFile::fake()->image('fake.jpg');

    $result = $this->post(route('upload-img'), [
        'image' => $file,
    ])->assertJson(fn (AssertableJson $json) => $json
        ->where('status', Response::HTTP_OK)
        ->where('error', false)
        ->has('image', fn (AssertableJson $json) => $json->hasAll(['url', 'id'])
            ->etc())
        ->etc())
        ->json();

    $url = parse_url($result['image']['url']);
    parse_str($url['query'], $queryParam);
    SignatureFactory::create($signKey)->validateRequest($url['path'], $queryParam);
});
