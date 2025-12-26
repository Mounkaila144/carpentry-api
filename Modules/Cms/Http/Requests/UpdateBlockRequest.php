<?php

namespace Modules\Cms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBlockRequest extends FormRequest
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
        $blockId = $this->route('id');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'identifier' => ['sometimes', 'string', 'max:255', Rule::unique('cms_blocks', 'identifier')->ignore($blockId)],
            'type' => ['nullable', 'string', 'max:100'],
            'content' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
            'page_id' => ['nullable', 'exists:cms_pages,id'],
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
            'name.required' => 'Le nom est obligatoire.',
            'identifier.unique' => 'Cet identifiant est déjà utilisé.',
            'page_id.exists' => 'La page sélectionnée n\'existe pas.',
        ];
    }
}
