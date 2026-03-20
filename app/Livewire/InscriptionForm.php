<?php

namespace App\Livewire;

use App\Models\Inscription;
use Illuminate\Support\Str;
use Livewire\Component;

class InscriptionForm extends Component
{
    public int $step = 0;
    public bool $submitted = false;

    // Step 0: Plan
    public string $plan = '';

    // Step 1: Info basica
    public string $nombre = '';
    public string $apellido = '';
    public string $email = '';
    public string $whatsapp = '';
    public ?int $edad = null;
    public ?float $peso = null;
    public ?float $estatura = null;
    public string $genero = '';
    public string $objetivo = '';
    public string $ciudad = '';
    public string $pais = '';

    // Step 2: Experiencia
    public string $experiencia = '';
    public string $dias_disponibles = '';
    public string $equipamiento = '';
    public string $coaching_previo = '';
    public string $rutina_actual = '';

    // Step 3: Preferencias
    public string $tipo_entrenamiento = '';
    public string $duracion_sesion = '';
    public string $horario = '';
    public string $restricciones_ejercicio = '';

    // Step 4: Lesiones
    public string $lesion = '';
    public string $detalle_lesion = '';
    public string $condiciones_medicas = '';
    public string $medicamentos = '';

    // Step 5: Nutricion
    public string $dieta_actual = '';
    public string $alergias = '';
    public string $comidas_dia = '';
    public string $experiencia_macros = '';
    public string $alimentos_excluir = '';

    // Step 6: Horarios
    public string $horario_trabajo = '';
    public string $comer_fuera = '';
    public string $nivel_estres = '';
    public string $horas_sueno = '';

    // Step 7: Finales
    public string $como_conocio = '';
    public string $notas = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terminos = false;

    public function rules(): array
    {
        return match ($this->step) {
            0 => ['plan' => 'required|in:esencial,metodo,elite'],
            1 => [
                'nombre' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'whatsapp' => 'required|string|max:50',
                'edad' => 'required|integer|min:14|max:80',
                'peso' => 'required|numeric|min:30|max:250',
                'estatura' => 'required|numeric|min:100|max:230',
                'genero' => 'required|in:masculino,femenino,otro',
                'objetivo' => 'required|string',
            ],
            2 => [
                'experiencia' => 'required|string',
                'dias_disponibles' => 'required|string',
                'equipamiento' => 'required|string',
            ],
            3 => [],
            4 => ['lesion' => 'required|string'],
            5 => [],
            6 => [],
            7 => [
                'password' => 'required|string|min:8|confirmed',
                'terminos' => 'accepted',
            ],
            default => [],
        };
    }

    public function messages(): array
    {
        return [
            'plan.required' => 'Selecciona un plan para continuar.',
            'nombre.required' => 'Tu nombre es requerido.',
            'email.required' => 'Tu email es requerido.',
            'email.email' => 'Ingresa un email valido.',
            'whatsapp.required' => 'Tu WhatsApp es requerido.',
            'edad.required' => 'Tu edad es requerida.',
            'edad.min' => 'Edad minima: 14 anos.',
            'peso.required' => 'Tu peso actual es requerido.',
            'estatura.required' => 'Tu estatura es requerida.',
            'genero.required' => 'Selecciona tu genero.',
            'objetivo.required' => 'Selecciona tu objetivo principal.',
            'experiencia.required' => 'Selecciona tu experiencia.',
            'dias_disponibles.required' => 'Selecciona los dias disponibles.',
            'equipamiento.required' => 'Selecciona tu equipamiento.',
            'lesion.required' => 'Indica si tienes alguna lesion.',
            'password.required' => 'Crea una contrasena para tu cuenta.',
            'password.min' => 'Minimo 8 caracteres.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
            'terminos.accepted' => 'Debes aceptar los terminos.',
        ];
    }

    public function selectPlan(string $plan): void
    {
        $this->plan = $plan;
    }

    public function nextStep(): void
    {
        $this->validate();
        if ($this->step < 7) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 0) {
            $this->step--;
        }
    }

    public function submit(): void
    {
        $this->validate();

        $extra = json_encode([
            'peso' => $this->peso,
            'estatura' => $this->estatura,
            'genero' => $this->genero,
            'equipamiento' => $this->equipamiento,
            'coaching_previo' => $this->coaching_previo,
            'rutina_actual' => $this->rutina_actual,
            'tipo_entrenamiento' => $this->tipo_entrenamiento,
            'duracion_sesion' => $this->duracion_sesion,
            'restricciones_ejercicio' => $this->restricciones_ejercicio,
            'condiciones_medicas' => $this->condiciones_medicas,
            'medicamentos' => $this->medicamentos,
            'dieta_actual' => $this->dieta_actual,
            'alergias' => $this->alergias,
            'comidas_dia' => $this->comidas_dia,
            'experiencia_macros' => $this->experiencia_macros,
            'alimentos_excluir' => $this->alimentos_excluir,
            'horario_trabajo' => $this->horario_trabajo,
            'comer_fuera' => $this->comer_fuera,
            'nivel_estres' => $this->nivel_estres,
            'horas_sueno' => $this->horas_sueno,
            'notas' => $this->notas,
            'password_hash' => bcrypt($this->password),
        ], JSON_UNESCAPED_UNICODE);

        Inscription::create([
            'id' => Str::ulid()->toBase32(),
            'status' => 'pending_contact',
            'plan' => $this->plan,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'whatsapp' => $this->whatsapp,
            'ciudad' => $this->ciudad,
            'pais' => $this->pais,
            'edad' => $this->edad,
            'objetivo' => $this->objetivo . '|||' . $extra,
            'experiencia' => $this->experiencia,
            'lesion' => $this->lesion,
            'detalle_lesion' => $this->detalle_lesion,
            'dias_disponibles' => $this->dias_disponibles,
            'horario' => $this->horario,
            'como_conocio' => $this->como_conocio,
            'ip_hash' => hash('sha256', request()->ip()),
        ]);

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.inscription-form')
            ->layout('components.layouts.public', [
                'title' => 'Inscripcion - WellCore Fitness',
                'description' => 'Formulario de inscripcion WellCore Fitness. 8 pasos para empezar tu transformacion personalizada.',
            ]);
    }
}
