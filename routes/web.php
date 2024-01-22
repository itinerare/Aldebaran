<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ChangelogController;
use App\Http\Controllers\Admin\CommissionController as AdminCommissionController;
use App\Http\Controllers\Admin\Data\CommissionController as CommissionDataController;
use App\Http\Controllers\Admin\Data\GalleryController as GalleryDataController;
use App\Http\Controllers\Admin\MailingListController as AdminMailingListController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\MailingListController;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// GENERAL
Route::controller(Controller::class)->group(function () {
    Route::get('/', 'getIndex');
    Route::get('about', 'getAbout');
    Route::get('privacy', 'getPrivacyPolicy');
    Route::get('changelog', 'getChangelog');

    Route::get('feeds', 'getFeeds');
});

Route::feeds('feeds');

Route::controller(GalleryController::class)->group(function () {
    Route::prefix('gallery')->group(function () {
        Route::get('/', 'getGallery');
        Route::get('pieces/{id}', 'getPiece');
        Route::get('pieces/{id}.{slug?}', 'getPiece');
    });

    Route::prefix('projects')->group(function () {
        Route::get('{name}', 'getProject');
    });
});

Route::controller(MailingListController::class)->prefix('mailing-lists')->group(function () {
    Route::get('{id}', 'getMailingList');
    Route::get('verify/{id}', 'getVerify');
    Route::get('unsubscribe/{id}', 'getUnsubscribe');
    Route::post('{id}/subscribe', 'postSubscribe')
        ->middleware(ProtectAgainstSpam::class);
});

Route::controller(CommissionController::class)->prefix('commissions')->group(function () {
    Route::get('{class}', 'getInfo');
    Route::get('{class}/tos', 'getVerify');
    Route::get('class}/queue', 'getQueue');

    Route::get('types/{key}', 'getType');
    Route::get('types/{key}/gallery', 'getTypeGallery');
    Route::get('{class}/new', 'getNewCommission');
    Route::post('new', 'postNewCommission')
        ->middleware(ProtectAgainstSpam::class);

    Route::get('view/{key}', 'getViewCommission');
    Route::get('view/{key}/{id}', 'getViewCommissionImage');

    Route::get('{class}/quotes/new', 'getNewQuote');
    Route::get('quotes/view/{key}', 'getViewQuote');
    Route::post('quotes/new', 'postNewQuote')
        ->middleware(ProtectAgainstSpam::class);

    // Clobbers the above routes otherwise
    Route::get('{class}/{key}', 'getClassPage');
});

