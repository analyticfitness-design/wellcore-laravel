<?php

namespace App\Livewire\Coach;

use App\Models\AcademyContent;
use App\Models\CoachCommunityPost;
use App\Models\CoachVideoTip;
use App\Models\PlanTemplate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.coach', ['title' => 'Recursos'])]
class Resources extends Component
{
    // ---- Navigation ----
    public string $activeModule = 'guides';

    // ---- Academy CRUD ----
    public bool $showAcademyModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    // Academy form fields
    public string $acTitle = '';
    public string $acCategory = 'nutricion';
    public string $acContentType = 'article';
    public string $acAudience = 'client';
    public string $acDescription = '';
    public string $acBodyHtml = '';
    public string $acContentUrl = '';
    public string $acThumbnailUrl = '';
    public int $acSortOrder = 0;
    public bool $acActive = true;

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public ?int $deletingId = null;
    public string $deletingTitle = '';

    // Flash
    public string $flashMessage = '';
    public string $flashType = 'success';

    // ---- Guides content (static) ----
    public array $guides = [];

    // ---- Tools content (static) ----
    public array $tools = [];

    public function mount(): void
    {
        $this->guides = $this->getGuides();
        $this->tools = $this->getTools();
    }

    public function switchModule(string $module): void
    {
        $this->activeModule = $module;
        $this->flashMessage = '';
        $this->resetAcademyForm();
    }

    // ============================================================
    //  Academy CRUD
    // ============================================================

    public function openCreateModal(): void
    {
        $this->resetAcademyForm();
        $this->isEditing = false;
        $this->showAcademyModal = true;

        // Default sort_order = max + 1
        $max = AcademyContent::max('sort_order') ?? 0;
        $this->acSortOrder = $max + 1;
    }

    public function openEditModal(int $id): void
    {
        $item = AcademyContent::find($id);
        if (! $item) {
            return;
        }

        $this->isEditing = true;
        $this->editingId = $id;
        $this->acTitle = $item->title;
        $this->acCategory = $item->category;
        $this->acContentType = $item->content_type ?? 'article';
        $this->acAudience = $item->audience ?? 'client';
        $this->acDescription = $item->description ?? '';
        $this->acBodyHtml = $item->body_html ?? '';
        $this->acContentUrl = $item->content_url ?? '';
        $this->acThumbnailUrl = $item->thumbnail_url ?? '';
        $this->acSortOrder = $item->sort_order ?? 0;
        $this->acActive = (bool) $item->active;
        $this->showAcademyModal = true;
    }

    public function saveAcademy(): void
    {
        $this->validate([
            'acTitle' => 'required|string|max:255',
            'acCategory' => 'required|string|max:80',
            'acContentType' => 'required|in:video,pdf,article,guide',
            'acAudience' => 'required|in:client,coach,both',
            'acDescription' => 'nullable|string',
            'acBodyHtml' => 'nullable|string',
            'acContentUrl' => 'nullable|url|max:500',
            'acThumbnailUrl' => 'nullable|url|max:500',
            'acSortOrder' => 'integer|min:0',
        ]);

        $data = [
            'title' => $this->acTitle,
            'category' => $this->acCategory,
            'content_type' => $this->acContentType,
            'audience' => $this->acAudience,
            'description' => $this->acDescription ?: null,
            'body_html' => $this->acBodyHtml ?: null,
            'content_url' => $this->acContentUrl ?: null,
            'thumbnail_url' => $this->acThumbnailUrl ?: null,
            'sort_order' => $this->acSortOrder,
            'active' => $this->acActive,
        ];

        if ($this->isEditing && $this->editingId) {
            AcademyContent::where('id', $this->editingId)->update($data);
            $this->flashMessage = 'Contenido actualizado exitosamente.';
        } else {
            AcademyContent::create($data);
            $this->flashMessage = 'Contenido creado exitosamente.';
        }

        $this->flashType = 'success';
        $this->showAcademyModal = false;
        $this->resetAcademyForm();
    }

    public function confirmDelete(int $id): void
    {
        $item = AcademyContent::find($id);
        if (! $item) {
            return;
        }

        $this->deletingId = $id;
        $this->deletingTitle = $item->title;
        $this->showDeleteConfirm = true;
    }

    public function deleteAcademy(): void
    {
        if ($this->deletingId) {
            AcademyContent::where('id', $this->deletingId)->delete();
            $this->flashMessage = 'Contenido eliminado.';
            $this->flashType = 'success';
        }

        $this->showDeleteConfirm = false;
        $this->deletingId = null;
        $this->deletingTitle = '';
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deletingId = null;
        $this->deletingTitle = '';
    }

