<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class Academia extends Component
{
    public function placeholder()
    {
        return view('livewire.placeholders.loading-skeleton');
    }

    public function render()
    {
        return view('livewire.client.academia');
    }
}
