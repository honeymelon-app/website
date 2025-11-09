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

        abort_if(! $key || ! $secret, 403, 'Invalid client credentials.');

        $client = Client::where('key', $key)->first();

        abort_if(! $client || ! Hash::check($secret, $client->secret), 403, 'Invalid client credentials.');

        return $next($request);
    }
}
