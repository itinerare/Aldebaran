<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**************************************************************************************************
    General routes
**************************************************************************************************/

Route::get('/', 'Controller@getIndex');
Route::get('/about', 'Controller@getAbout');
Route::get('/privacy', 'Controller@getPrivacyPolicy');
Route::get('/changelog', 'Controller@getChangelog');

// GALLERY
Route::group(['prefix' => 'gallery'], function () {
    Route::get('/', 'GalleryController@getGallery');
    Route::get('pieces/{id}.', 'GalleryController@getPiece');
    Route::get('pieces/{id}.{slug?}', 'GalleryController@getPiece');
});

Route::group(['prefix' => 'projects'], function () {
    Route::get('{name}', 'GalleryController@getProject');
});

// MAILING LISTS
Route::group(['prefix' => 'mailing-lists'], function () {
    Route::get('{id}', 'MailingListController@getMailingList');
    Route::get('verify/{id}', 'MailingListController@getVerify');
    Route::get('unsubscribe/{id}', 'MailingListController@getUnsubscribe');
    Route::post('{id}/subscribe', 'MailingListController@postSubscribe');
});

// COMMISSIONS
Route::group(['prefix' => 'commissions'], function () {
    Route::get('{class}', 'CommissionController@getInfo');
    Route::get('{class}/tos', 'CommissionController@getTos');
    Route::get('{class}/queue', 'CommissionController@getQueue');

    Route::get('types/{key}', 'CommissionController@getType');
    Route::get('types/{key}/gallery', 'CommissionController@getTypeGallery');
    Route::get('{class}/new', 'CommissionController@getNewCommission');
    Route::post('new', 'CommissionController@postNewCommission');

    Route::get('view/{key}', 'CommissionController@getViewCommission');
    Route::get('view/{key}/{id}', 'CommissionController@getViewCommissionImage');

    Route::get('{class}/{key}', 'CommissionController@getClassPage');
});

Route::get('/feeds', 'Controller@getFeeds');
Route::feeds('feeds');

Route::group(['prefix' => 'admin/webhooks', 'namespace' => 'Admin'], function () {
    if (config('aldebaran.commissions.payment_processors.stripe.integration.enabled')) {
        Route::post('stripe', 'CommissionController@postStripeWebhook');
    }
    if (config('aldebaran.commissions.payment_processors.paypal.integration.enabled')) {
        Route::post('paypal', 'CommissionController@postPaypalWebhook');
    }
});

/***************************************************
    Routes that require login
****************************************************/

Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['auth', 'verified']], function () {
    require_once __DIR__.'/admin.php';
});
