<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexFileManagerRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sort_field' => ['required_with:sort_order', 'in:name,created_at'],
            'sort_order' => ['sometimes', 'required', 'in:asc,desc'],
        ];
    }
}
