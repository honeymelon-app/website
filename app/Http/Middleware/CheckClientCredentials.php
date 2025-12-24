<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class CheckClientCredentials
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Client-Key');
        $secret = $request->header('X-Client-Secret');

        if (! $key || ! $secret) {
            return response()->json(['message' => 'Invalid client credentials.'], 403);
        }

        $client = Client::where('key', $key)->first();

        if (! $client || ! Hash::check($secret, $client->secret)) {
            return response()->json(['message' => 'Invalid client credentials.'], 403);
        }

        if ($client->revoked_at) {
            return response()->json(['message' => 'Client has been revoked.'], 403);
        }

        $client->touch('last_used_at');

        return $next($request);
    }
}
