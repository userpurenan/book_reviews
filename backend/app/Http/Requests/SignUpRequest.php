<?php

namespace App\Http\Requests;

use App\Http\Requests\ApiRequest;

class SignUpRequest extends ApiRequest
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
            'name' => ['required'],
            'email' => ['required','email:filter'],
            'password' => ['required','min:5', 'max:15','zxcvbn:1,username,email'],
        ];
    }
}
