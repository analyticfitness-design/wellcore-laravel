<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\Attributes\On;

class RestTimer extends Component
{
    public int $duration = 90;
    public bool $showTimer = false;

    #[On('open-rest-timer')]
    public function openTimer(int $seconds = 90): void
    {
        $this->duration = $seconds;
        $this->showTimer = true;
    }

    public function closeTimer(): void
    {
        $this->showTimer = false;
    }

    public function render()
    {
        return view('livewire.client.rest-timer');
    }
}
