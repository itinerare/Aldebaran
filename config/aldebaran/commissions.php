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

    // What currency and symbol the site uses.
    // Unless using a payment processor integration, this is only used for display
    // However, if so, you should ensure that the currency is set to a valid ISO code
    // (see https://www.iso.org/iso-4217-currency-codes.html)
    // See also https://stripe.com/docs/currencies for supported currencies for Stripe
    'currency'        => 'USD',
    'currency_symbol' => '$',

    /*
    |--------------------------------------------------------------------------
    | Payment Processors
    |--------------------------------------------------------------------------
    |
    | These settings relate to what payment processors are available,
    | and which may be selected by commissioners when requesting a new commission.
    |
    | You may freely add options here, provided you supply the necessay information.
    | See PayPal's entry as an example/for information as to what value does what.
    | It is recommended you disable any you do not want to use rather than remove them, however.
    |
    | NOTE: At least one option should be enabled at all times!
    |
    */

    'payment_processors' => [
        // A simple key used for internal identification. Should not contain spaces.
        'paypal' => [
            // Label for use around the site. May contain spaces.
            'label' => 'PayPal',
            // Whether or not the payment processor may be selected when creating a new commission request. 1 to enable, 0 to disable.
            // Note that this does not apply retroactively;
            // you can safely disable a payment processor without causing problems.
            'enabled' => 1,
            // Fee information. This should follow this format, with all values as decimals:
            // Base: base fee amount
            // Percent: percent for domestic payments
            // Percent (Intl): percent for international payments
            'fee' => [
                // Current as of Aug 10 2021
                'base'         => .49,
                'percent'      => 3.49,
                'percent_intl' => 4.99,
            ],
        ],

        'stripe' => [
            'label'   => 'Stripe',
            'enabled' => 0,
            'fee'     => [
                // Current as of Jun 1 2023
                'base'         => .30,
                'percent'      => 2.9,
                'percent_intl' => 2.9 + 1.5,
            ],
            'integration' => [
                // Whether or not Stripe integration features should be enabled
                // Requires you to have your secret key set in the site's .env file!
                'enabled'      => 0,
                // How many days after sending before invoices are considered due
                // This is 30 by default per Stripe
                'invoices_due' => 30,

                // !! Do not change this !!
                // Edit the corresponding values in the .env file instead.
                'secret_key'     => env('STRIPE_SECRET_KEY'),
                'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            ],
        ],

        // Provided as a catch-all.
        // Note that there will be no calculation for any relevant fees!
        'other' => [
            'label'   => 'Other',
            'enabled' => 0,
            'fee'     => [
                'base'         => 0,
                'percent'      => 0,
                'percent_intl' => 0,
            ],
        ],
    ],
];
