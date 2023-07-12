<?php

namespace Vuetik\VuetikLaravel\Nodes;

use Illuminate\Support\Facades\Http;
use Tiptap\Core\Node;
use Tiptap\Editor;
use Tiptap\Utils\InlineStyle;
use Vuetik\VuetikLaravel\Utils;

class Twitter extends Node
{
    public static $name = "twitter";

    public function parseHTML(): array
    {
        return [
            [
                'tag' => 'div[data-twitter-id][data-twitter-url]'
            ]
        ];
    }

    public function addAttributes(): array
    {
        return [
            'data-twitter-id' => [
                'default' => null,
                'parseHTML' => fn($DOMNode) => InlineStyle::getAttribute($DOMNode, "data-twitter-id")
            ],
            'data-twitter-url' => [
                'default' => null,
                'parseHTML' => fn($DOMNode) => InlineStyle::getAttribute($DOMNode, "data-twitter-url")
            ]
        ];
    }

    public function renderHTML($node, $HTMLAttributes = []): ?array
    {
        $url = $node->attrs->{'data-twitter-url'};

        $response = Http::get('https://publish.twitter.com/oembed', [
            'url' => $url,
            'omit_script' => 1
        ]);

        $result = $response->json();

        return [
            'content' => trim($result['html']) ?? "<p>Failed Fetching Twitter</p>"
        ];
    }
}
