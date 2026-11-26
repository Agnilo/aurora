<?php

return [
    'fallback' => 'images/welcome/main2.png',
    'canvas' => [1254, 1254],
    'defaults' => [
        'body' => 'default',
        'hair' => 'pink',
        'outfit' => 'default',
        'expression' => 'neutral',
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
        'expression' => ['neutral' => 'images/mascot/expressions/neutral.png'],
        'accessory' => ['none' => null],
    ],
    'customizable' => ['hair', 'outfit'],
    'swatches' => [
        'hair' => ['pink' => '#edaca5', 'brown' => '#a66f4d', 'blonde' => '#e9c27f'],
        'outfit' => ['default' => '#d1b7e2', 'mint' => '#a9cfb6', 'rose' => '#dda0b2'],
    ],
    'poses' => [],
];
