<?php

namespace Modules\Cms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
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
            'key' => ['required', 'string', 'max:255', 'unique:cms_settings,key'],
            'value' => ['nullable', 'string'],
            'group' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'in:string,integer,float,boolean,json,array'],
            'options' => ['nullable', 'array'],
            'is_public' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'key.required' => 'La clé est obligatoire.',
            'key.unique' => 'Cette clé est déjà utilisée.',
            'type.in' => 'Le type sélectionné n\'est pas valide.',
        ];
    }
}
