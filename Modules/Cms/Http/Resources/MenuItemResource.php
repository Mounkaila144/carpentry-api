<?php

namespace Modules\Cms\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'menu_id' => $this->menu_id,
            'parent_id' => $this->parent_id,
            'title' => $this->title,
            'url' => $this->url,
            'route' => $this->route,
            'route_params' => $this->route_params,
            'page_id' => $this->page_id,
            'target' => $this->target,
            'icon' => $this->icon,
            'css_class' => $this->css_class,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'computed_url' => $this->computed_url,
            'menu' => new MenuResource($this->whenLoaded('menu')),
            'parent' => new MenuItemResource($this->whenLoaded('parent')),
            'children' => MenuItemResource::collection($this->whenLoaded('children')),
            'page' => new PageResource($this->whenLoaded('page')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
