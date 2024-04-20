<?php

use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ImageController::class, 'showUploadForm'])->name('images.index');
Route::post('/upload', [ImageController::class, 'upload'])->name('images.upload');
Route::get('/list', [ImageController::class, 'list'])->name('images.list');
Route::get('/download', [ImageController::class, 'download'])->name('images.download');
Route::prefix('api/v1')->group(function () {
    Route::get('/get-list', [ImageController::class, 'actionGetList'])->name('images.get-list.api');
    Route::get('/view/{id}', [ImageController::class, 'actionView'])->name('images.view.api');
});
