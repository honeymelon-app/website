<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Common validation rules used across the application.
 */
final class ValidationRules
{
    /**
     * Standard UUID validation rule.
     *
     * @return array<int, string>
     */
    public static function uuid(): array
    {
        return ['required', 'uuid'];
    }

    /**
     * Email validation rules.
     *
     * @return array<int, string>
     */
    public static function email(bool $required = true): array
    {
        return array_filter([
            $required ? 'required' : 'sometimes',
            'email',
            'max:255',
        ]);
    }

    /**
     * Required string with max length.
     *
     * @return array<int, string>
     */
    public static function requiredString(int $maxLength = 255): array
    {
        return ['required', 'string', "max:{$maxLength}"];
    }

    /**
     * Optional string with max length.
     *
     * @return array<int, string>
     */
    public static function optionalString(int $maxLength = 255): array
    {
        return ['sometimes', 'string', "max:{$maxLength}"];
    }

    /**
     * Platform identifier validation.
     *
     * @return array<int, string>
     */
    public static function platform(): array
    {
        return ['required', 'string', 'max:64'];
    }

    /**
     * Release channel validation.
     *
     * @return array<int, string>
     */
    public static function releaseChannel(): array
    {
        return ['required', 'string', 'in:stable,beta,alpha,rc'];
    }

    /**
     * URL validation.
     *
     * @return array<int, string>
     */
    public static function url(bool $required = true): array
    {
        return array_filter([
            $required ? 'required' : 'sometimes',
            'url',
            'max:2048',
        ]);
    }
}
