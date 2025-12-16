<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FileController;

Route::get('/files/{id}', [FileController::class, 'show'])->name('api.files.show');

