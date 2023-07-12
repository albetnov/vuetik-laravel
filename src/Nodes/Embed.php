<?php

namespace Vuetik\VuetikLaravel\Nodes;

use Tiptap\Core\Node;
use Vuetik\VuetikLaravel\Utils;

class Embed extends Node
{
    public static $name = 'embed';

    public function parseHTML()
    {
        return [
            [
                'tag' => 'iframe',
            ],
        ];
    }

    public function addAttributes(): array
    {
        return [
            'src' => [
                'default' => null,
            ],
            'frameborder' => [
                'default' => 0,
            ],
            'allowfullscreen' => [
                'default' => null,
            ],
        ];
    }

    public function renderHTML($node, array $HTMLAttributes = []): array
    {
        return ['iframe', Utils::toStringAttributes($HTMLAttributes)];
    }
}
