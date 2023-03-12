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

    /*
    |--------------------------------------------------------------------------
    | Layout Settings
    |--------------------------------------------------------------------------
    |
    | These are settings related to the layout of the site.
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

        // Whether or not to display previous/next buttons when viewing a piece
        // that link to its nearest neighbors (chronologically)
        'piece_previous_next_buttons' => 1,
    ],

    'layout' => [
        // Whether or not the site should be full-width
        // By default content is confined to the center portion
        // This is overridden for the admin panel/anywhere there is a navigation sidebar
        'full_width' => 0,
    ],

    // Whether galleries should display images in columns or rows
    // Should be either 'rows' or 'columns'
    // It's recommended to use rows!
    'gallery_arrangement' => 'rows',

    /*
    |--------------------------------------------------------------------------
    | Image Settings
    |--------------------------------------------------------------------------
    |
    | These are settings related to the site's images.
    |
    */

    // Image dimensions, in px. Which thumbnail dimension is used
    // depends on the "gallery_arrangement" setting above
    // Note that these settings are not retroactive!
    'thumbnail_width'    => 250,
    'thumbnail_height'   => 200,
    'display_image_size' => 2000,

    // What format(s) images should be saved/displayed in
    // Supported formats are JPEG, PNG, GIF, BMP, or WebP
    // For more information, see https://image.intervention.io/v2/introduction/formats
    // WebP is recommended for display images at minimum due to both transparency support as well as better compression
    // Broadly these settings should be left at default as they have been balanced around compatibility/convenience, image fidelity (where relevant), and performance/space saving
    // Note that these settings are not retroactive!
    'image_formats' => [
        // Includes full-sized images; setting this to WebP is recommended if storage space is a significant concern,
        // as these are likely to be the largest use of storage for the site, especially if there are many and/or large images
        // However at present this does not preserve DPI, so it is disabled by default as this may be relevant
        // Setting this value to null will maintain images' existing format(s)
        'full' => null,

        // Includes watermarked/display image and thumbnails. Has no impact unless different from the setting above
        // Again this is highly recommended to be kept at default (WebP)
        // Setting to null will maintain either the existing format (if the above is not set) or defer to the setting above (if it is)
        'display' => 'webp',

        // What format to display full-size images to on-demand when accessed via the commission interface
        // Allows commissioners, etc. to retrieve images in a more broadly supported format as appropriate
        // Only applies if "full" above is not null
        'commission_full' => 'png',

        // What format to display images in on-demand when accessed via the admin panel, i.e. when editing a piece image
        // Allows retrieving images in a more broadly supported format as appropriate
        // Only applies if "full" and/or "display" above are not null (only converts images for which the relevant value is set)
        'admin_view' => 'png',

        // What format site images uploaded through the admin panel should be stored in
        // Supports PNG or WebP, cannot be null
        'site_images' => 'webp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Features
    |--------------------------------------------------------------------------
    |
    | Whether or not email-related features should be enabled.
    | NOTE: This requires email to be configured!
    |
    */

    'email_features' => 0,

    /*
    |--------------------------------------------------------------------------
    | Enable Captcha
    |--------------------------------------------------------------------------
    |
    | Whether or not reCAPTCHA is enabled. Presented as a privacy-conscious option;
    | this is liable to lead to spam if using commission components/the commission
    | request form, but is probably outright unnecessary if not.
    |
    | Note that enabling this requires you to have supplied the relevant information
    | in the .env file!
    |
    | Simply change to "1" to enable, or keep at "0" to disable.
    |
    */

    'captcha' => 0,

    /*
    |--------------------------------------------------------------------------
    | Enable Backups
    |--------------------------------------------------------------------------
    |
    | This feature will create a daily backup automatically. Requires a cron job
    | to be set up as well!
    | Note that it's recommended to configure config/backup.php as desired as well,
    | especially to adjust the location backups are saved to (by default, they are
    | saved locally). Note that even if this is disabled, backups can still be ran
    | manually using the backup:run command.
    |
    | Simply change to "1" to enable, or keep at "0" to disable.
    |
    */

    'enable_backups' => 0,

    /*
    |--------------------------------------------------------------------------
    | Version
    |--------------------------------------------------------------------------
    |
    | This is the current version of Aldebaran that your site is using.
    | Do not change this value!
    |
    */

    'version' => '3.7.0',

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
    // Used to send commission notifications and mailing list entries if enabled.
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
