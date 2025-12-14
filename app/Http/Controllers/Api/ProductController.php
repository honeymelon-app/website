<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Get active products with pricing information.
     *
     * Returns:
     * {
     *   "products": [
     *     {
     *       "slug": "honeymelon",
     *       "name": "Honeymelon",
     *       "description": "...",
     *       "price_cents": 2900,
     *       "currency": "usd",
     *       "formatted_price": "$29.00"
     *     }
     *   ]
     * }
     */
    public function index(): JsonResponse
    {
        $products = Product::where('is_active', true)
            ->get(['slug', 'name', 'description', 'price_cents', 'currency']);

        return response()->json([
            'products' => $products->map(fn (Product $product) => [
                'slug' => $product->slug,
                'name' => $product->name,
                'description' => $product->description,
                'price_cents' => $product->price_cents,
                'currency' => $product->currency,
                'formatted_price' => $product->formatted_price,
            ]),
        ]);
    }

    /**
     * Get a single product by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([
            'product' => [
                'slug' => $product->slug,
                'name' => $product->name,
                'description' => $product->description,
                'price_cents' => $product->price_cents,
                'currency' => $product->currency,
                'formatted_price' => $product->formatted_price,
            ],
        ]);
    }
}
