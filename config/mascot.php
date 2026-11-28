<?php

return [
    'fallback' => 'images/welcome/main2.png',
    'canvas' => [1254, 1254],
    'defaults' => [
        'background' => 'default',
        'body' => 'default',
        'hair' => 'pink',
        'outfit' => null,
        'top' => 'default',
        'bottom' => 'jeans',
        'shoes' => 'default',
        'eye_color' => 'brown',
        'expression' => 'neutral',
        'blush' => 'peach',
        'accessory' => 'none',
    ],
    'variants' => [
        'body' => ['default' => 'images/mascot/body/default.png'],
        'hair' => [
            'pink' => ['back' => 'images/mascot/hair/pink-back.png', 'front' => 'images/mascot/hair/pink-front.png'],
            'brown' => ['back' => 'images/mascot/hair/brown-back.png', 'front' => 'images/mascot/hair/brown-front.png'],
            'blonde' => ['back' => 'images/mascot/hair/blonde-back.png', 'front' => 'images/mascot/hair/blonde-front.png'],
        ],
        'outfit' => [
            'default' => 'images/mascot/outfits/default.png',
            'mint' => 'images/mascot/outfits/mint.png',
            'rose' => 'images/mascot/outfits/rose.png',
        ],
        'shoes' => ['default' => 'images/mascot/shoes/default.png'],
        'expression' => ['neutral' => 'images/mascot/expressions/neutral-adaptive.png'],
        'accessory' => ['none' => null],
    ],
    'customizable' => ['hair', 'top', 'bottom', 'outfit', 'shoes', 'body', 'accessory', 'background', 'eye_color', 'expression', 'blush'],
    'swatches' => [
        'hair' => ['pink' => '#edaca5', 'brown' => '#a66f4d', 'blonde' => '#e9c27f'],
        'outfit' => ['default' => '#d1b7e2', 'mint' => '#a9cfb6', 'rose' => '#dda0b2'],
        'shoes' => ['default' => '#f3ebe0'],
    ],
    'poses' => [],
];
