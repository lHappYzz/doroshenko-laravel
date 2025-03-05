<?php

use App\Http\Controllers\FileManagerController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::get('/index', [FileManagerController::class, 'index']);
Route::get('/file/{id}', [FileManagerController::class, 'get']);
Route::post('/upload', [FileManagerController::class, 'upload']);
Route::post('/delete', [FileManagerController::class, 'delete']);
Route::get('/download/{id}', [FileManagerController::class, 'download']);
Route::post('/update/{id}', [FileManagerController::class, 'update']);
