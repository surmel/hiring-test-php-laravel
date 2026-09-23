<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\PublishController;
use App\Http\Controllers\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Baseline endpoint — Resource контроллер для Posts
Route::apiResource('posts', PostController::class);
Route::post('/posts/{post}/tags', [PostController::class, 'attachTags']);
Route::delete('/posts/{post}/tags/{tag}', [PostController::class, 'detachTag']);
Route::apiResource('tags', TagController::class);

// Блок Б — код-ревью, не рефакторить
Route::post('/publish/batch', [PublishController::class, 'batch']);
Route::get('/publish/report', [PublishController::class, 'report']);
