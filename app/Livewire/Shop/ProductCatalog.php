<?php

namespace App\Livewire\Shop;

use App\Models\ShopBrand;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.shop', ['title' => 'Tienda WellCore'])]
class ProductCatalog extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $brand = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingBrand(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = '';
        $this->brand = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = ShopProduct::query()
            ->where('active', true)
            ->with(['brand', 'category']);

        if ($this->search !== '') {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->category !== '') {
            $query->whereHas('category', fn ($q) => $q->where('slug', $this->category));
        }

        if ($this->brand !== '') {
            $query->whereHas('brand', fn ($q) => $q->where('slug', $this->brand));
        }

        $products = $query->orderByDesc('featured')->latest()->paginate(12);

        $categories = ShopCategory::where('active', true)->orderBy('sort_order')->get();
        $brands = ShopBrand::where('active', true)->orderBy('name')->get();

        return view('livewire.shop.product-catalog', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }
}
