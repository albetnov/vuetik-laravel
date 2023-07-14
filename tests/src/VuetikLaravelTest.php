<?php

use Illuminate\Support\Facades\Route;
use Vuetik\VuetikLaravel\Exceptions\TwitterParseException;
use Vuetik\VuetikLaravel\Factories\BinaryImageFactory;
use Vuetik\VuetikLaravel\Factories\IdImageFactory;
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
        ->and($content->image->ids)->toBeArray()
        ->and($content->image->ids[0])->toBeInstanceOf(IdImageFactory::class);
});

it('Rendered extended image content (base64)', function () {
    $payload = file_get_contents(__DIR__.'/examples/image_base64.json');
    $content = VuetikLaravel::parseJson($payload);

    expect($content->html)->toContain('img', 'src')
        ->and($content->image->binaries)->toBeArray()
        ->and($content->image->binaries[0])->toBeInstanceOf(BinaryImageFactory::class)
        ->and($content->image->binaries[0]->content)->toBeString();
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
