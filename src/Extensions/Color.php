<?php

namespace Vuetik\VuetikLaravel\Extensions;

use DOMNode;
use Tiptap\Core\Extension;
use Tiptap\Utils\InlineStyle;

class Color extends Extension
{
    public static $name = 'color';

    public function addGlobalAttributes(): array
    {
        return [
            [
                'types' => ['textStyle'],
                'attributes' => [
                    'color' => [
                        'default' => null,
                        'parseHTML' => fn(DOMNode $DOMNode) => InlineStyle::getAttribute($DOMNode, "color") ?? "",
                        'renderHTML' => function($attributes) {
                            if(!property_exists($attributes, "color") || !$attributes->color) {
                                return null;
                            }

                            return [
                                'style' => "color: {$attributes->color}"
                            ];
                        }
                    ]
                ]
            ]
        ];
    }
}
