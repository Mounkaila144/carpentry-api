<?php

namespace Modules\Cms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuRequest extends FormRequest
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
        $menuId = $this->route('id');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'identifier' => ['sometimes', 'string', 'max:255', Rule::unique('cms_menus', 'identifier')->ignore($menuId)],
            'description' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'identifier.unique' => 'Cet identifiant est déjà utilisé.',
        ];
    }
}
