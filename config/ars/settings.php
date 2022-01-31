<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | This is a list of general site settings that should not be changed
    | frequently.
    |
    */

    // Whether galleries should display images in columns or rows.
    // Should be either 'rows' or 'columns'
    'gallery_arrangement' => 'rows',

    // Image dimensions, in px.
    // Which thumbnail dimension is used depends on the setting above.
    'thumbnail_width'    => 250,
    'thumbnail_height'   => 200,
    'display_image_size' => 2000,

    // Fee information. Current for PayPal as of Aug 10 2021
    'fee' => [
        'base'         => .49,
        'percent'      => 3.49,
        'percent_intl' => 4.99,
    ],

    /*
    |--------------------------------------------------------------------------
    | Version
    |--------------------------------------------------------------------------
    |
    | This is the current version of Ars Errabunda that your site is using.
    | Do not change this value!
    |
    */

    'version' => '2.0.0',
];
