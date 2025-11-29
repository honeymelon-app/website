<?php

declare(strict_types=1);

namespace App\Http\Requests;

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
            'amount' => ['required', 'integer', 'min:100'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'success_url' => ['required', 'url'],
            'cancel_url' => ['required', 'url'],
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
            'provider.required' => 'A payment provider is required',
            'provider.in' => 'The provider must be stripe',
            'amount.required' => 'An amount is required',
            'amount.integer' => 'The amount must be an integer (in cents)',
            'amount.min' => 'The amount must be at least $1.00 (100 cents)',
            'currency.size' => 'The currency must be a 3-letter ISO code (e.g., usd, eur)',
            'success_url.required' => 'A success URL is required',
            'success_url.url' => 'The success URL must be a valid URL',
            'cancel_url.required' => 'A cancel URL is required',
            'cancel_url.url' => 'The cancel URL must be a valid URL',
            'email.email' => 'The email must be a valid email address',
        ];
    }
}
