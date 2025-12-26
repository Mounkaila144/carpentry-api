<?php

use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\Superadmin\BlockController;
use Modules\Cms\Http\Controllers\Superadmin\MediaController;
use Modules\Cms\Http\Controllers\Superadmin\MenuController;
use Modules\Cms\Http\Controllers\Superadmin\MenuItemController;
use Modules\Cms\Http\Controllers\Superadmin\PageController;
use Modules\Cms\Http\Controllers\Superadmin\SettingController;

/*
|--------------------------------------------------------------------------
| Superadmin CMS Routes (CENTRAL DATABASE)
|--------------------------------------------------------------------------
| Ces routes utilisent la base de données centrale pour le CMS
*/

Route::prefix('superadmin')->middleware(['auth:sanctum'])->group(function () {
    Route::prefix('cms')->group(function () {
        // Pages
        Route::prefix('pages')->group(function () {
            Route::get('/', [PageController::class, 'index']);
            Route::post('/', [PageController::class, 'store']);
            Route::get('/templates', [PageController::class, 'templates']);
            Route::get('/{id}', [PageController::class, 'show']);
            Route::put('/{id}', [PageController::class, 'update']);
            Route::delete('/{id}', [PageController::class, 'destroy']);
            Route::post('/{id}/restore', [PageController::class, 'restore']);
            Route::delete('/{id}/force', [PageController::class, 'forceDelete']);
            Route::post('/{id}/publish', [PageController::class, 'publish']);
            Route::post('/{id}/unpublish', [PageController::class, 'unpublish']);
            Route::post('/{id}/duplicate', [PageController::class, 'duplicate']);
            Route::post('/reorder', [PageController::class, 'reorder']);
        });

        // Blocks
        Route::prefix('blocks')->group(function () {
            Route::get('/', [BlockController::class, 'index']);
            Route::post('/', [BlockController::class, 'store']);
            Route::get('/types', [BlockController::class, 'types']);
            Route::get('/{id}', [BlockController::class, 'show']);
            Route::put('/{id}', [BlockController::class, 'update']);
            Route::delete('/{id}', [BlockController::class, 'destroy']);
            Route::post('/{id}/restore', [BlockController::class, 'restore']);
            Route::delete('/{id}/force', [BlockController::class, 'forceDelete']);
            Route::post('/reorder', [BlockController::class, 'reorder']);
        });

        // Menus
        Route::prefix('menus')->group(function () {
            Route::get('/', [MenuController::class, 'index']);
            Route::post('/', [MenuController::class, 'store']);
            Route::get('/locations', [MenuController::class, 'locations']);
            Route::get('/{id}', [MenuController::class, 'show']);
            Route::put('/{id}', [MenuController::class, 'update']);
            Route::delete('/{id}', [MenuController::class, 'destroy']);
            Route::post('/{id}/restore', [MenuController::class, 'restore']);
            Route::delete('/{id}/force', [MenuController::class, 'forceDelete']);
        });

        // Menu Items
        Route::prefix('menu-items')->group(function () {
            Route::get('/', [MenuItemController::class, 'index']);
            Route::post('/', [MenuItemController::class, 'store']);
            Route::get('/{id}', [MenuItemController::class, 'show']);
            Route::put('/{id}', [MenuItemController::class, 'update']);
            Route::delete('/{id}', [MenuItemController::class, 'destroy']);
            Route::post('/{id}/restore', [MenuItemController::class, 'restore']);
            Route::delete('/{id}/force', [MenuItemController::class, 'forceDelete']);
            Route::post('/reorder', [MenuItemController::class, 'reorder']);
        });

        // Media
        Route::prefix('media')->group(function () {
            Route::get('/', [MediaController::class, 'index']);
            Route::post('/', [MediaController::class, 'store']);
            Route::get('/folders', [MediaController::class, 'folders']);
            Route::get('/{id}', [MediaController::class, 'show']);
            Route::put('/{id}', [MediaController::class, 'update']);
            Route::delete('/{id}', [MediaController::class, 'destroy']);
            Route::post('/{id}/restore', [MediaController::class, 'restore']);
            Route::delete('/{id}/force', [MediaController::class, 'forceDelete']);
            Route::post('/bulk-delete', [MediaController::class, 'bulkDelete']);
        });

        // Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingController::class, 'index']);
            Route::post('/', [SettingController::class, 'store']);
            Route::get('/groups', [SettingController::class, 'groups']);
            Route::get('/{id}', [SettingController::class, 'show']);
            Route::put('/{id}', [SettingController::class, 'update']);
            Route::delete('/{id}', [SettingController::class, 'destroy']);
            Route::post('/bulk', [SettingController::class, 'bulkUpdate']);
        });
    });
});
