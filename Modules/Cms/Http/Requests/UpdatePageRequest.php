<?php

namespace Modules\Cms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Cms\Entities\Page;

class UpdatePageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $pageId = $this->route('id');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('cms_pages', 'slug')->ignore($pageId)],
            'content' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'template' => ['nullable', 'string', 'max:100'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:cms_pages,id'],
            'status' => ['nullable', 'string', 'in:'.implode(',', [Page::STATUS_DRAFT, Page::STATUS_PUBLISHED, Page::STATUS_ARCHIVED])],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'slug.unique' => 'Ce slug est déjà utilisé.',
            'parent_id.exists' => 'La page parente sélectionnée n\'existe pas.',
            'status.in' => 'Le statut sélectionné n\'est pas valide.',
        ];
    }
}
