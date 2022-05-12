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

    'navigation' => [
        // This enables the main gallery page and shows the nav bar item
        // By default, this is enabled (1) but can be set to 0 to disable
        'gallery' => 1,

        // By default, projects are in a drop-down on the nav bar; this
        // makes each project its own item on the nav bar
        // Note that this requires being careful about how many projects/
        // overall navbar items you have!
        'projects_nav' => 0,
    ],

    'layout' => [
        // Whether or not the site should be full-width
        // By default content is confined to the center portion
        // This is overridden for the admin panel/anywhere there is a navigation sidebar
        'full_width' => 0,
    ],

    // Whether galleries should display images in columns or rows
    // Should be either 'rows' or 'columns'
    'gallery_arrangement' => 'rows',

    // Image dimensions, in px.
    // Which thumbnail dimension is used depends on the setting above
    'thumbnail_width'    => 250,
    'thumbnail_height'   => 200,
    'display_image_size' => 2000,

    'commissions' => [
        // Enables and displays the site's commission components
        // NOTE: Enabling this and using these components in a way that
        // generates income, or contributes to generating income, requires a private license!
        'enabled' => 0,

        // Fee information. Current for PayPal as of Aug 10 2021
        'fee' => [
            'base'         => .49,
            'percent'      => 3.49,
            'percent_intl' => 4.99,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Version
    |--------------------------------------------------------------------------
    |
    | This is the current version of Aldebaran that your site is using.
    | Do not change this value!
    |
    */

    'version' => '2.1.1',

    /*
    |--------------------------------------------------------------------------
    | Miscellaneous Values
    |--------------------------------------------------------------------------
    |
    | These are miscellaneous settings stored here for caching purposes.
    | They are likely to be sensitive and should not be saved here directly,
    | however, "storing" the .env values here helps with site performance.
    |
    | !! Do not change these !!
    | Edit the corresponding value(s) in the .env file instead.
    |
    */

    // The email information for the site.
    // Used to send commission notifications if enabeled.
    'admin_email' => [
        'address'  => env('MAIL_USERNAME', false),
        'password' => env('MAIL_PASSWORD', false),
    ],

    // thum.io info, used for generating meta tag preview images if enabled
    'thum_io' => [
        'key' => env('THUM_IO_KEY', false),
        'id'  => env('THUM_IO_ID', false),
    ],
];
