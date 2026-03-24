<?php

namespace App\Livewire\Rise;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.rise', ['title' => 'Tracking Diario'])]
class DailyTracking extends Component
{
    public function mount(): void
    {
        $this->redirect(route('rise.habits'), navigate: true);
    }

    public function render()
    {
        return view('livewire.rise.daily-tracking');
    }
}
