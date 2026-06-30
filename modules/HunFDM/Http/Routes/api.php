<?php

use Illuminate\Support\Facades\Route;
use Modules\HunFDM\Http\Controllers\Api\FdmApiController;

Route::prefix('hunfdm')->middleware(['api', 'auth.apikey'])->group(function () {
    Route::post('/report',          [FdmApiController::class, 'store']);
    Route::get('/report/{pirepId}', [FdmApiController::class, 'show']);
});
