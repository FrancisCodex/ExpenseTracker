<?php

namespace App\Http\Requests\Income;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateIncomeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['string', 'required', 'max:100'],
            'amount' => ['numeric', 'required'],
            'date' => ['date', 'required'],
            'categories' => ['required'],
            'description' => ['string', 'nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'categories.required' => 'Please select at least one category',
        ];
    }
}
