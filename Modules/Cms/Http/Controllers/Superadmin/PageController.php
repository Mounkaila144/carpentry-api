<?php

namespace Modules\Cms\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Modules\Cms\Entities\Page;
use Modules\Cms\Http\Requests\StorePageRequest;
use Modules\Cms\Http\Requests\UpdatePageRequest;
use Modules\Cms\Http\Resources\PageResource;

class PageController extends Controller
{
    /**
     * Display a listing of the pages.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $status = $request->input('status');
        $isActive = $request->input('is_active');
        $parentId = $request->input('parent_id');

        $query = Page::with(['parent', 'children']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        if ($parentId !== null) {
            if ($parentId === 'null' || $parentId === '') {
                $query->root();
            } else {
                $query->where('parent_id', $parentId);
            }
        }

        $pages = $query->ordered()->paginate($perPage);

        return PageResource::collection($pages);
    }

    /**
     * Store a newly created page.
     */
    public function store(StorePageRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? true;
        $data['status'] = $data['status'] ?? Page::STATUS_DRAFT;

        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Ensure unique slug
        $data['slug'] = $this->ensureUniqueSlug($data['slug']);

        // Auto-set order if not provided
        if (! isset($data['order'])) {
            $maxOrder = Page::where('parent_id', $data['parent_id'] ?? null)->max('order');
            $data['order'] = ($maxOrder ?? 0) + 1;
        }

        $page = Page::create($data);

        return response()->json([
            'message' => 'Page créée avec succès.',
            'data' => new PageResource($page->load(['parent', 'blocks'])),
        ], 201);
    }

    /**
     * Display the specified page.
     */
    public function show(string $id): JsonResponse
    {
        $page = Page::with(['parent', 'children', 'blocks'])->findOrFail($id);

        return response()->json([
            'data' => new PageResource($page),
        ]);
    }

    /**
     * Update the specified page.
     */
    public function update(UpdatePageRequest $request, string $id): JsonResponse
    {
        $page = Page::findOrFail($id);
        $data = $request->validated();

        // Ensure unique slug if changed
        if (isset($data['slug']) && $data['slug'] !== $page->slug) {
            $data['slug'] = $this->ensureUniqueSlug($data['slug'], $page->id);
        }

        $page->update($data);

        return response()->json([
            'message' => 'Page modifiée avec succès.',
            'data' => new PageResource($page->load(['parent', 'blocks'])),
        ]);
    }

    /**
     * Remove the specified page (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        $page = Page::findOrFail($id);
        $page->delete();

        return response()->json([
            'message' => 'Page supprimée avec succès.',
        ]);
    }

    /**
     * Restore a soft deleted page.
     */
    public function restore(string $id): JsonResponse
    {
        $page = Page::onlyTrashed()->findOrFail($id);
        $page->restore();

        return response()->json([
            'message' => 'Page restaurée avec succès.',
            'data' => new PageResource($page),
        ]);
    }

    /**
     * Permanently delete a page.
     */
    public function forceDelete(string $id): JsonResponse
    {
        $page = Page::withTrashed()->findOrFail($id);
        $page->forceDelete();

        return response()->json([
            'message' => 'Page supprimée définitivement.',
        ]);
    }

    /**
     * Publish a page.
     */
    public function publish(string $id): JsonResponse
    {
        $page = Page::findOrFail($id);
        $page->update([
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        return response()->json([
            'message' => 'Page publiée avec succès.',
            'data' => new PageResource($page),
        ]);
    }

    /**
     * Unpublish a page (set to draft).
     */
    public function unpublish(string $id): JsonResponse
    {
        $page = Page::findOrFail($id);
        $page->update([
            'status' => Page::STATUS_DRAFT,
        ]);

        return response()->json([
            'message' => 'Page dépubliée avec succès.',
            'data' => new PageResource($page),
        ]);
    }

    /**
     * Duplicate a page.
     */
    public function duplicate(string $id): JsonResponse
    {
        $page = Page::with('blocks')->findOrFail($id);

        $newPage = $page->replicate();
        $newPage->title = $page->title.' (copie)';
        $newPage->slug = $this->ensureUniqueSlug($page->slug.'-copy');
        $newPage->status = Page::STATUS_DRAFT;
        $newPage->published_at = null;
        $newPage->save();

        // Duplicate blocks
        foreach ($page->blocks as $block) {
            $newBlock = $block->replicate();
            $newBlock->page_id = $newPage->id;
            $newBlock->identifier = $block->identifier.'_'.$newPage->id;
            $newBlock->save();
        }

        return response()->json([
            'message' => 'Page dupliquée avec succès.',
            'data' => new PageResource($newPage->load('blocks')),
        ], 201);
    }

    /**
     * Reorder pages.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'pages' => ['required', 'array'],
            'pages.*.id' => ['required', 'exists:cms_pages,id'],
            'pages.*.order' => ['required', 'integer'],
            'pages.*.parent_id' => ['nullable', 'exists:cms_pages,id'],
        ]);

        foreach ($request->input('pages') as $item) {
            Page::where('id', $item['id'])->update([
                'order' => $item['order'],
                'parent_id' => $item['parent_id'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Ordre des pages mis à jour.',
        ]);
    }

    /**
     * Get available templates.
     */
    public function templates(): JsonResponse
    {
        $templates = [
            'default' => 'Page standard',
            'home' => 'Page d\'accueil',
            'contact' => 'Page de contact',
            'landing' => 'Landing page',
            'full-width' => 'Pleine largeur',
            'sidebar' => 'Avec barre latérale',
        ];

        return response()->json([
            'data' => $templates,
        ]);
    }

    /**
     * Ensure the slug is unique.
     */
    protected function ensureUniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $originalSlug = $slug;
        $counter = 1;

        $query = Page::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;

            $query = Page::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }
}
