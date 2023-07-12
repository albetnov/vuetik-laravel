<?php

namespace Vuetik\VuetikLaravel\Nodes;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;
use Vuetik\VuetikLaravel\Utils;

class Youtube extends Node
{
    public static $name = "youtube";

    public function addAttributes(): array
    {
        return [
            'src' => [
                'default' => null,
            ],
            'start' => [
                'default' => 0
            ],
            'width' => [
                'default' => 640,
            ],
            'height' => [
                'default' => 480
            ]
        ];
    }

    public function parseHTML(): array
    {
        return [
            [
                'tag' => 'div[data-youtube-video] iframe'
            ]
        ];
    }

    public function renderHTML($node, array $HTMLAttributes = []): array
    {
        return ['iframe', Utils::toStringAttributes($HTMLAttributes)];
    }
}
