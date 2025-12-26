<?php

namespace Modules\Cms\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Cms\Entities\MenuItem;
use Modules\Cms\Http\Requests\StoreMenuItemRequest;
use Modules\Cms\Http\Requests\UpdateMenuItemRequest;
use Modules\Cms\Http\Resources\MenuItemResource;

class MenuItemController extends Controller
{
    /**
     * Display a listing of the menu items.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 50);
        $menuId = $request->input('menu_id');

        $query = MenuItem::with(['page', 'children']);

        if ($menuId) {
            $query->where('menu_id', $menuId);
        }

        $items = $query->root()->ordered()->paginate($perPage);

        return MenuItemResource::collection($items);
    }

    /**
     * Store a newly created menu item.
     */
    public function store(StoreMenuItemRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? true;

        // Auto-set order if not provided
        if (! isset($data['order'])) {
            $maxOrder = MenuItem::where('menu_id', $data['menu_id'])
                ->where('parent_id', $data['parent_id'] ?? null)
                ->max('order');
            $data['order'] = ($maxOrder ?? 0) + 1;
        }

        $item = MenuItem::create($data);

        return response()->json([
            'message' => 'Élément de menu créé avec succès.',
            'data' => new MenuItemResource($item->load('page')),
        ], 201);
    }

    /**
     * Display the specified menu item.
     */
    public function show(string $id): JsonResponse
    {
        $item = MenuItem::with(['page', 'children', 'parent'])->findOrFail($id);

        return response()->json([
            'data' => new MenuItemResource($item),
        ]);
    }

    /**
     * Update the specified menu item.
     */
    public function update(UpdateMenuItemRequest $request, string $id): JsonResponse
    {
        $item = MenuItem::findOrFail($id);
        $item->update($request->validated());

        return response()->json([
            'message' => 'Élément de menu modifié avec succès.',
            'data' => new MenuItemResource($item->load('page')),
        ]);
    }

    /**
     * Remove the specified menu item.
     */
    public function destroy(string $id): JsonResponse
    {
        $item = MenuItem::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'Élément de menu supprimé avec succès.',
        ]);
    }

    /**
     * Restore a soft deleted menu item.
     */
    public function restore(string $id): JsonResponse
    {
        $item = MenuItem::onlyTrashed()->findOrFail($id);
        $item->restore();

        return response()->json([
            'message' => 'Élément de menu restauré avec succès.',
            'data' => new MenuItemResource($item),
        ]);
    }

    /**
     * Permanently delete a menu item.
     */
    public function forceDelete(string $id): JsonResponse
    {
        $item = MenuItem::withTrashed()->findOrFail($id);
        $item->forceDelete();

        return response()->json([
            'message' => 'Élément de menu supprimé définitivement.',
        ]);
    }

    /**
     * Reorder menu items.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:cms_menu_items,id'],
            'items.*.order' => ['required', 'integer'],
            'items.*.parent_id' => ['nullable', 'exists:cms_menu_items,id'],
        ]);

        foreach ($request->input('items') as $item) {
            MenuItem::where('id', $item['id'])->update([
                'order' => $item['order'],
                'parent_id' => $item['parent_id'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Ordre des éléments mis à jour.',
        ]);
    }
}
