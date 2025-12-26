<?php

use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\Frontend\BlockController;
use Modules\Cms\Http\Controllers\Frontend\MenuController;
use Modules\Cms\Http\Controllers\Frontend\PageController;
use Modules\Cms\Http\Controllers\Frontend\SettingController;

/*
|--------------------------------------------------------------------------
| Frontend CMS Routes (PUBLIC - READ ONLY)
|--------------------------------------------------------------------------
| Ces routes sont publiques et ne nécessitent pas d'authentification
| Elles permettent de récupérer le contenu CMS pour le frontend
*/

Route::prefix('cms')->group(function () {
    // Pages
    Route::prefix('pages')->group(function () {
        Route::get('/', [PageController::class, 'index']);
        Route::get('/home', [PageController::class, 'home']);
        Route::get('/tree', [PageController::class, 'tree']);
        Route::get('/{slug}', [PageController::class, 'show'])->where('slug', '[a-z0-9\-]+');
    });

    // Blocks
    Route::prefix('blocks')->group(function () {
        Route::get('/', [BlockController::class, 'index']);
        Route::get('/multiple', [BlockController::class, 'multiple']);
        Route::get('/{identifier}', [BlockController::class, 'show']);
    });

    // Menus
    Route::prefix('menus')->group(function () {
        Route::get('/location/{location}', [MenuController::class, 'byLocation']);
        Route::get('/{identifier}', [MenuController::class, 'show']);
    });

    // Settings
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index']);
        Route::get('/multiple', [SettingController::class, 'multiple']);
        Route::get('/{key}', [SettingController::class, 'show']);
    });
});
