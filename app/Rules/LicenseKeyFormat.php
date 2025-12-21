<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a license key format.
 *
 * Format: Five groups of 5 alphanumeric characters (excluding 0, 1, O, I),
 * separated by hyphens. Example: ABCDE-12345-FGHIJ-67890-KLMNO-PQRST
 */
class LicenseKeyFormat implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        // Pattern: 5-character groups (A-Z, 2-9), separated by hyphens, 6-41 groups
        $pattern = '/^[A-Z2-9]{5}(?:-[A-Z2-9]{5}){5,40}$/';

        if (! preg_match($pattern, $value)) {
            $fail('The :attribute format is invalid.');
        }
    }
}
