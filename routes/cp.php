<?php

use Illuminate\Support\Facades\Route;
use studioespresso\splashingimages\Http\Controllers\DownloadController;
use studioespresso\splashingimages\Http\Controllers\ImagesController;

Route::middleware(['auth', 'can:accessCp', 'can:accessPlugin-splashing-images'])->group(function () {
    Route::post('splashing-images/download', DownloadController::class);
    Route::get('splashing-images/search/{page?}', [ImagesController::class, 'search'])->whereNumber('page');
    Route::get('splashing-images/{page?}', [ImagesController::class, 'index'])->whereNumber('page');
});