    public function toggleActive(int $id): void
    {
        $item = AcademyContent::find($id);
        if (! $item) {
            return;
        }

        $item->active = ! $item->active;
        $item->save();

        $this->flashMessage = $item->active ? 'Contenido activado.' : 'Contenido desactivado.';
        $this->flashType = 'success';
    }

    public function moveUp(int $id): void
    {
        $item = AcademyContent::find($id);
        if (! $item || $item->sort_order <= 0) {
            return;
        }

        // Find item above
        $above = AcademyContent::where('sort_order', '<', $item->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        if ($above) {
            $tmpOrder = $item->sort_order;
            $item->sort_order = $above->sort_order;
            $above->sort_order = $tmpOrder;
            $item->save();
            $above->save();
        } else {
            $item->sort_order = max(0, $item->sort_order - 1);
            $item->save();
        }
    }

    public function moveDown(int $id): void
    {
        $item = AcademyContent::find($id);
        if (! $item) {
            return;
        }

        $below = AcademyContent::where('sort_order', '>', $item->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($below) {
            $tmpOrder = $item->sort_order;
            $item->sort_order = $below->sort_order;
            $below->sort_order = $tmpOrder;
            $item->save();
            $below->save();
        } else {
            $item->sort_order = $item->sort_order + 1;
            $item->save();
        }
    }

    public function closeModal(): void
    {
        $this->showAcademyModal = false;
        $this->resetAcademyForm();
    }

    protected function resetAcademyForm(): void
    {
        $this->isEditing = false;
        $this->editingId = null;
        $this->acTitle = '';
        $this->acCategory = 'nutricion';
        $this->acContentType = 'article';
        $this->acAudience = 'client';
        $this->acDescription = '';
        $this->acBodyHtml = '';
        $this->acContentUrl = '';
        $this->acThumbnailUrl = '';
        $this->acSortOrder = 0;
        $this->acActive = true;
    }

    // ============================================================
    //  Static content helpers
    // ============================================================

    protected function getGuides(): array
    {
        return [
            [
                'title' => 'Guia de Check-ins',
                'icon' => 'clipboard-check',
                'sections' => [
                    [
                        'heading' => 'Frecuencia recomendada',
                        'content' => 'Solicita un check-in semanal a cada cliente. El mejor dia es el lunes para revisar la semana anterior y ajustar la semana entrante.',
                    ],
                    [
                        'heading' => 'Que revisar',
                        'content' => 'Bienestar general (1-10), dias entrenados vs programados, adherencia nutricional, calidad de sueno, nivel de estres, y fotos de progreso mensuales.',
                    ],
                    [
                        'heading' => 'Como dar feedback',
                        'content' => 'Siempre inicia con algo positivo. Se especifico en los ajustes. Usa datos del check-in para respaldar tus recomendaciones. Termina con un objetivo claro para la proxima semana.',
                    ],
                ],
            ],
            [
                'title' => 'Protocolo de Ajustes',
                'icon' => 'adjustments',
                'sections' => [
                    [
                        'heading' => 'Cuando ajustar el plan',
                        'content' => 'Ajusta si el cliente no progresa en 2-3 semanas consecutivas, reporta dolor o molestia persistente, o su bienestar cae por debajo de 5/10 consistentemente.',
                    ],
                    [
                        'heading' => 'Progresion de carga',
                        'content' => 'Incrementa volumen antes que intensidad. Aumenta 5-10% semanal en principiantes, 2-5% en intermedios. Si falla repeticiones, mantener carga una semana mas.',
                    ],
                    [
                        'heading' => 'Ajustes nutricionales',
                        'content' => 'Revisa calorias cada 2-4 semanas segun progreso. Deficit: no mas de 500kcal bajo mantenimiento. Superavit: 200-300kcal sobre mantenimiento para masa muscular limpia.',
                    ],
                ],
            ],
            [
                'title' => 'Comunicacion con Clientes',
                'icon' => 'chat',
                'sections' => [
                    [
                        'heading' => 'Tiempo de respuesta',
                        'content' => 'Responde mensajes dentro de 24 horas habiles. Check-ins dentro de 48 horas. Emergencias (lesion, dolor agudo) lo antes posible.',
                    ],
                    [
                        'heading' => 'Tono y estilo',
                        'content' => 'Mantener tono profesional pero cercano. Usar el nombre del cliente. Evitar tecnicismos excesivos. Adaptar el lenguaje al nivel del cliente.',
                    ],
                    [
                        'heading' => 'Limites saludables',
                        'content' => 'Establece horarios de atencion claros. No diagnostiques condiciones medicas. Refiere a profesionales de salud cuando sea necesario.',
                    ],
                ],
            ],
            [
                'title' => 'Manejo de Lesiones',
                'icon' => 'shield',
                'sections' => [
                    [
                        'heading' => 'Evaluacion inicial',
                        'content' => 'Pregunta: donde duele, desde cuando, que lo provoca, intensidad 1-10. Si es agudo o severo, refiere a medico/fisioterapeuta antes de continuar.',
                    ],
                    [
                        'heading' => 'Modificaciones',
                        'content' => 'Sustituye ejercicios que agraven el dolor. Mantiene al cliente activo con movimientos seguros. Reduce volumen e intensidad gradualmente en la zona afectada.',
                    ],
                    [
                        'heading' => 'Regreso al entrenamiento',
                        'content' => 'Progresion gradual: 50% → 70% → 85% → 100% de la carga previa. Minimo 1 semana en cada nivel. Monitorear dolor en cada sesion.',
                    ],
                ],
            ],
            [
                'title' => 'Nutricion Basica',
                'icon' => 'nutrition',
                'sections' => [
                    [
                        'heading' => 'Bases del plan nutricional',
                        'content' => 'Calcula TDEE segun actividad. Proteina: 1.6-2.2g/kg. Grasas: 0.8-1.2g/kg. Carbohidratos: completar calorias restantes. Hidratacion: 35ml/kg/dia minimo.',
                    ],
                    [
                        'heading' => 'Timing nutricional',
                        'content' => 'Pre-entreno (1-2h antes): carbohidratos + proteina. Post-entreno (dentro de 2h): proteina + carbohidratos. Distribuir proteina en 3-5 comidas al dia.',
                    ],
                    [
                        'heading' => 'Suplementacion basica',
                        'content' => 'Creatina monohidrato: 3-5g/dia. Proteina whey: segun necesidad para alcanzar meta diaria. Vitamina D: si hay deficiencia comprobada. Omega-3: 1-2g EPA+DHA/dia.',
                    ],
                ],
            ],
        ];
    }

    protected function getTools(): array
    {
        return [
            [
                'title' => 'Calculadoras Fitness',
                'description' => 'TDEE, macros, 1RM, IMC y mas calculadoras para tus clientes.',
                'icon' => 'calculator',
                'route' => 'client.academia',
            ],
            [
                'title' => 'Timer de Entrenamiento',
                'description' => 'Cronometro, temporizador de descanso y timer Tabata/EMOM.',
                'icon' => 'clock',
                'route' => 'client.timer',
            ],
            [
                'title' => 'Mindfulness & Recuperacion',
                'description' => 'Ejercicios de respiracion guiada y meditacion para clientes.',
                'icon' => 'sparkles',
                'route' => 'client.mindfulness',
            ],
            [
                'title' => 'Base de Recetas',
                'description' => 'Recetas saludables categorizadas por macros y tipo de comida.',
                'icon' => 'book-open',
                'route' => 'client.recipes',
            ],
            [
                'title' => 'Records Personales',
                'description' => 'Seguimiento de PRs y records en ejercicios principales.',
                'icon' => 'trophy',
                'route' => 'client.records',
            ],
            [
                'title' => 'Biblioteca de Videos',
                'description' => 'Videos demostrativos de ejercicios y tecnica correcta.',
                'icon' => 'play',
                'route' => 'client.videos',
            ],
        ];
    }

    // ============================================================
    //  Render
    // ============================================================

    public function render()
    {
        $coachId = auth('wellcore')->id();

        // Academy content list
        $academyItems = AcademyContent::orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        // Video tips
        $videoTips = CoachVideoTip::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Community posts / articles
        $articles = CoachCommunityPost::orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Plan templates accessible to this coach
        $templates = PlanTemplate::where(function ($q) use ($coachId) {
            $q->where('coach_id', $coachId)
              ->orWhere('is_public', true);
        })->orderByDesc('created_at')->get();

        return view('livewire.coach.resources', [
            'academyItems' => $academyItems,
            'videoTips' => $videoTips,
            'articles' => $articles,
            'templates' => $templates,
        ]);
    }
}
