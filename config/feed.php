<?php

return [
    'feeds' => [
        'main' => [
            /*
             * Here you can specify which class and method will return
             * the items that should appear in the feed. For example:
             * 'App\Model@getAllFeedItems'
             *
             * You can also pass an argument to that method:
             * ['App\Model@getAllFeedItems', 'argument']
             */
            'items' => 'App\Models\Gallery\Piece@getFeedItems',

            /*
             * The feed will be available on this url.
             */
            'url' => '/gallery',

            'title'       => env('APP_NAME', 'Laravel').' ・ Gallery',
            'description' => 'Pieces from the main gallery.',
            'language'    => 'en-US',

            /*
             * The view that will render the feed.
             */
            'view' => 'feed::atom',

            /*
             * The type to be used in the <link> tag
             */
            'type' => 'application/atom+xml',
        ],

        'all' => [
            /*
             * Here you can specify which class and method will return
             * the items that should appear in the feed. For example:
             * 'App\Model@getAllFeedItems'
             *
             * You can also pass an argument to that method:
             * ['App\Model@getAllFeedItems', 'argument']
             */
            'items' => ['App\Models\Gallery\Piece@getFeedItems', 'gallery' => false],

            /*
             * The feed will be available on this url.
             */
            'url' => '/all',

            'title'       => env('APP_NAME', 'Laravel').' ・ All',
            'description' => 'All pieces, regardless of their appearance in the main gallery.',
            'language'    => 'en-US',

            /*
             * The view that will render the feed.
             */
            'view' => 'feed::atom',

            /*
             * The type to be used in the <link> tag
             */
            'type' => 'application/atom+xml',
        ],

        'changelog' => [
            /*
             * Here you can specify which class and method will return
             * the items that should appear in the feed. For example:
             * 'App\Model@getAllFeedItems'
             *
             * You can also pass an argument to that method:
             * ['App\Model@getAllFeedItems', 'argument']
             */
            'items' => 'App\Models\Changelog@getFeedItems',

            /*
             * The feed will be available on this url.
             */
            'url' => '/changelog',

            'title'       => env('APP_NAME', 'Laravel').' ・ Changelog',
            'description' => 'Changelog entries.',
            'language'    => 'en-US',

            /*
             * The view that will render the feed.
             */
            'view' => 'feed::atom',

            /*
             * The type to be used in the <link> tag
             */
            'type' => 'application/atom+xml',
        ],
    ],
];
