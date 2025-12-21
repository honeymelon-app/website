<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class PricingController extends Controller
{
    /**
     * Display the pricing page with product information.
     */
    public function __invoke(): Response
    {
        $product = Product::where('slug', 'honeymelon')
            ->where('is_active', true)
            ->first();

        return Inertia::render('Pricing', [
            'product' => $product ? [
                'slug' => $product->slug,
                'name' => $product->name,
                'description' => $product->description,
                'price_cents' => $product->price_cents,
                'currency' => $product->currency,
                'formatted_price' => $product->formatted_price,
            ] : null,
            'docsUrl' => config('app.docs_url'),
        ]);
    }
}
