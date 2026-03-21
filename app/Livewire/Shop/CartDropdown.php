<?php

namespace App\Livewire\Shop;

use Livewire\Attributes\On;
use Livewire\Component;

class CartDropdown extends Component
{
    public array $cart = [];
    public int $cartCount = 0;

    public function mount(): void
    {
        $this->loadCart();
    }

    #[On('cart-updated')]
    public function loadCart(): void
    {
        $this->cart = session('cart', []);
        $this->cartCount = array_sum(array_column($this->cart, 'quantity'));
    }

    public function removeFromCart(int $productId): void
    {
        $cart = session('cart', []);
        $cart = array_values(array_filter($cart, fn ($item) => $item['product_id'] !== $productId));
        session(['cart' => $cart]);

        $this->loadCart();
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
        $this->loadCart();
    }

    public function clearCart(): void
    {
        session()->forget('cart');
        $this->loadCart();
        $this->dispatch('toast', type: 'info', message: 'Carrito vaciado');
    }

    public function getCartTotal(): float
    {
        return array_sum(array_map(
            fn ($item) => $item['price'] * $item['quantity'],
            $this->cart
        ));
    }

    public function render()
    {
        return view('livewire.shop.cart-dropdown', [
            'cartTotal' => $this->getCartTotal(),
        ]);
    }
}
