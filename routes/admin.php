<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Behind-the-scenes routes.
|
*/

Route::get('/', 'AdminController@getIndex');

# QUEUES
Route::group(['prefix' => 'commissions'], function() {
    Route::get('/{class}', 'CommissionController@getCommissionIndex');
    Route::get('/{class}/{status}', 'CommissionController@getCommissionIndex')->where('status', 'pending|accepted|complete|declined');
    Route::get('edit/{id}', 'CommissionController@getCommission');
    Route::post('edit/{id}', 'CommissionController@postCommission');
    Route::post('edit/{id}/{action}', 'CommissionController@postCommission')->where('action', 'accept|update|complete|decline|ban');

    Route::get('new/{id}', 'CommissionController@getNewCommission');
    Route::post('new', 'CommissionController@postNewCommission');
});

Route::get('ledger', 'CommissionController@getLedger');

# DATA
Route::group(['prefix' => 'data', 'namespace' => 'Data'], function() {
    # PROJECTS
    Route::get('projects', 'GalleryController@getProjectIndex');
    Route::get('projects/create', 'GalleryController@getCreateProject');
    Route::get('projects/edit/{id}', 'GalleryController@getEditProject');
    Route::get('projects/delete/{id}', 'GalleryController@getDeleteProject');
    Route::post('projects/create', 'GalleryController@postCreateEditProject');
    Route::post('projects/edit/{id?}', 'GalleryController@postCreateEditProject');
    Route::post('projects/delete/{id}', 'GalleryController@postDeleteProject');
    Route::post('projects/sort', 'GalleryController@postSortProject');

    # PIECES
    Route::get('pieces', 'GalleryController@getPieceIndex');
    Route::get('pieces/create', 'GalleryController@getCreatePiece');
    Route::get('pieces/edit/{id}', 'GalleryController@getEditPiece');
    Route::get('pieces/delete/{id}', 'GalleryController@getDeletePiece');
    Route::post('pieces/create', 'GalleryController@postCreateEditPiece');
    Route::post('pieces/edit/{id?}', 'GalleryController@postCreateEditPiece');
    Route::post('pieces/delete/{id}', 'GalleryController@postDeletePiece');

    Route::get('pieces/images/create/{id}', 'GalleryController@getCreateImage');
    Route::post('pieces/images/create', 'GalleryController@postCreateEditImage');
    Route::get('pieces/images/edit/{id}', 'GalleryController@getEditImage');
    Route::post('pieces/images/edit/{id}', 'GalleryController@postCreateEditImage');
    Route::get('pieces/images/delete/{id}', 'GalleryController@getDeleteImage');
    Route::post('pieces/images/delete/{id}', 'GalleryController@postDeleteImage');

    Route::post('pieces/{id}/sort-images', 'GalleryController@postSortPieceImages');

    # TAGS
    Route::get('tags', 'GalleryController@getTagIndex');
    Route::get('tags/create', 'GalleryController@getCreateTag');
    Route::get('tags/edit/{id}', 'GalleryController@getEditTag');
    Route::get('tags/delete/{id}', 'GalleryController@getDeleteTag');
    Route::post('tags/create', 'GalleryController@postCreateEditTag');
    Route::post('tags/edit/{id?}', 'GalleryController@postCreateEditTag');
    Route::post('tags/delete/{id}', 'GalleryController@postDeleteTag');

    # PROGRAMS
    Route::get('programs', 'GalleryController@getProgramIndex');
    Route::get('programs/create', 'GalleryController@getCreateProgram');
    Route::get('programs/edit/{id}', 'GalleryController@getEditProgram');
    Route::get('programs/delete/{id}', 'GalleryController@getDeleteProgram');
    Route::post('programs/create', 'GalleryController@postCreateEditProgram');
    Route::post('programs/edit/{id?}', 'GalleryController@postCreateEditProgram');
    Route::post('programs/delete/{id}', 'GalleryController@postDeleteProgram');

    # COMMISSION CLASSES
    Route::get('commission-classes', 'CommissionController@getCommissionClassIndex');
    Route::get('commission-classes/create', 'CommissionController@getCreateCommissionClass');
    Route::get('commission-classes/edit/{id}', 'CommissionController@getEditCommissionClass');
    Route::get('commission-classes/delete/{id}', 'CommissionController@getDeleteCommissionClass');
    Route::post('commission-classes/create', 'CommissionController@postCreateEditCommissionClass');
    Route::post('commission-classes/edit/{id?}', 'CommissionController@postCreateEditCommissionClass');
    Route::post('commission-classes/delete/{id}', 'CommissionController@postDeleteCommissionClass');
    Route::post('commission-classes/sort', 'CommissionController@postSortCommissionClass');

    # COMMISSION CATEGORIES
    Route::get('commission-categories', 'CommissionController@getIndex');
    Route::get('commission-categories/create', 'CommissionController@getCreateCommissionCategory');
    Route::get('commission-categories/edit/{id}', 'CommissionController@getEditCommissionCategory');
    Route::get('commission-categories/delete/{id}', 'CommissionController@getDeleteCommissionCategory');
    Route::post('commission-categories/create', 'CommissionController@postCreateEditCommissionCategory');
    Route::post('commission-categories/edit/{id?}', 'CommissionController@postCreateEditCommissionCategory');
    Route::post('commission-categories/delete/{id}', 'CommissionController@postDeleteCommissionCategory');
    Route::post('commission-categories/sort', 'CommissionController@postSortCommissionCategory');

    # COMMISSION TYPES
    Route::get('commission-types', 'CommissionController@getCommissionTypeIndex');
    Route::get('commission-types/create', 'CommissionController@getCreateCommissionType');
    Route::get('commission-types/edit/{id}', 'CommissionController@getEditCommissionType');
    Route::get('commission-types/delete/{id}', 'CommissionController@getDeleteCommissionType');
    Route::post('commission-types/create', 'CommissionController@postCreateEditCommissionType');
    Route::post('commission-types/edit/{id?}', 'CommissionController@postCreateEditCommissionType');
    Route::post('commission-types/delete/{id}', 'CommissionController@postDeleteCommissionType');
    Route::post('commission-types/sort', 'CommissionController@postSortCommissionType');
});

