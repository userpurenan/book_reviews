<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class BookRequest extends FormRequest
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

    protected function failedValidation(Validator $validator)
    {
        $response  = $validator->errors()->toArray();
    
        throw new HttpResponseException(response()->json([ 'message' => 'バリデーションエラー',
                                                           'error' => $response], 422, [], JSON_UNESCAPED_UNICODE));   
    }
}
