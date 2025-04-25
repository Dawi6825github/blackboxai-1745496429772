<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class BetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isActive();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'round_id' => 'required|exists:rounds,id',
            'pattern_id' => 'required|exists:patterns,id',
            'amount' => 'required|numeric|min:0',
            'card_ids' => 'required|array|min:1',
            'card_ids.*' => 'exists:cards,id',
        ];
    }
}
