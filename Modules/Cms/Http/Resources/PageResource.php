<?php

namespace Modules\Cms\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'template' => $this->template,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'featured_image' => $this->featured_image,
            'parent_id' => $this->parent_id,
            'status' => $this->status,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'published_at' => $this->published_at?->toIso8601String(),
            'parent' => new PageResource($this->whenLoaded('parent')),
            'children' => PageResource::collection($this->whenLoaded('children')),
            'blocks' => BlockResource::collection($this->whenLoaded('blocks')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
