<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Mindfulness — WellCore'])]
class Mindfulness extends Component
{
    public string $activeSession = '';
    public bool $sessionActive = false;
    public int $sessionDuration = 0; // en segundos

    public function startSession(string $type): void
    {
        $this->activeSession = $type;
        $this->sessionActive = true;
        $this->sessionDuration = match ($type) {
            'breathing'     => 300,  // 5 minutos
            'meditation'    => 600,  // 10 minutos
            'body-scan'     => 900,  // 15 minutos
            'visualization' => 480,  // 8 minutos
            default         => 300,
        };
    }

    public function endSession(): void
    {
        $this->sessionActive = false;
        $this->activeSession = '';
        $this->sessionDuration = 0;
    }

    public function render()
    {
        $sessions = [
            [
                'id'          => 'breathing',
                'title'       => 'Respiración 4-7-8',
                'description' => 'Inhala 4s, retén 7s, exhala 8s. Reduce cortisol y activa el nervio vago.',
                'duration'    => '5 min',
                'emoji'       => '🌬️',
                'benefit'     => 'Reduce estrés',
            ],
            [
                'id'          => 'meditation',
                'title'       => 'Meditación de Atención Plena',
                'description' => 'Observa tus pensamientos sin juzgar. Mejora el foco y la recuperación.',
                'duration'    => '10 min',
                'emoji'       => '🧘',
                'benefit'     => 'Mejora foco',
            ],
            [
                'id'          => 'body-scan',
                'title'       => 'Body Scan',
                'description' => 'Recorre cada parte de tu cuerpo con atención. Detecta tensión muscular.',
                'duration'    => '15 min',
                'emoji'       => '🔍',
                'benefit'     => 'Recuperación activa',
            ],
            [
                'id'          => 'visualization',
                'title'       => 'Visualización de Rendimiento',
                'description' => 'Visualiza tu próximo entrenamiento o competición. Técnica usada por atletas de élite.',
                'duration'    => '8 min',
                'emoji'       => '🏆',
                'benefit'     => 'Mejora rendimiento',
            ],
        ];

        return view('livewire.client.mindfulness', compact('sessions'));
    }
}
