<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\ValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class CreateCheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'provider' => ['required', 'in:stripe'],
            'product_id' => [...ValidationRules::uuid(), 'exists:products,id'],
            'success_url' => ValidationRules::url(),
            'cancel_url' => ValidationRules::url(),
            'email' => ['sometimes', 'email'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'provider.in' => 'The provider must be stripe',
            'product_id.exists' => 'The selected product does not exist',
        ];
    }
}
