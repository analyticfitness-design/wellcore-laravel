<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class EvidenceHacks extends Component
{
    public function render()
    {
        return view('livewire.client.evidence-hacks');
    }
}
