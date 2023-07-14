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

        $image = [
            'ids' => [],
            'binaries' => [],
        ];

        $editor->setContent($content)->descendants(function (&$node) use (&$image) {
            if ($node->type === 'extendedImage') {
                $attrs = $node->attrs;

                $ids = [];
                $binaries = [];

                if (Str::startsWith($attrs->src, 'data:image/png;base64,')) {
                    $binaries['file'] = $attrs->src;
                }

                if ($attrs->{'data-image-id'}) {
                    $ids['id'] = $attrs->{'data-image-id'};
                    unset($attrs->{'data-image-id'});
                }

                if ($attrs->width) {
                    isset($ids['id']) && $ids['width'] = $attrs->width;
                    isset($binaries['file']) && $binaries['width'] = $attrs->width;
                    unset($attrs->width);
                }

                if ($attrs->height) {
                    isset($ids['id']) && $ids['height'] = $attrs->height;
                    isset($binaries['file']) && $binaries['height'] = $attrs->height;
                    unset($attrs->height);
                }

                if (! empty($ids)) {
                    $image['ids'][] = $ids;
                }

                if (! empty($binaries)) {
                    $image['binaries'][] = $binaries;
                }
            }
        });

        return new ContentFactory($editor->getHTML(),
            new ImageFactory($image['ids'], $image['binaries'])
        );
    }
}
