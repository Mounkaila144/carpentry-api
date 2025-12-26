<?php

namespace Modules\Cms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuItemRequest extends FormRequest
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
        return [
            'menu_id' => ['required', 'exists:cms_menus,id'],
            'parent_id' => ['nullable', 'exists:cms_menu_items,id'],
            'title' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'route' => ['nullable', 'string', 'max:255'],
            'route_params' => ['nullable', 'array'],
            'page_id' => ['nullable', 'exists:cms_pages,id'],
            'target' => ['nullable', 'string', 'in:_self,_blank,_parent,_top'],
            'icon' => ['nullable', 'string', 'max:100'],
            'css_class' => ['nullable', 'string', 'max:255'],
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
            'menu_id.required' => 'Le menu est obligatoire.',
            'menu_id.exists' => 'Le menu sélectionné n\'existe pas.',
            'title.required' => 'Le titre est obligatoire.',
            'parent_id.exists' => 'L\'élément parent sélectionné n\'existe pas.',
            'page_id.exists' => 'La page sélectionnée n\'existe pas.',
            'target.in' => 'La cible sélectionnée n\'est pas valide.',
        ];
    }
}
