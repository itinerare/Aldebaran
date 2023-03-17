<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Files
    |--------------------------------------------------------------------------
    |
    | This is a list of files that will appear in the image uploader
    | section of the admin panel, to be used in the site and its layout.
    |
    */

    'avatar'     => [
        'name'        => 'Avatar',
        'description' => 'Personal avatar. Used for meta tags.',
        'filename'    => 'avatar',
    ],

    'watermark'  => [
        'name'        => 'Watermark',
        'description' => 'Personal watermark, used to automatically watermark images. Should be opaque (opacity will be adjusted in processing).',
        'filename'    => 'watermark',
    ],

    'sidebar_bg' => [
        'name'        => 'Sidebar Background',
        'description' => 'Background used for the sidebar. Optional.',
        'filename'    => 'sidebar_bg',
    ],

    'logo'      => [
        'name'        => 'Logo',
        'description' => 'Logo. Used primarily for PayPal invoicing, if enabled.',
        'filename'    => 'logo',
    ],
];
