<?php

return [
    'sizes' => [
        'thumbnail' => ['width' => '70', 'height' => '70'],
        'card-medium' => ['width' => '400', 'height' => '400'],
        'card-large' => ['width' => '620', 'height' => '620'],

    ],
    'jpeg_compression' => 80,
    'original_path' => 'images/avatar/originals',
    'variant_pattern' => 'images/avatar/variants/%sx%s',
    'disk' => env('MEDIA_DISK', 'public'),
];
