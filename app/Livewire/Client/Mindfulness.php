<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class Mindfulness extends Component
{
    public function render()
    {
        return view('livewire.client.mindfulness');
    }
}
