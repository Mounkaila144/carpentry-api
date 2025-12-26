<?php

namespace Modules\Cms\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Cms\Entities\Block;
use Modules\Cms\Http\Resources\BlockResource;

class BlockController extends Controller
{
    /**
     * Display a listing of active blocks.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 50);
        $type = $request->input('type');

        $query = Block::active()->whereNull('page_id');

        if ($type) {
            $query->where('type', $type);
        }

        $blocks = $query->ordered()->paginate($perPage);

        return BlockResource::collection($blocks);
    }

    /**
     * Display a block by identifier.
     */
    public function show(string $identifier): JsonResponse
    {
        $block = Block::active()
            ->byIdentifier($identifier)
            ->firstOrFail();

        return response()->json([
            'data' => new BlockResource($block),
        ]);
    }

    /**
     * Get multiple blocks by identifiers.
     */
    public function multiple(Request $request): JsonResponse
    {
        $request->validate([
            'identifiers' => ['required', 'array'],
            'identifiers.*' => ['string'],
        ]);

        $blocks = Block::active()
            ->whereIn('identifier', $request->input('identifiers'))
            ->ordered()
            ->get();

        return response()->json([
            'data' => BlockResource::collection($blocks),
        ]);
    }
}
