<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Image;
use Vuetik\VuetikLaravel\Exceptions\ImageNotFoundException;
use Vuetik\VuetikLaravel\Exceptions\TwitterParseException;
use Vuetik\VuetikLaravel\Factories\ImageFactory;
use Vuetik\VuetikLaravel\Models\VuetikImages;
use Vuetik\VuetikLaravel\Tests\Helpers;
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
    Helpers::fakeVuetikImage();
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

    $content = VuetikLaravel::parseJson($payload, [
        'twitter' => [
            'throwOnFail' => false,
        ],
    ]);

    expect($content->html)->toEqual('<div data-twitter-url="https://twitter.com/livepixelart/status/iajfioewjfioawe?s=20" data-twitter-id="1678516215709585408"><p>Failed Fetching Twitter</p></div>');
});

it('Failed rendered twitter embed due to invalid id (with exception)', function () {
    $payload = file_get_contents(__DIR__.'/examples/twitter_invalid.json');

    $content = VuetikLaravel::parseJson($payload);

    expect($content)->toThrow(TwitterParseException::class);
})->throws(TwitterParseException::class);

it('Skipped string strategy invalid image', function () {
    $payload = file_get_contents(__DIR__.'/examples/image_base64_invalid.json');

    $content = VuetikLaravel::parseJson($payload, [
        'image' => [
            'throwOnFail' => false,
        ],
    ]);

    expect($content->images)->toBeEmpty();
});

it('throw error on string strategy invalid image', function () {
    $payload = file_get_contents(__DIR__.'/examples/image_base64_invalid.json');

    expect(VuetikLaravel::parseJson($payload))->toThrow(NotReadableException::class);
})->throws(NotReadableException::class);

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

it('persist important image elements (width, height, id)', function () {
    $image = file_get_contents(__DIR__.'/examples/image.json');
    Helpers::fakeVuetikImage();

    $content = VuetikLaravel::parseJson($image);

    expect($content->html)->toContain('width', 'height', 'src', 'img', 'data-image-id');
});

it('rendered the glide url', function () {
    $image = file_get_contents(__DIR__.'/examples/image.json');
    Helpers::fakeVuetikImage(noProps: true);

    $content = VuetikLaravel::parseJson($image);

    expect($content->html)->toContain('img', 'w=564', 'h=564', 's=');
});

it('rendered normal url', function () {
    config()->set('vuetik-laravel.glide.enable', false);
    Helpers::fakeVuetikImage();

    $image = file_get_contents(__DIR__.'/examples/image.json');

    $content = VuetikLaravel::parseJson($image);
    expect($content->html)->toContain('default.jpg')->not->toContain('w=564', 'h=564', 's=');
});

it('throw exception due to image not found', function () {
    $image = file_get_contents(__DIR__.'/examples/image.json');
    $content = VuetikLaravel::parseJson($image);

    expect($content)->toThrow(ImageNotFoundException::class);
})->throws(ImageNotFoundException::class);

it('ignore the not found with appended class prefix', function () {
    $image = file_get_contents(__DIR__.'/examples/image.json');

    $content = VuetikLaravel::parseJson($image, [
        'image' => [
            'throwOnFail' => false,
        ],
    ]);
    expect($content->html)->toContain('vuetik__failed__img');
});

it("wrapped twitter in it's div container", function () {
    $twitter = file_get_contents(__DIR__.'/examples/twitter.json');

    $content = VuetikLaravel::parseJson($twitter);

    expect($content->html)->toContain('div', 'data-twitter-id=', 'data-twitter-url');
});

it('getTwitterAttrs return passed parameter arrays', function () {
    $exception = new TwitterParseException(['id' => 1, 'url' => 'test']);
    expect($exception->getTwitterAttrs())->toBeArray()->toHaveKeys(['id', 'url'])
        ->and($exception->getTwitterAttrs()['id'])->toEqual(1)
        ->and($exception->getTwitterAttrs()['url'])->toEqual('test');
});

it('get all parsed image ids using getImagesArray', function () {
    $image = file_get_contents(__DIR__.'/examples/image.json');
    Helpers::fakeVuetikImage();

    $content = VuetikLaravel::parseJson($image);
    expect($content->getImagesArray())->toBeArray()->toContain('e4b9da63-cf1e-45d2-b967-2c8e44591c9e');
});