# TEXT PAGES
Route::group(['prefix' => 'pages'], function() {
    Route::get('/', 'PageController@getPagesIndex');
    Route::get('edit/{id}', 'PageController@getEditPage');
    Route::post('edit/{id?}', 'PageController@postEditPage');
});

# CHANGELOG
Route::group(['prefix' => 'changelog'], function() {
    Route::get('/', 'ChangelogController@getChangelogIndex');
    Route::get('create', 'ChangelogController@getCreateLog');
    Route::get('edit/{id}', 'ChangelogController@getEditLog');
    Route::get('delete/{id}', 'ChangelogController@getDeleteLog');
    Route::post('create', 'ChangelogController@postCreateEditLog');
    Route::post('edit/{id?}', 'ChangelogController@postCreateEditLog');
    Route::post('delete/{id}', 'ChangelogController@postDeleteLog');
});

# SITE SETTINGS
Route::get('site-settings', 'AdminController@getSettings');
Route::post('site-settings/{key}', 'AdminController@postEditSetting');

# SITE IMAGES
Route::group(['prefix' => 'site-images'], function() {
    Route::get('/', 'AdminController@getSiteImages');
    Route::post('upload', 'AdminController@postUploadImage');
    Route::post('upload/css', 'AdminController@postUploadCss');
});

# ACCOUNT SETTINGS
Route::group(['prefix' => 'account-settings'], function() {
    Route::get('/', 'AccountController@getAccountSettings');
    Route::post('email', 'AccountController@postEmail');
    Route::post('password', 'AccountController@postPassword');

    Route::group(['prefix' => 'two-factor'], function() {
        Route::post('enable', 'AccountController@postEnableTwoFactor');
        Route::get('confirm', 'AccountController@getConfirmTwoFactor');
        Route::post('confirm', 'AccountController@postConfirmTwoFactor');
        Route::post('disable', 'AccountController@postDisableTwoFactor');
    });
});
