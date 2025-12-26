<?php

namespace Modules\Cms\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Cms\Entities\Menu;
use Modules\Cms\Http\Resources\MenuResource;

class MenuController extends Controller
{
    /**
     * Display a menu by identifier.
     */
    public function show(string $identifier): JsonResponse
    {
        $menu = Menu::active()
            ->with(['items' => function ($q) {
                $q->active()->ordered();
            }])
            ->byIdentifier($identifier)
            ->firstOrFail();

        return response()->json([
            'data' => new MenuResource($menu),
        ]);
    }

    /**
     * Get menu by location.
     */
    public function byLocation(string $location): JsonResponse
    {
        $menu = Menu::active()
            ->with(['items' => function ($q) {
                $q->active()->ordered();
            }])
            ->byLocation($location)
            ->first();

        if (! $menu) {
            return response()->json([
                'message' => 'Menu non trouvé pour cet emplacement.',
            ], 404);
        }

        return response()->json([
            'data' => new MenuResource($menu),
        ]);
    }
}
