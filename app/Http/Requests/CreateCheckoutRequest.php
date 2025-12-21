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
            'product_id' => ['required', 'uuid', 'exists:products,id'],
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
            'product_id.required' => 'A product is required',
            'product_id.uuid' => 'The product ID must be a valid UUID',
            'product_id.exists' => 'The selected product does not exist',
            'success_url.required' => 'A success URL is required',
            'success_url.url' => 'The success URL must be a valid URL',
            'cancel_url.required' => 'A cancel URL is required',
            'cancel_url.url' => 'The cancel URL must be a valid URL',
            'email.email' => 'The email must be a valid email address',
        ];
    }
}
