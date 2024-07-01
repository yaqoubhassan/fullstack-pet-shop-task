<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = auth()->id();

        return [
            'first_name' => 'filled|string|max:255',
            'last_name' => 'filled|string|max:255',
            'email' => 'filled|string|email|max:255|unique:users,email,' . $userId,
            'password' => 'filled|string|min:8|confirmed',
            'address' => 'filled|string',
            'phone_number' => 'filled|string|max:15',
            'is_marketing' => 'boolean',
            'is_admin' => 'boolean',
            'avatar' => 'nullable|file|mimes:jpg,jpeg,png,bmp|max:2048',
        ];
    }
}
