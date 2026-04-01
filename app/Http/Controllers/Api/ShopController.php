<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShopBrand;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * GET /api/v/shop/products
     *
     * List active products with optional search, category, and brand filters.
     * Returns paginated JSON matching the Livewire ProductCatalog logic.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search'   => 'nullable|string|max:200',
            'category' => 'nullable|string|max:100',
            'brand'    => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:48',
        ]);

        $query = ShopProduct::query()
            ->where('active', true)
            ->with(['brand:id,name,slug,logo_url', 'category:id,name,slug,icon']);

        $search   = $request->input('search', '');
        $category = $request->input('category', '');
        $brand    = $request->input('brand', '');

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($category !== '') {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        if ($brand !== '') {
            $query->whereHas('brand', fn ($q) => $q->where('slug', $brand));
        }

        $perPage  = (int) $request->input('per_page', 12);
        $products = $query->orderByDesc('featured')->latest()->paginate($perPage);

        // Sidebar filter options
        $categories = ShopCategory::where('active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'icon']);

        $brands = ShopBrand::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'logo_url']);

        return response()->json([
            'products'   => $products,
            'categories' => $categories,
            'brands'     => $brands,
        ]);
    }

    /**
     * GET /api/v/shop/products/{slug}
     *
     * Single product detail with related products from the same category.
     * Increments view count (mirrors Livewire ProductDetail behaviour).
     */
    public function show(string $slug): JsonResponse
    {
        $product = ShopProduct::where('slug', $slug)
            ->where('active', true)
            ->with(['brand:id,name,slug,logo_url', 'category:id,name,slug,icon'])
            ->firstOrFail();

        // Increment view count
        $product->increment('views');

        // Related products from same category
        $related = ShopProduct::where('active', true)
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->limit(4)
            ->get();

        return response()->json([
            'product'         => $product,
            'relatedProducts' => $related,
        ]);
    }
}
