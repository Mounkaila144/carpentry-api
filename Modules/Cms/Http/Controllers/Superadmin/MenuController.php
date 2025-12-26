<?php

namespace Modules\Cms\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Cms\Entities\Menu;
use Modules\Cms\Http\Requests\StoreMenuRequest;
use Modules\Cms\Http\Requests\UpdateMenuRequest;
use Modules\Cms\Http\Resources\MenuResource;

class MenuController extends Controller
{
    /**
     * Display a listing of the menus.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $location = $request->input('location');

        $query = Menu::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('identifier', 'like', "%{$search}%");
            });
        }

        if ($location) {
            $query->where('location', $location);
        }

        $menus = $query->latest()->paginate($perPage);

        return MenuResource::collection($menus);
    }

    /**
     * Store a newly created menu.
     */
    public function store(StoreMenuRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? true;

        $menu = Menu::create($data);

        return response()->json([
            'message' => 'Menu créé avec succès.',
            'data' => new MenuResource($menu),
        ], 201);
    }

    /**
     * Display the specified menu with its items.
     */
    public function show(string $id): JsonResponse
    {
        $menu = Menu::with('items.children')->findOrFail($id);

        return response()->json([
            'data' => new MenuResource($menu),
        ]);
    }

    /**
     * Update the specified menu.
     */
    public function update(UpdateMenuRequest $request, string $id): JsonResponse
    {
        $menu = Menu::findOrFail($id);
        $menu->update($request->validated());

        return response()->json([
            'message' => 'Menu modifié avec succès.',
            'data' => new MenuResource($menu),
        ]);
    }

    /**
     * Remove the specified menu.
     */
    public function destroy(string $id): JsonResponse
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return response()->json([
            'message' => 'Menu supprimé avec succès.',
        ]);
    }

    /**
     * Restore a soft deleted menu.
     */
    public function restore(string $id): JsonResponse
    {
        $menu = Menu::onlyTrashed()->findOrFail($id);
        $menu->restore();

        return response()->json([
            'message' => 'Menu restauré avec succès.',
            'data' => new MenuResource($menu),
        ]);
    }

    /**
     * Permanently delete a menu.
     */
    public function forceDelete(string $id): JsonResponse
    {
        $menu = Menu::withTrashed()->findOrFail($id);
        $menu->forceDelete();

        return response()->json([
            'message' => 'Menu supprimé définitivement.',
        ]);
    }

    /**
     * Get available menu locations.
     */
    public function locations(): JsonResponse
    {
        $locations = [
            'header' => 'Menu principal (header)',
            'footer' => 'Menu pied de page (footer)',
            'sidebar' => 'Menu latéral (sidebar)',
            'mobile' => 'Menu mobile',
        ];

        return response()->json([
            'data' => $locations,
        ]);
    }
}
