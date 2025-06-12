<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the file upload system including validation rules,
    | thumbnail sizes, and storage paths.
    |
    */

    'validation' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB in bytes
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png', 
            'image/gif',
            'image/webp',
            'image/avif'
        ],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif']
    ],

    'thumbnails' => [
        'avatar' => [
            'width' => 200,
            'height' => 200,
            'quality' => 85
        ],
        'pet_photo' => [
            'width' => 400,
            'height' => 400,
            'quality' => 85
        ]
    ],

    'storage' => [
        'disk' => 'public',
        'directories' => [
            'avatars' => 'avatars',
            'pets' => 'pets',
            'temp' => 'temp'
        ]
    ],

    'cleanup' => [
        'temp_file_lifetime' => 24, // hours
        'schedule_time' => '02:00',
        'enable_auto_cleanup' => true
    ]

]; 