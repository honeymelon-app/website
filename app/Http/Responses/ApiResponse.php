<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Standardized API response helper for consistent JSON responses.
 */
final class ApiResponse
{
    /**
     * Return a success response.
     */
    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $status = Response::HTTP_OK,
        ?string $dataKey = 'data'
    ): JsonResponse {
        // If dataKey is null and data is an array, return data at root level
        if ($dataKey === null && is_array($data)) {
            return response()->json($data, $status);
        }

        $response = [
            'success' => true,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response[$dataKey] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Return an error response.
     */
    public static function error(
        string $message,
        mixed $errors = null,
        int $status = Response::HTTP_BAD_REQUEST,
        ?string $errorCode = null
    ): JsonResponse {
        $response = [
            'success' => false,
        ];

        // For 500 errors, use 'message' and 'error' structure for clarity
        if ($status >= 500) {
            $response['message'] = $message;
            if ($errors !== null && is_string($errors)) {
                $response['error'] = $errors;
            }
        } else {
            $response['error'] = $message;
        }

        if ($errorCode !== null) {
            $response['error_code'] = $errorCode;
        }

        if ($errors !== null && ! is_string($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a created response (201).
     */
    public static function created(
        mixed $data = null,
        ?string $message = null,
        ?string $dataKey = 'data'
    ): JsonResponse {
        return self::success($data, $message, Response::HTTP_CREATED, $dataKey);
    }

    /**
     * Return a no content response (204).
     */
    public static function noContent(): Response
    {
        return response()->noContent();
    }

    /**
     * Return a not found response (404).
     */
    public static function notFound(
        string $message = 'Resource not found',
        ?string $errorCode = null
    ): JsonResponse {
        return self::error($message, null, Response::HTTP_NOT_FOUND, $errorCode);
    }

    /**
     * Return a validation error response (422).
     */
    public static function validationError(
        mixed $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return self::error($message, $errors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Return an unauthorized response (401).
     */
    public static function unauthorized(
        string $message = 'Unauthorized',
        ?string $errorCode = null
    ): JsonResponse {
        return self::error($message, null, Response::HTTP_UNAUTHORIZED, $errorCode);
    }

    /**
     * Return a forbidden response (403).
     */
    public static function forbidden(
        string $message = 'Forbidden',
        ?string $errorCode = null
    ): JsonResponse {
        return self::error($message, null, Response::HTTP_FORBIDDEN, $errorCode);
    }

    /**
     * Return a conflict response (409).
     */
    public static function conflict(
        string $message = 'Resource conflict',
        ?string $errorCode = null
    ): JsonResponse {
        return self::error($message, null, Response::HTTP_CONFLICT, $errorCode);
    }

    /**
     * Return a server error response (500).
     */
    public static function serverError(
        string $message = 'Internal server error',
        ?string $details = null
    ): JsonResponse {
        return self::error($message, $details, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
