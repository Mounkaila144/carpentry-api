<?php

namespace Modules\Cms\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Cms\Entities\Media;
use Modules\Cms\Http\Resources\MediaResource;

class MediaController extends Controller
{
    /**
     * Display a listing of the media.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 24);
        $search = $request->input('search');
        $type = $request->input('type');
        $folder = $request->input('folder');

        $query = Media::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('filename', 'like', "%{$search}%")
                    ->orWhere('alt', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->ofType($type);
        }

        if ($folder !== null) {
            $query->inFolder($folder ?: null);
        }

        $media = $query->latest()->paginate($perPage);

        return MediaResource::collection($media);
    }

    /**
     * Upload a new media file.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200'], // 50MB max
            'folder' => ['nullable', 'string', 'max:255'],
            'alt' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
        ]);

        $file = $request->file('file');
        $folder = $request->input('folder', 'uploads');

        // Generate unique filename
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs("cms/{$folder}", $filename, 'public');

        // Get image dimensions if applicable
        $width = null;
        $height = null;
        if (str_starts_with($file->getMimeType(), 'image/')) {
            try {
                [$width, $height] = getimagesize($file->getRealPath());
            } catch (\Exception $e) {
                // Ignore if we can't get dimensions
            }
        }

        $media = Media::create([
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => 'public',
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'alt' => $request->input('alt'),
            'title' => $request->input('title'),
            'caption' => $request->input('caption'),
            'folder' => $folder,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Fichier uploadé avec succès.',
            'data' => new MediaResource($media),
        ], 201);
    }

    /**
     * Display the specified media.
     */
    public function show(string $id): JsonResponse
    {
        $media = Media::findOrFail($id);

        return response()->json([
            'data' => new MediaResource($media),
        ]);
    }

    /**
     * Update the specified media metadata.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'alt' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
            'folder' => ['nullable', 'string', 'max:255'],
        ]);

        $media = Media::findOrFail($id);
        $media->update($request->only(['name', 'alt', 'title', 'caption', 'folder']));

        return response()->json([
            'message' => 'Média modifié avec succès.',
            'data' => new MediaResource($media),
        ]);
    }

    /**
     * Remove the specified media (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        $media = Media::findOrFail($id);
        $media->delete();

        return response()->json([
            'message' => 'Fichier supprimé avec succès.',
        ]);
    }

    /**
     * Restore a soft deleted media.
     */
    public function restore(string $id): JsonResponse
    {
        $media = Media::onlyTrashed()->findOrFail($id);
        $media->restore();

        return response()->json([
            'message' => 'Fichier restauré avec succès.',
            'data' => new MediaResource($media),
        ]);
    }

    /**
     * Permanently delete a media.
     */
    public function forceDelete(string $id): JsonResponse
    {
        $media = Media::withTrashed()->findOrFail($id);

        // Delete file from storage
        Storage::disk($media->disk)->delete($media->path);

        $media->forceDelete();

        return response()->json([
            'message' => 'Fichier supprimé définitivement.',
        ]);
    }

    /**
     * Get list of folders.
     */
    public function folders(): JsonResponse
    {
        $folders = Media::select('folder')
            ->whereNotNull('folder')
            ->distinct()
            ->pluck('folder')
            ->sort()
            ->values();

        return response()->json([
            'data' => $folders,
        ]);
    }

    /**
     * Bulk delete media.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:cms_media,id'],
        ]);

        $media = Media::whereIn('id', $request->input('ids'))->get();

        foreach ($media as $item) {
            Storage::disk($item->disk)->delete($item->path);
            $item->forceDelete();
        }

        return response()->json([
            'message' => count($media).' fichier(s) supprimé(s) avec succès.',
        ]);
    }
}
