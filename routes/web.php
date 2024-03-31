<?php

use App\Http\Controllers\Admin\CommissionController as AdminCommissionController;
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
        Route::get('pieces/{id}', 'getPiece')
            ->whereNumber('id');
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
    Route::get('{class}/tos', 'getTos');
    Route::get('{class}/queue', 'getQueue');

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

Route::prefix('admin')->middleware(['auth', 'verified'])->group(__DIR__.'/admin.php');
