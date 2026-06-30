<?php

use Illuminate\Support\Facades\Route;
use Modules\HunFDM\Http\Controllers\Admin\AdminFdmController;

Route::prefix('admin/hunfdm')->middleware(['web', 'auth', 'ability:admin'])->group(function () {
    Route::get('/',     [AdminFdmController::class, 'index'])->name('admin.hunfdm.index');
    Route::get('/{id}', [AdminFdmController::class, 'show'])->name('admin.hunfdm.show');
});
