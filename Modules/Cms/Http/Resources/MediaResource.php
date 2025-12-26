<?php

namespace Modules\Cms\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'filename' => $this->filename,
            'path' => $this->path,
            'disk' => $this->disk,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'human_size' => $this->human_size,
            'width' => $this->width,
            'height' => $this->height,
            'alt' => $this->alt,
            'title' => $this->title,
            'caption' => $this->caption,
            'folder' => $this->folder,
            'metadata' => $this->metadata,
            'is_active' => $this->is_active,
            'url' => $this->url,
            'is_image' => $this->isImage(),
            'is_video' => $this->isVideo(),
            'is_document' => $this->isDocument(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
