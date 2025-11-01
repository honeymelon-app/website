<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublishReleaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // TODO: Add authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tag' => ['required', 'string', 'regex:/^v?\d+\.\d+\.\d+$/'],
            'channel' => ['required', 'string', Rule::in(['stable', 'beta'])],
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
            'tag.required' => 'A release tag is required',
            'tag.regex' => 'Tag must be a valid semantic version (e.g., v1.2.3 or 1.2.3)',
            'channel.required' => 'A release channel is required',
            'channel.in' => 'Channel must be either stable or beta',
        ];
    }
}
