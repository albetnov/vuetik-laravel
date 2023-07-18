<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;
use Vuetik\VuetikLaravel\Exceptions\TwitterParseException;
use Vuetik\VuetikLaravel\Factories\ImageFactory;
use Vuetik\VuetikLaravel\Models\VuetikImages;
use Vuetik\VuetikLaravel\Utils;
use Vuetik\VuetikLaravel\VuetikLaravel;

beforeEach(function () {
    Route::setRoutes(new \Illuminate\Routing\RouteCollection());
});

it('Registered route successfully', function () {
    \Vuetik\VuetikLaravel\Facades\VuetikLaravel::routes();
    expect(Route::getRoutes()->getRoutesByMethod(['POST'])['POST']['upload-img'])
        ->toBeInstanceOf(\Illuminate\Routing\Route::class);
});

it('Rendered underlined content', function () {
    $html = '<p><u>fewfawe</u></p>';
    $payload = file_get_contents(__DIR__.'/examples/underline.json');

    $content = VuetikLaravel::parseJson($payload);

    expect($content->html)->toEqual($html);
});

it('failed parsing json and throw exception', function () {
    expect(VuetikLaravel::parseJson('not valid'))->toThrow(\InvalidArgumentException::class);
})->throws(\InvalidArgumentException::class);

it('Rendered extended image content', function () {
    $payload = file_get_contents(__DIR__.'/examples/image.json');
    $content = VuetikLaravel::parseJson($payload);

    expect($content->html)->toContain('img', 'src')
        ->and($content->images)->toBeArray()
        ->and($content->images[0])->toBeInstanceOf(ImageFactory::class)
        ->and($content->images[0]->id)->toBeString()
        ->and($content->images[0]->width)->toEqual('564')
        ->and($content->images[0]->height)->toEqual('564');
});

it('Rendered extended image content (base64)', function () {
    $payload = file_get_contents(__DIR__.'/examples/image_base64.json');
    $content = VuetikLaravel::parseJson($payload);

    $image = VuetikImages::first();

    expect($content->html)->toContain('img', 'src')
        ->and($content->images)->toBeArray()
        ->and($content->images[0])->toBeInstanceOf(ImageFactory::class)
        ->and($content->images[0]->id)->toEqual($image->id);
});

it('Rendered Text Style content', function () {
    $html = '<p><span>feawfawe</span></p>';
    $payload = file_get_contents(__DIR__.'/examples/textStyle.json');

    $content = VuetikLaravel::parseJson($payload);

    expect($content->html)->toEqual($html);
});

it('Rendered Highlighted text content', function () {
    $payload = '<p><mark data-color="#fde68a" style="background-color: #fde68a;">ewfewafaw</mark></p>';

    $content = VuetikLaravel::parse($payload);

    expect($content->html)->toEqual($payload);
});

it('Rendered Aligned Text', function () {
    $payloads = ['<p style="text-align: center;">fewfew</p>', '<h1 style="text-align: center;">Another one</h1>'];

    foreach ($payloads as $payload) {
        $content = VuetikLaravel::parse($payload);

        expect($content->html)->toEqual($payload);
    }
});

it('Rendered task list', function () {
    $payload = file_get_contents(__DIR__.'/examples/taskList.json');

    $html = <<<'html'
    <ul data-type="taskList"><li data-checked="true" data-type="taskItem"><label><input type="checkbox" checked="checked"><span></span></label><div><p>fewafaewfaew</p></div></li></ul>
    html;

    $content = VuetikLaravel::parseJson($payload);

    expect($content->html)->toEqual(trim($html));
});

it('Rendered link successfully', function () {
    $payload = '<p><a target="_blank" rel="noopener noreferrer nofollow" href="https://google.com">dfadas</a></p>';

    $content = VuetikLaravel::parse($payload);

    expect($content->html)->toEqual($payload);
});

