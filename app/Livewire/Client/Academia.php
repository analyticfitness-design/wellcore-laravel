<?php

namespace App\Livewire\Client;

use App\Models\AcademyContent;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Academia — WellCore'])]
class Academia extends Component
{
    public string $search = '';
    public string $categoryFilter = '';
    public ?int $selectedContentId = null;

    public function selectContent(int $id): void
    {
        $this->selectedContentId = $this->selectedContentId === $id ? null : $id;
    }

    public function render()
    {
        $query = AcademyContent::where('active', true)
            ->orderBy('sort_order');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        $contents = $query->limit(100)->get(['id', 'title', 'category', 'description', 'content_type', 'sort_order', 'active']);

        // Categories are nearly static data — cache for 1 hour (TTL 3600s).
        // This eliminates a second query on every keystroke in the search field.
        $categories = Cache::remember('academia:categories', 3600, function () {
            return AcademyContent::where('active', true)
                ->distinct()
                ->pluck('category')
                ->filter()
                ->values();
        });

        $selectedContent = $this->selectedContentId
            ? $contents->firstWhere('id', $this->selectedContentId)
            : null;

        return view('livewire.client.academia', compact('contents', 'categories', 'selectedContent'));
    }
}
