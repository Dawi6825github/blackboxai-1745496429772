<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,user',
            'status' => 'required|boolean',
        ];

        // For updates, make username and email unique except for the current user
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['username'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($this->route('user')),
            ];
            
            $rules['email'] = [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->route('user')),
            ];
            
            $rules['password'] = 'nullable|string|min:8|confirmed';
        } else {
            $rules['username'] = 'required|string|max:255|unique:users';
            $rules['email'] = 'nullable|string|email|max:255|unique:users';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        return $rules;
    }
}
