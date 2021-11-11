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
    'thumbnail_width' => 250,
    'thumbnail_height' => 200,
    'display_image_size' => 2000,

    // Fee information. Current for PayPal as of Aug 10 2021
    'base_fee' => .49,
    'percent_fee' => 3.49,
];