it('Rendered color successfully', function () {
    $payload = '<p><span style="color: #bd2828;">feawfawe</span></p>';

    $content = VuetikLaravel::parse($payload);

    expect($content->html)->toEqual($payload);
});

it('Rendered CodeBlock successfully (Shiki Highlighted)', function () {
    $payload = file_get_contents(__DIR__.'/examples/codeBlock.json');

    $content = VuetikLaravel::parseJson($payload);

    expect($content->html)->toContain('pre', 'shiki', 'code', 'span');
});

it('Rendered Youtube Embed', function () {
    $payload = file_get_contents(__DIR__.'/examples/youtube.json');
    $html = '<iframe src="https://www.youtube.com/watch?v=WFmmS_tqV-w" width="640" height="480"></iframe>';
    $content = VuetikLaravel::parseJson($payload);

    expect($content->html)->toEqual($html);
});

it('Rendered window embed', function () {
    $payload = file_get_contents(__DIR__.'/examples/embed.json');
    $html = '<iframe src="http://localhost:5173" allowfullscreen="1"></iframe>';
    $content = VuetikLaravel::parseJson($payload);

    expect($content->html)->toEqual($html);
});

it('Rendered Twitter Embed', function () {
    $payload = file_get_contents(__DIR__.'/examples/twitter.json');

    $content = VuetikLaravel::parseJson($payload);
    $html = file_get_contents(__DIR__.'/examples/twitter_result.html');

    expect($content->html)->toEqual(trim($html));
});

it('Failed rendered twitter embed due to invalid id', function () {
    $payload = file_get_contents(__DIR__.'/examples/twitter_invalid.json');

    $content = VuetikLaravel::parseJson($payload);

    expect($content->html)->toEqual('<p>Failed Fetching Twitter</p>');
});

it('Failed rendered twitter embed due to invalid id (with exception)', function () {
    $payload = file_get_contents(__DIR__.'/examples/twitter_invalid.json');

    $content = VuetikLaravel::parseJson($payload, [
        'twitter' => [
            'throwOnFail' => true,
        ],
    ]);

    expect($content)->toThrow(TwitterParseException::class);
})->throws(TwitterParseException::class);

it('Skipped string strategy invalid image due to invalid', function () {
    $payload = file_get_contents(__DIR__.'/examples/image_base64_invalid.json');

    $content = VuetikLaravel::parseJson($payload);

    expect($content->images)->toBeEmpty();
});

it('Check if passed parameters on Image Encode is inherited', function () {
    config()->set('vuetik-laravel.base64_to_storage.quality', 90);

    $image = file_get_contents(__DIR__.'/examples/image_base64.json');

    $mockedImage = Mockery::mock('overload:'.Image::class);
    $mockedImage->shouldReceive('encode')
        ->once()
        ->with(\config('vuetik-laravel.base64_to_storage.save_format'), \config('vuetik-laravel.base64_to_storage.quality'))
        ->andReturnSelf();

    VuetikLaravel::parseJson($image);
})->skip('This test have to be run in separate process or isolation from global state, Tried using native PHP Unit but I cant get it work');

it('saved image according to the config', function () {
    Storage::fake('images');
    config()->set('vuetik-laravel.storage.disk', 'images');
    config()->set('vuetik-laravel.base64_to_storage.save_format', 'jpg');

    $image = file_get_contents(__DIR__.'/examples/image_base64.json');

    $content = VuetikLaravel::parseJson($image);

    $db = VuetikImages::find($content->images[0]->id);

    expect($db->file_name)->toContain('jpg');

    Storage::disk('images')->assertExists(Utils::parseStoragePath().$db->file_name);
});

it('persist both width and height', function () {
    $image = file_get_contents(__DIR__.'/examples/image.json');

    $content = VuetikLaravel::parseJson($image, [
        'image' => [
            'persistWidth' => true,
            'persistHeight' => true,
        ],
    ]);

    expect($content->html)->toContain('width', 'height', 'src', 'img');
});
