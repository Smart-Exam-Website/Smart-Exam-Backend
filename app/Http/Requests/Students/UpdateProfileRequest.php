<?php

namespace App\Http\Requests\Students;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'firstName' => 'string|max:255',
            'lastName' => 'string|max:255',
            'email' => 'email|max:255|unique:users',
            'password' => 'min:6',
            'gender' => 'string|in:male,female',
            'phone' => 'unique:users|digits:11',
            'department' => 'string|max:255',
            'school' => 'string|max:255',
            'studentCode' => 'string|unique:students'
        ];
    }
}
