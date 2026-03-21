<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class WorkoutTimer extends Component
{
    public function render()
    {
        return view('livewire.client.workout-timer');
    }
}
