<?php

namespace Modules\Cms\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Cms\Entities\Page;
use Modules\Cms\Http\Resources\PageResource;

class PageController extends Controller
{
    /**
     * Display a listing of published pages.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 15);
        $template = $request->input('template');
        $parentId = $request->input('parent_id');

        $query = Page::published()->with(['blocks' => function ($q) {
            $q->active()->ordered();
        }]);

        if ($template) {
            $query->where('template', $template);
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
     * Display a page by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $page = Page::published()
            ->with(['blocks' => function ($q) {
                $q->active()->ordered();
            }])
            ->bySlug($slug)
            ->firstOrFail();

        return response()->json([
            'data' => new PageResource($page),
        ]);
    }

    /**
     * Get the home page.
     */
    public function home(): JsonResponse
    {
        $page = Page::published()
            ->with(['blocks' => function ($q) {
                $q->active()->ordered();
            }])
            ->where('template', 'home')
            ->first();

        if (! $page) {
            $page = Page::published()
                ->with(['blocks' => function ($q) {
                    $q->active()->ordered();
                }])
                ->bySlug('home')
                ->first();
        }

        if (! $page) {
            return response()->json([
                'message' => 'Page d\'accueil non trouvée.',
            ], 404);
        }

        return response()->json([
            'data' => new PageResource($page),
        ]);
    }

    /**
     * Get page tree (for navigation).
     */
    public function tree(): JsonResponse
    {
        $pages = Page::published()
            ->root()
            ->with(['children' => function ($q) {
                $q->published()->ordered();
            }])
            ->ordered()
            ->get(['id', 'title', 'slug', 'parent_id', 'order']);

        return response()->json([
            'data' => $pages,
        ]);
    }
}
