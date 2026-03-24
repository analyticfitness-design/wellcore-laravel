<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Registro Nutricional — WellCore'])]
class EvidenceHacks extends Component
{
    public function render()
    {
        return view('livewire.client.evidence-hacks');
    }
}
