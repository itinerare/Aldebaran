# Aldebaran

Aldebaran is a Laravel-based framework for a personal gallery and/or commission site aimed at artists and other creatives.

- Support Discord: https://discord.gg/mVqUzgQXMd
- Example Site (my own): https://www.itinerare.net

I originally built this project for my own site specifically, but later created a more generalized version and have since been working to extend its capabilities. In essence, I originally built a static version of my own site, but rapidly grew tired of maintaining it due to the need to manually create thumbnails, etc. Consequently, I built this to do so as well as handle watermarking, etc. in a configurable way without me needing to open an image editor.

After friends suggested offhandedly that it would be neat if it could also handle commission information, I built that into it too, since this way it's easier (for me, at least) to handle commissions; not only is the information all in one place, the site can then serve both a commissioner-only view as well as a more sparse public-facing queue.
From there, many such features have cascaded out as I have found things necessary or thought them suitable to the overall project.

## Features
### Gallery & Projects
- An overall gallery showing all pieces on the site (with configurable exceptions if desired); the gallery can also be disabled with a config option
- Projects into which different pieces are categorized. These are displayed in a dropdown in the main navigation bar, or, with a config option, as navbar items themselves
- Pieces are entries in projects/the site's gallery that may contain multiple images, a description, and so on. They can have tags attached to them as well as different programs or media used in their creation, and can be indicated as a good example (or not) for commission purposes
- Each image attached to a piece has a "display version" that can be watermarked and/or resized, including a configurable repeating text watermark, as desired. This can also be disabled. Pieces can have one or more primary and secondary images, which are displayed more and less prominently respectively (e.g. the primary images are selected from for the piece's thumbnail)
- Tags can be applied to pieces both for organizational purposes (and searched for within the gallery/projects) as well as being used for some internal organization, such as denoting pieces that should not appear in the main gallery, or pieces that should be shown as examples for a given commission type
- Programs and media are configurable and can have icons set if desired, which are displayed on pieces if set

### Commissions
Note that use of commission components in a use that generates or contributes to generating monetary income requires a [private license](LICENSE.md).

- Configurable commission classes-- large, overarching varieties of commissions that a site offers (e.g. art commissions). Classes can be individually opened and closed at will, have overall available slots set, etc.
- Commission categories, which are used to organize different individual types of commissions within a class
- Commission types, which are individual kinds of commissions on offer, and for which examples are (optionally) shown. These have different settings such as pricing, information about extras, slots/availablility, etc.
- A form builder for commission forms which can include content from the commission type's class and/or category for minimal redundancy
- Commission info pages per class that display all visible categories and types with pricing, examples semi-randomly selected from the valid pool (preferring pieces that are set as good examples)
- Support for "hidden" commission types, which are active but not visible on the main commission info page, via a randomly generated "key" URL
- Commission queues per class that display all accepted commissions in the queue with general information, set status, and created/last updated timestamps
- Admin commission queue with form responses, contact and payment information, etc. displayed, where commissions can be accepted/declined/updated/etc. including attaching pieces to a commission, which will allow the commissioner access to the full-sized images. Multiple payments may also be attached to a commission and/or marked as paid, international, etc. including tip if relevant; total after PayPal fees are calculated and shown for each. Commissioners may also be banned from this menu in case of abuse
- Commissioner-only view per commission (accessible via a URL with a randomly generated key) that lists form responses, any pieces attached to the commission, and any comments from the artist/site operator
- Admin-only ledger view which lists payments recorded per year, for the year in which they were paid, with link to the relevant commission

### Other functions
- Two-factor authentication for the sole/admin account
- Various pages' text around the site are configurable via the admin panel
- A changelog updatable via the admin panel
- RSS feeds for the gallery and changelog entries
- Admin panel for editing site settings, including commission-related settings
- Admin panel for uploading images used in the site's layout as well as custom CSS

## Setup

For those not familiar with web development, please refer to the [Full Guide](https://github.com/itinerare/Aldebaran/wiki/Setup-Guide) for a much more detailed set of instructions!

### Obtain a copy of the code

```
$ git clone https://github.com/itinerare/aldebaran.git
```

### Configure .env in the directory

```
$ cp .env.example .env
```

Fill out .env as appropriate. The following are required:

- APP_NAME=(Your site's name, without spaces)
- APP_ENV=production
- APP_DEBUG=false
- APP_URL=
- CONTACT_ADDRESS=(Email address)

### Setting up

Install packages with composer:
```
$ composer install
```

Generate app key and run database migrations:
```
$ php artisan key:generate 
$ php artisan migrate
```

Perform general site setup:
```
$ php artisan setup-aldebaran
```

## Contributing
Thank you for considering contributing to Aldebaran! Please see the [Contribution Guide](CONTRIBUTING.md) for information on how best to contribute.

### Extending Aldebaran
If you are interested in providing optional/plugin-type functionality for Aldebaran, please contact me first and foremost; while I am open to developing plugin support and would rather do so before any are made, I will not be doing so until there is concrete interest in it.

## Contact
If you have any questions, please contact me via email at [aldebaran@itinerare.net](emailto:aldebaran@itinerare.net).
