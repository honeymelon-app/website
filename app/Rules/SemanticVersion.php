<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a semantic version format (e.g., 1.2.3, v1.2.3).
 */
class SemanticVersion implements ValidationRule
{
    public function __construct(
        private readonly bool $allowVPrefix = true
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        $pattern = $this->allowVPrefix
            ? '/^v?\d+\.\d+\.\d+$/'
            : '/^\d+\.\d+\.\d+$/';

        if (! preg_match($pattern, $value)) {
            $fail('The :attribute must be a valid semantic version.');
        }
    }
}