// WEBHOOK ENDPOINTS
Route::controller(AdminCommissionController::class)->prefix('admin/webhooks')->group(function () {
    if (config('aldebaran.commissions.payment_processors.stripe.integration.enabled')) {
        Route::post('stripe', 'postStripeWebhook');
    }
    if (config('aldebaran.commissions.payment_processors.paypal.integration.enabled')) {
        Route::post('paypal', 'postPaypalWebhook');
    }
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Routes requiring authentication; as this is a one-user application,
| user and admin functions are functionally one and the same.
|
*/

Route::prefix('admin')->middleware(['auth', 'verified'])->group(function () {
    Route::controller(AdminController::class)->group(function () {
        Route::get('/', 'getIndex');

        // SITE SETTINGS/IMAGES
        Route::prefix('site-settings')->group(function () {
            Route::get('/', 'getSettings');
            Route::post('{key}', 'postEditSetting');
        });

        Route::prefix('site-images')->group(function () {
            Route::get('/', 'getSiteImages');
            Route::post('upload', 'postUploadImage');
            Route::post('upload/css', 'postUploadCss');
        });
    });

    // COMMISSION QUEUES
    Route::controller(AdminCommissionController::class)->group(function () {
        Route::get('ledger', 'getLedger');

        Route::prefix('commissions')->group(function () {
            Route::get('{class}', 'getCommissionIndex');
            Route::get('{class}/{status}', 'getCommissionIndex')
                ->where('status', 'pending|accepted|complete|declined');
            Route::get('edit/{id}', 'getCommission');
            Route::post('edit/{id}', 'postCommission');
            Route::post('edit/{id}/{action}', 'postCommission')
                ->where('action', 'accept|update|complete|decline|ban');

            Route::get('invoice/{id}', 'getSendInvoice');
            Route::post('invoice/{id}', 'postSendInvoice');

            Route::get('new/{id}', 'getNewCommission');
            Route::post('new', 'postNewCommission');

            // QUOTES
            Route::prefix('quotes')->group(function () {
                Route::get('{class}', 'getQuoteIndex');
                Route::get('{class}/{status}', 'getQuoteIndex')
                    ->where('status', 'pending|accepted|complete|declined');
                Route::get('{class}/new', 'getNewQuote');
                Route::get('edit/{id}', 'getQuote');
                Route::post('new', 'postNewQuote');
                Route::post('edit/{id}', 'postQuote');
                Route::post('edit/{id}/{action}', 'postQuote')
                    ->where('action', 'accept|update|complete|decline|ban');
            });
        });
    });

    // DATA
    Route::prefix('data')->group(function () {
        // GALLERY DATA
        Route::controller(GalleryDataController::class)->group(function () {
            Route::prefix('projects')->group(function () {
                Route::get('/', 'getProjectIndex');
                Route::get('create', 'getCreateProject');
                Route::get('edit/{id}', 'getEditProject');
                Route::get('delete/{id}', 'getDeleteProject');
                Route::post('create', 'postCreateEditProject');
                Route::post('edit/{id?}', 'postCreateEditProject');
                Route::post('delete/{id}', 'postDeleteProject');
                Route::post('sort', 'postSortProject');
            });

            Route::prefix('pieces')->group(function () {
                Route::get('/', 'getPieceIndex');
                Route::get('create', 'getCreatePiece');
                Route::get('edit/{id}', 'getEditPiece');
                Route::get('delete/{id}', 'getDeletePiece');
                Route::post('create', 'postCreateEditPiece');
                Route::post('edit/{id?}', 'postCreateEditPiece');
                Route::post('delete/{id}', 'postDeletePiece');

                Route::post('{id}/sort-images', 'postSortPieceImages');
                Route::post('{id}/sort-literatures', 'postSortPieceLiteratures');

                Route::prefix('images')->group(function () {
                    Route::get('create/{id}', 'getCreateImage');
                    Route::post('create', 'postCreateEditImage');
                    Route::get('edit/{id}', 'getEditImage');
                    Route::post('edit/{id}', 'postCreateEditImage');
                    Route::get('delete/{id}', 'getDeleteImage');
                    Route::post('delete/{id}', 'postDeleteImage');
                    Route::get('view/{id}/{type}', 'getImageFile');
                });

                Route::prefix('literatures')->group(function () {
                    Route::get('create/{id}', 'getCreateLiterature');
                    Route::post('create', 'postCreateEditLiterature');
                    Route::get('edit/{id}', 'getEditLiterature');
                    Route::post('edit/{id}', 'postCreateEditLiterature');
                    Route::get('delete/{id}', 'getDeleteLiterature');
                    Route::post('delete/{id}', 'postDeleteLiterature');
                });
            });

            Route::prefix('tags')->group(function () {
                Route::get('/', 'getTagIndex');
                Route::get('create', 'getCreateTag');
                Route::get('edit/{id}', 'getEditTag');
                Route::get('delete/{id}', 'getDeleteTag');
                Route::post('create', 'postCreateEditTag');
                Route::post('edit/{id?}', 'postCreateEditTag');
                Route::post('delete/{id}', 'postDeleteTag');
            });

            Route::prefix('programs')->group(function () {
                Route::get('/', 'getProgramIndex');
                Route::get('create', 'getCreateProgram');
                Route::get('edit/{id}', 'getEditProgram');
                Route::get('delete/{id}', 'getDeleteProgram');
                Route::post('create', 'postCreateEditProgram');
                Route::post('edit/{id?}', 'postCreateEditProgram');
                Route::post('delete/{id}', 'postDeleteProgram');
            });
        });

        // COMMISSIONS DATA
        Route::controller(CommissionDataController::class)->prefix('commissions')->group(function () {
            Route::prefix('classes')->group(function () {
                Route::get('/', 'getCommissionClassIndex');
                Route::get('create', 'getCreateCommissionClass');
                Route::get('edit/{id}', 'getEditCommissionClass');
                Route::get('delete/{id}', 'getDeleteCommissionClass');
                Route::post('create', 'postCreateEditCommissionClass');
                Route::post('edit/{id?}', 'postCreateEditCommissionClass');
                Route::post('delete/{id}', 'postDeleteCommissionClass');
                Route::post('sort', 'postSortCommissionClass');
            });

            Route::prefix('categories')->group(function () {
                Route::get('/', 'getIndex');
                Route::get('create', 'getCreateCommissionCategory');
                Route::get('edit/{id}', 'getEditCommissionCategory');
                Route::get('delete/{id}', 'getDeleteCommissionCategory');
                Route::post('create', 'postCreateEditCommissionCategory');
                Route::post('edit/{id?}', 'postCreateEditCommissionCategory');
                Route::post('delete/{id}', 'postDeleteCommissionCategory');
                Route::post('sort', 'postSortCommissionCategory');
            });

            Route::prefix('types')->group(function () {
                Route::get('/', 'getCommissionTypeIndex');
                Route::get('create', 'getCreateCommissionType');
                Route::get('edit/{id}', 'getEditCommissionType');
                Route::get('delete/{id}', 'getDeleteCommissionType');
                Route::post('create', 'postCreateEditCommissionType');
                Route::post('edit/{id?}', 'postCreateEditCommissionType');
                Route::post('delete/{id}', 'postDeleteCommissionType');
                Route::post('sort', 'postSortCommissionType');
            });
        });
    });

    // MAILING LISTS
    Route::controller(AdminMailingListController::class)->prefix('mailing-lists')->group(function () {
        Route::get('/', 'getMailingListIndex');
        Route::get('create', 'getCreateMailingList');
        Route::get('edit/{id}', 'getEditMailingList');
        Route::get('delete/{id}', 'getDeleteMailingList');
        Route::post('create', 'postCreateEditMailingList');
        Route::post('edit/{id?}', 'postCreateEditMailingList');
        Route::post('delete/{id}', 'postDeleteMailingList');

        Route::prefix('entries')->group(function () {
            Route::get('create/{id}', 'getCreateEntry');
            Route::post('create', 'postCreateEditEntry');
            Route::get('edit/{id}', 'getEditEntry');
            Route::post('edit/{id}', 'postCreateEditEntry');
            Route::get('delete/{id}', 'getDeleteEntry');
            Route::post('delete/{id}', 'postDeleteEntry');
        });

        Route::prefix('subscriber')->group(function () {
            Route::get('{id}/kick', 'getKickSubscriber');
            Route::get('{id}/ban', 'getBanSubscriber');
            Route::post('{id}/kick', 'postKickSubscriber');
            Route::post('{id}/ban', 'postBanSubscriber');
        });
    });

    // MAINTENANCE, ETC.
    Route::controller(PageController::class)->prefix('pages')->group(function () {
        Route::get('/', 'getPagesIndex');
        Route::get('edit/{id}', 'getEditPage');
        Route::post('edit/{id?}', 'postEditPage');
    });

    Route::controller(ChangelogController::class)->prefix('changelog')->group(function () {
        Route::get('/', 'getChangelogIndex');
        Route::get('create', 'getCreateLog');
        Route::get('edit/{id}', 'getEditLog');
        Route::get('delete/{id}', 'getDeleteLog');
        Route::post('create', 'postCreateEditLog');
        Route::post('edit/{id?}', 'postCreateEditLog');
        Route::post('delete/{id}', 'postDeleteLog');
    });

    Route::controller(AccountController::class)->prefix('account-settings')->group(function () {
        Route::get('/', 'getAccountSettings');
        Route::post('email', 'postEmail');
        Route::post('password', 'postPassword');

        Route::prefix('two-factor')->group(function () {
            Route::post('enable', 'postEnableTwoFactor');
            Route::get('confirm', 'getConfirmTwoFactor');
            Route::post('confirm', 'postConfirmTwoFactor');
            Route::post('disable', 'postDisableTwoFactor');
        });
    });
});
