<?php

use Illuminate\Support\Facades\Route;
use studioespresso\splashingimages\controllers\DownloadController;
use studioespresso\splashingimages\controllers\ImagesController;

Route::middleware(['auth', 'can:accessCp', 'can:accessPlugin-splashing-images'])->group(function () {
    Route::post('splashing-images/download', DownloadController::class);
    Route::get('splashing-images/search/{page?}', [ImagesController::class, 'search'])->whereNumber('page');
    Route::get('splashing-images/{page?}', [ImagesController::class, 'index'])->whereNumber('page');
});
