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
        'max_file_size' => (env('FILE_UPLOAD_MAX_SIZE', 10) * 1024 * 1024), // Convert MB to bytes
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png', 
            'image/gif',
            'image/webp',
            'image/avif'
        ],
        'allowed_extensions' => explode(',', env('FILE_UPLOAD_ALLOWED_TYPES', 'jpg,jpeg,png,gif,webp,avif'))
    ],

    'thumbnails' => [
        'avatar' => [
            'width' => env('FILE_UPLOAD_THUMB_WIDTH', 200),
            'height' => env('FILE_UPLOAD_THUMB_HEIGHT', 200),
            'quality' => 85
        ],
        'pet_photo' => [
            'width' => env('FILE_UPLOAD_THUMB_WIDTH', 400),
            'height' => env('FILE_UPLOAD_THUMB_HEIGHT', 400),
            'quality' => 85
        ]
    ],

    'storage' => [
        // Use environment variable to switch between local and cloud storage
        'disk' => env('FILESYSTEM_DISK', 'public'),
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
    ],

    /*
    |--------------------------------------------------------------------------
    | DigitalOcean Spaces Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to DigitalOcean Spaces integration
    |
    */
    'digital_ocean' => [
        'cdn_enabled' => env('DO_SPACES_CDN_ENABLED', false),
        'cdn_endpoint' => env('DO_SPACES_CDN_ENDPOINT'),
        'public_url_template' => env('DO_SPACES_PUBLIC_URL', 'https://{bucket}.{region}.digitaloceanspaces.com'),
    ]

]; 