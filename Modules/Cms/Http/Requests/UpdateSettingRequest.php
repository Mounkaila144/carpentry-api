<?php

namespace Modules\Cms\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
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
        $settingId = $this->route('id');

        return [
            'key' => ['sometimes', 'string', 'max:255', Rule::unique('cms_settings', 'key')->ignore($settingId)],
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
            'key.unique' => 'Cette clé est déjà utilisée.',
            'type.in' => 'Le type sélectionné n\'est pas valide.',
        ];
    }
}
