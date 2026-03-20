<?php

namespace App\Livewire\Shop;

use App\Models\ShopProduct;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.shop', ['title' => 'Producto'])]
class ProductDetail extends Component
{
    public ShopProduct $product;
    public array $relatedProducts = [];

    public function mount(string $slug): void
    {
        $this->product = ShopProduct::where('slug', $slug)
            ->where('active', true)
            ->with(['brand', 'category'])
            ->firstOrFail();

        // Increment view count
        $this->product->increment('views');

        // Load related products from same category
        $this->relatedProducts = ShopProduct::where('active', true)
            ->where('id', '!=', $this->product->id)
            ->where('category_id', $this->product->category_id)
            ->limit(4)
            ->get()
            ->toArray();
    }

    public function addToCart(): void
    {
        // TODO: Implement cart functionality
        $this->dispatch('notify', message: 'Producto agregado al carrito');
    }

    public function render()
    {
        return view('livewire.shop.product-detail');
    }
}
