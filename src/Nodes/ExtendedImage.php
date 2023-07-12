<?php

namespace Vuetik\VuetikLaravel\Nodes;

use Tiptap\Nodes\Image;

class ExtendedImage extends Image
{
    public static $name = 'extendedImage';

    public function addAttributes()
    {
        return [
            ...parent::addAttributes(),
            'class' => [
                'default' => '',
            ],
            'data-image-id' => [
                'default' => null,
            ],
            'width' => [
                'default' => null,
            ],
            'height' => [
                'default' => null,
            ],
        ];
    }
}
