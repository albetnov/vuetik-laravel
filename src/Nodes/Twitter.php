<?php

namespace Vuetik\VuetikLaravel\Nodes;

use Illuminate\Support\Facades\Http;
use Tiptap\Core\Node;
use Tiptap\Utils\InlineStyle;
use Vuetik\VuetikLaravel\Exceptions\TwitterParseException;

class Twitter extends Node
{
    public static $name = 'twitter';

    private array $twitter = [
        'id' => null,
        'url' => null
    ];

    public function addOptions(): array
    {
        return [
            'throwOnFail' => false,
        ];
    }

    public function parseHTML(): array
    {
        return [
            [
                'tag' => 'div[data-twitter-id][data-twitter-url]',
            ],
        ];
    }

    public function addAttributes(): array
    {
        return [
            'data-twitter-id' => [
                'default' => null,
                'parseHTML' => fn ($DOMNode) => InlineStyle::getAttribute($DOMNode, 'data-twitter-id'),
            ],
            'data-twitter-url' => [
                'default' => null,
                'parseHTML' => fn ($DOMNode) => InlineStyle::getAttribute($DOMNode, 'data-twitter-url'),
            ],
        ];
    }

    private function wrapOnTwitterDiv(string $innerHTML): string {
        return "<div data-twitter-url=\"{$this->twitter['url']}\" data-twitter-id=\"{$this->twitter['id']}\">{$innerHTML}</div>";
    }

    /**
     * @throws TwitterParseException
     */
    public function renderHTML($node, $HTMLAttributes = []): ?array
    {
        $this->twitter['url'] = $node->attrs->{'data-twitter-url'};
        $this->twitter['id'] = $node->attrs->{'data-twitter-id'};

        $response = Http::get('https://publish.twitter.com/oembed', [
            'url' => $this->twitter['url'],
            'omit_script' => 1,
        ]);

        $result = $response->json();

        if ($response->status() !== 200) {
            if ($this->options['throwOnFail']) {
                throw new TwitterParseException($this->twitter);
            }

            return [
                'content' => $this->wrapOnTwitterDiv('<p>Failed Fetching Twitter</p>'),
            ];
        }

        return [
            'content' => $this->wrapOnTwitterDiv(trim($result['html'])),
        ];
    }
}
