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
        $cart = session('cart', []);
        $productId = $this->product->id;

        // Check if item already exists in cart — increment quantity
        $found = false;
        foreach ($cart as &$item) {
            if ($item['product_id'] === $productId) {
                $item['quantity']++;
                $found = true;
                break;
            }
        }
        unset($item);

        if (! $found) {
            $cart[] = [
                'product_id' => $productId,
                'name' => $this->product->name,
                'price' => $this->product->price_cop,
                'quantity' => 1,
                'image' => $this->product->image_url,
            ];
        }

        session(['cart' => $cart]);

        $this->dispatch('cart-updated');
        $this->dispatch('toast', type: 'success', message: 'Producto agregado al carrito');
    }

    public function removeFromCart(int $productId): void
    {
        $cart = session('cart', []);
        $cart = array_values(array_filter($cart, fn ($item) => $item['product_id'] !== $productId));
        session(['cart' => $cart]);

        $this->dispatch('cart-updated');
        $this->dispatch('toast', type: 'info', message: 'Producto eliminado del carrito');
    }

    public function updateQuantity(int $productId, int $qty): void
    {
        $cart = session('cart', []);

        if ($qty < 1) {
            $this->removeFromCart($productId);
            return;
        }

        foreach ($cart as &$item) {
            if ($item['product_id'] === $productId) {
                $item['quantity'] = $qty;
                break;
            }
        }
        unset($item);

        session(['cart' => $cart]);

        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.shop.product-detail');
    }
}
