<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RoundRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'status' => 'required|boolean',
            'min_bet' => 'required|numeric|min:0',
            'max_bet' => 'required|numeric|gt:min_bet',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'pattern_ids' => 'required|array',
            'pattern_ids.*' => 'exists:patterns,id',
        ];
    }
}
