<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Commission Types
    |--------------------------------------------------------------------------
    |
    | This is a list of commission types that will be available for configuration
    | and for users of the site to view information on and request commissions for.
    |
    */

    'art' => [
        'forms' => [
            'basic' => [
                'references' => [
                    'name' => 'Reference(s)',
                    'label' => 'Reference(s)',
                    'type' => 'textarea',
                    'help' => 'Please provide the URL(s) of clear reference(s) for each character.'
                ],
                'details' => [
                    'name' => 'Details',
                    'label' => 'Desired pose(s), attitude(s)/Expression(s), and the like',
                    'type' => 'textarea',
                    'help' => 'Consult the information for the commission type you\'ve selected for more details.'
                ],
            ],
        ],
        // Any custom pages that should be created for this commission type
        'pages' => [
            'willwont' => [
                'name' => 'Will and Won\'t Draw',
                'text' => '<p>Will and Won\'t draw information goes here.</p>'
            ],
        ]
    ],
];
