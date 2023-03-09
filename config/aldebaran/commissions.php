<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Commissions Settings
    |--------------------------------------------------------------------------
    |
    | These are settings related to the site's commission-related components.
    |
    | NOTE: Enabling commissions and using these components in a way that generates
    | income, or contributes to generating income, requires a private license!
    |
    */

    // Enables and displays the site's commission components
    'enabled' => 0,

    // Fee information. Current for PayPal as of Aug 10 2021
    'fee'     => [
        'base'         => .49,
        'percent'      => 3.49,
        'percent_intl' => 4.99,
    ],

    // Progress states that can be selected when updating commissions
    // Feel free to edit these; it will not impact preexisting commissions
    'progress_states' => [
        'Not Started',
        'Working On',
        'Sketch',
        'Lines',
        'Color',
        'Shading',
        'Pending Approval',
        'Finalizing',
        'Finished',
    ],
];
