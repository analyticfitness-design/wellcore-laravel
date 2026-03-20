<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Recuperar Contraseña — WellCore'])]
class ForgotPassword extends Component
{
    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
