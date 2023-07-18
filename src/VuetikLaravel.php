<?php

namespace Vuetik\VuetikLaravel;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tiptap\Editor;
use Tiptap\Extensions\StarterKit;
use Tiptap\Extensions\TextAlign;
use Tiptap\Marks\Highlight;
use Tiptap\Marks\Link;
use Tiptap\Marks\TextStyle;
use Tiptap\Marks\Underline;
use Tiptap\Nodes\CodeBlockShiki;
use Tiptap\Nodes\Table;
use Tiptap\Nodes\TableCell;
use Tiptap\Nodes\TableHeader;
use Tiptap\Nodes\TableRow;
use Tiptap\Nodes\TaskItem;
use Tiptap\Nodes\TaskList;
use Vuetik\VuetikLaravel\Controllers\UploadImageController;
use Vuetik\VuetikLaravel\Extensions\Color;
use Vuetik\VuetikLaravel\Factories\ContentFactory;
use Vuetik\VuetikLaravel\Factories\ImageFactory;
use Vuetik\VuetikLaravel\Nodes\Embed;
use Vuetik\VuetikLaravel\Nodes\ExtendedImage;
use Vuetik\VuetikLaravel\Nodes\Twitter;
use Vuetik\VuetikLaravel\Nodes\Youtube;

class VuetikLaravel
{
    public static function routes(): void
    {
        Route::post('/upload-img', UploadImageController::class)->name('upload-img');
    }

    public static function parseJson(string $json, array $options = []): ContentFactory
    {
        $content = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Payload JSON is not valid');
        }

        return self::parse($content, $options);
    }

    public static function parse(string|array $content, array $options = []): ContentFactory
    {
        $editor = new Editor([
            'extensions' => [
                new StarterKit([
                    'codeBlock' => false,
                ]),
                new CodeBlockShiki([
                    'guessLanguage' => true,
                ]),
                new Underline(),
                new ExtendedImage(),
                new TextStyle(),
                new Highlight([
                    'multicolor' => true,
                ]),
                new TextAlign([
                    'types' => ['heading', 'paragraph'],
                ]),
                new TaskList(),
                new TaskItem(),
                new Link(),
                new Table(),
                new TableCell(),
                new TableRow(),
                new TableHeader(),
                new Color(),
                new Youtube(),
                new Twitter([
                    'throwOnFail' => Arr::get($options, 'twitter.throwOnFail') ?? false,
                ]),
                new Embed(),
            ],
        ]);

        $images = [];

        $editor->setContent($content)->descendants(function (&$node) use (&$images, $options) {
            if ($node->type === 'extendedImage') {
                $attrs = $node->attrs;

                $image = [];

                if (Str::startsWith($attrs->src, 'data:image/png;base64,')) {
                    if (Arr::get($options, 'image.base64_to_disk', config('vuetik-laravel.base64_to_storage.enable', true))) {
                        $encodedUpload = new EncodedImageUpload($attrs->src);
                        $uploadedImage = $encodedUpload->save(
                            throwOnFail: Arr::get($options, 'image.throwOnFail', false),
                            saveFormat: Arr::get($options, 'image.saveFormat', config('vuetik-laravel.base64_to_storage.save_format', 'png')),
                            disk: Arr::get($options, 'image.disk', config('vuetik-laravel.storage.disk', 'local')),
                            quality: Arr::get($options, 'image.quality', config('vuetik-laravel.base64_to_storage.quality', 100))
                        );

                        if ($uploadedImage) {
                            $attrs->src = $uploadedImage->file_name;
                            $image['id'] = $uploadedImage->id;
                        }
                    }
                }

                if ($attrs->{'data-image-id'}) {
                    $image['id'] = $attrs->{'data-image-id'};
                    unset($attrs->{'data-image-id'});
                }

                if ($attrs->width) {
                    isset($image['id']) && $image['width'] = $attrs->width;

                    if (!Arr::get($options, "image.persistWidth", false)) unset($attrs->width);
                }

                if ($attrs->height) {
                    isset($image['id']) && $image['height'] = $attrs->height;

                    if (!Arr::get($options, 'image.persistHeight', false)) unset($attrs->height);
                }

                if (!empty($image)) $images[] = new ImageFactory($image);
            }
        });

        return new ContentFactory(
            html: $editor->getHTML(),
            images: $images
        );
    }
}
