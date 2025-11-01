<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Models\Update;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class UpdateManifestResponse implements Responsable
{
    /**
     * Create a new response instance.
     */
    public function __construct(
        private readonly array $manifest,
        private readonly int $ttl = 300
    ) {}

    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request): Response
    {
        return response()->json($this->manifest)
            ->header('Cache-Control', "public, max-age={$this->ttl}, stale-while-revalidate=60");
    }

    /**
     * Create a response from an Update model.
     */
    public static function fromUpdate(Update $update, int $ttl = 300): self
    {
        return new self($update->manifest, $ttl);
    }
}
