<?php

namespace Modules\Cms\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Cms\Entities\Block;
use Modules\Cms\Http\Requests\StoreBlockRequest;
use Modules\Cms\Http\Requests\UpdateBlockRequest;
use Modules\Cms\Http\Resources\BlockResource;

class BlockController extends Controller
{
    /**
     * Display a listing of the blocks.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $type = $request->input('type');
        $pageId = $request->input('page_id');

        $query = Block::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('identifier', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($pageId) {
            $query->where('page_id', $pageId);
        }

        $blocks = $query->ordered()->paginate($perPage);

        return BlockResource::collection($blocks);
    }

    /**
     * Store a newly created block.
     */
    public function store(StoreBlockRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? true;

        $block = Block::create($data);

        return response()->json([
            'message' => 'Bloc créé avec succès.',
            'data' => new BlockResource($block),
        ], 201);
    }

    /**
     * Display the specified block.
     */
    public function show(string $id): JsonResponse
    {
        $block = Block::with('page')->findOrFail($id);

        return response()->json([
            'data' => new BlockResource($block),
        ]);
    }

    /**
     * Update the specified block.
     */
    public function update(UpdateBlockRequest $request, string $id): JsonResponse
    {
        $block = Block::findOrFail($id);
        $block->update($request->validated());

        return response()->json([
            'message' => 'Bloc modifié avec succès.',
            'data' => new BlockResource($block),
        ]);
    }

    /**
     * Remove the specified block.
     */
    public function destroy(string $id): JsonResponse
    {
        $block = Block::findOrFail($id);
        $block->delete();

        return response()->json([
            'message' => 'Bloc supprimé avec succès.',
        ]);
    }

    /**
     * Restore a soft deleted block.
     */
    public function restore(string $id): JsonResponse
    {
        $block = Block::onlyTrashed()->findOrFail($id);
        $block->restore();

        return response()->json([
            'message' => 'Bloc restauré avec succès.',
            'data' => new BlockResource($block),
        ]);
    }

    /**
     * Permanently delete a block.
     */
    public function forceDelete(string $id): JsonResponse
    {
        $block = Block::withTrashed()->findOrFail($id);
        $block->forceDelete();

        return response()->json([
            'message' => 'Bloc supprimé définitivement.',
        ]);
    }

    /**
     * Get available block types.
     */
    public function types(): JsonResponse
    {
        $types = [
            'text' => 'Texte simple',
            'html' => 'HTML personnalisé',
            'hero' => 'Section héro',
            'cta' => 'Appel à l\'action',
            'features' => 'Liste de fonctionnalités',
            'testimonials' => 'Témoignages',
            'gallery' => 'Galerie d\'images',
            'video' => 'Vidéo',
            'contact' => 'Formulaire de contact',
            'faq' => 'FAQ',
            'pricing' => 'Tableau de prix',
            'team' => 'Équipe',
            'stats' => 'Statistiques',
            'newsletter' => 'Newsletter',
        ];

        return response()->json([
            'data' => $types,
        ]);
    }

    /**
     * Reorder blocks.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'blocks' => ['required', 'array'],
            'blocks.*.id' => ['required', 'exists:cms_blocks,id'],
            'blocks.*.order' => ['required', 'integer'],
        ]);

        foreach ($request->input('blocks') as $item) {
            Block::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Ordre des blocs mis à jour.',
        ]);
    }
}
