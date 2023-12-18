<?php

namespace App\Http\Requests;

use App\Http\Requests\ApiRequest;

class BookRequest extends ApiRequest
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
            'offset' => 'nullable',
            'title_keyword' => 'nullable|string|regex:/^[a-zA-Z0-9ぁ-んァ-ヶ一-龥々]+$/u',
        ];
    }
}
