<?php

namespace App\Livewire\Client;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client', ['title' => 'Evidence Hacks — WellCore'])]
class EvidenceHacks extends Component
{
    public string $categoryFilter = '';

    public function render()
    {
        $hacks = collect([
            [
                'id'       => 1,
                'category' => 'entrenamiento',
                'emoji'    => '💪',
                'title'    => 'La regla de las 48h',
                'summary'  => 'El musculo necesita 48-72h de recuperacion entre sesiones del mismo grupo.',
                'detail'   => 'Estudios de Kraemer et al. demuestran que el sintesis proteica muscular (MPS) se eleva durante 24-48h post entrenamiento. Entrenar el mismo musculo antes de que baje aumenta el dano sin anadir adaptacion.',
                'source'   => 'Journal of Strength and Conditioning Research, 2017',
            ],
            [
                'id'       => 2,
                'category' => 'nutricion',
                'emoji'    => '🥩',
                'title'    => 'Proteina: 1.6g/kg es el punto de saturacion',
                'summary'  => 'Consumir mas de 1.6g/kg de peso no anade mas masa muscular para la mayoria.',
                'detail'   => 'Meta-analisis de Morton et al. (2018) con 1,800 sujetos: el pico de ganancia muscular ocurre a ~1.62g/kg/dia. Consumir mas es simplemente oxidado como energia.',
                'source'   => 'British Journal of Sports Medicine, 2018',
            ],
            [
                'id'       => 3,
                'category' => 'descanso',
                'emoji'    => '😴',
                'title'    => 'Dormir mal reduce la fuerza un 10%',
                'summary'  => 'Una sola noche de sueno reducido (< 6h) disminuye el rendimiento muscular al dia siguiente.',
                'detail'   => 'Estudio de Reilly & Piercy: la privacion parcial de sueno reduce la fuerza isometrica y la potencia. El GH secretado en fase REM es crucial para recuperacion muscular.',
                'source'   => 'Ergonomics, 1994 / NSCA, 2021',
            ],
            [
                'id'       => 4,
                'category' => 'entrenamiento',
                'emoji'    => '🔄',
                'title'    => 'La sobrecarga progresiva es la clave',
                'summary'  => 'Sin aumentar el estimulo semana a semana, el musculo deja de crecer.',
                'detail'   => 'Principio de SAID (Specific Adaptation to Imposed Demands). Si siempre levantas el mismo peso, el cuerpo se adapta y detiene la hipertrofia. Aumenta carga, reps o volumen cada 2-3 semanas.',
                'source'   => 'Zatsiorsky & Kraemer, Science and Practice of Strength Training',
            ],
            [
                'id'       => 5,
                'category' => 'nutricion',
                'emoji'    => '⏰',
                'title'    => 'La ventana anabolica dura 3-4 horas',
                'summary'  => 'No necesitas comer inmediatamente post-entreno — tienes 3-4 horas de ventana real.',
                'detail'   => 'Aragon & Schoenfeld (2013) revisaron la "ventana anabolica" y concluyeron que si tuviste una comida previa con proteina, la ventana post-entreno es mucho mas amplia de lo que se cree.',
                'source'   => 'Journal of the International Society of Sports Nutrition, 2013',
            ],
            [
                'id'       => 6,
                'category' => 'suplementacion',
                'emoji'    => '💊',
                'title'    => 'Creatina: el suplemento mas estudiado',
                'summary'  => '5g/dia de creatina monohidrato aumenta la fuerza y el volumen muscular de forma consistente.',
                'detail'   => 'Mas de 500 estudios peer-reviewed. La creatina aumenta el PCr intramuscular, permite mas reps en rangos de alta intensidad, y a largo plazo se traduce en mas volumen y adaptacion.',
                'source'   => 'International Society of Sports Nutrition Position Stand, 2017',
            ],
        ]);

        if ($this->categoryFilter) {
            $hacks = $hacks->filter(fn ($h) => $h['category'] === $this->categoryFilter);
        }

        $categories = collect(['entrenamiento', 'nutricion', 'descanso', 'suplementacion']);

        return view('livewire.client.evidence-hacks', compact('hacks', 'categories'));
    }
}
