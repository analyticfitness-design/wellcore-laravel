<?php

namespace App\Livewire\Client;

use Livewire\Component;

class VideoLibrary extends Component
{
    public function placeholder()
    {
        return view('livewire.placeholders.loading-skeleton');
    }

    public function render()
    {
        return view('livewire.client.video-library');
    }
}
