<?php

namespace Vuetik\VuetikLaravel;

use Illuminate\Support\Facades\Route;
use Vuetik\VuetikLaravel\Controllers\UploadImageController;

class VuetikLaravel
{
    public static function routes(): void
    {
        Route::post('/upload-img', UploadImageController::class)->name('upload-img');
    }
}
