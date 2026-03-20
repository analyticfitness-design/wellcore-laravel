<div
    x-data="{
        customTemplates: JSON.parse(localStorage.getItem('wc_broadcast_templates') || '[]'),
        newTemplateName: '',
        showSaveTemplate: false,

        saveTemplate(name, message) {
            if (!name.trim() || !message.trim()) return;
            this.customTemplates.push({
                id: Date.now(),
                name: name.trim(),
                message: message.trim(),
                category: 'custom',
                created: new Date().toLocaleDateString('es-CO')
            });
            localStorage.setItem('wc_broadcast_templates', JSON.stringify(this.customTemplates));
            this.newTemplateName = '';
            this.showSaveTemplate = false;
        },

        deleteTemplate(id) {
            this.customTemplates = this.customTemplates.filter(t => t.id !== id);
            localStorage.setItem('wc_broadcast_templates', JSON.stringify(this.customTemplates));
        },

        builtInTemplates: [
            {
                id: 'b1',
                category: 'bienvenida',
                name: 'Bienvenida nuevo cliente',
                message: 'Bienvenido/a a WellCore! Soy tu coach y estare acompanandote en tu proceso. Cualquier duda no dudes en escribirme. Vamos con todo!'
            },
            {
                id: 'b2',
                category: 'bienvenida',
                name: 'Inicio de plan',
                message: 'Tu plan ya esta listo! Revisa tu dashboard para ver tu rutina de entrenamiento y plan nutricional. Recuerda que la consistencia es clave. Aqui estoy para apoyarte.'
            },
            {
                id: 'b3',
                category: 'motivacion',
                name: 'Motivacion semanal',
                message: 'Nueva semana, nuevas oportunidades! Recuerda que cada repeticion cuenta y cada decision saludable te acerca a tu objetivo. Tu puedes!'
            },
            {
                id: 'b4',
                category: 'motivacion',
                name: 'Celebracion de logro',
                message: 'Quiero felicitarte por tu compromiso y constancia! Los resultados se construyen dia a dia y tu lo estas demostrando. Sigue asi!'
            },
            {
                id: 'b5',
                category: 'recordatorio',
                name: 'Recordatorio check-in',
                message: 'Recuerda enviar tu check-in semanal! Es importante para poder ajustar tu plan y asegurar que sigamos en el camino correcto. Te espero!'
            },
            {
                id: 'b6',
                category: 'recordatorio',
                name: 'Recordatorio fotos progreso',
                message: 'No olvides subir tus fotos de progreso esta semana. Son una herramienta fundamental para ver tu evolucion. Te sorprenderas de los cambios!'
            },
            {
                id: 'b7',
                category: 'seguimiento',
                name: 'Seguimiento nutricional',
                message: 'Como vas con la alimentacion esta semana? Recuerda registrar tus comidas para que pueda ayudarte mejor. La nutricion es el 70% del resultado!'
            },
            {
                id: 'b8',
                category: 'seguimiento',
                name: 'Revision de progreso',
                message: 'Es momento de revisar como vamos! Agenda tu check-in para esta semana y cuéntame como te has sentido con el entrenamiento y la alimentacion.'
            },
            {
                id: 'b9',
                category: 'general',
                name: 'Horario festivo',
                message: 'Informacion importante: durante estos dias festivos, mantengamos el compromiso con nuestro plan. Ajusta horarios si es necesario pero no pierdas el ritmo!'
            },
            {
                id: 'b10',
                category: 'general',
                name: 'Actualizacion de plan',
                message: 'He actualizado tu plan con ajustes basados en tu progreso reciente. Revisa tu dashboard para ver los cambios. Cualquier duda, aqui estoy!'
            }
        ],

        categoryLabels: {
            'bienvenida': 'Bienvenida',
            'motivacion': 'Motivacion',
            'recordatorio': 'Recordatorio',
            'seguimiento': 'Seguimiento',
            'general': 'General',
            'custom': 'Personalizados'
        },

        categoryColors: {
            'bienvenida': 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'motivacion': 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            'recordatorio': 'bg-blue-500/10 text-blue-400 border-blue-500/20',
            'seguimiento': 'bg-purple-500/10 text-purple-400 border-purple-500/20',
            'general': 'bg-wc-text-tertiary/10 text-wc-text-tertiary border-wc-border',
            'custom': 'bg-wc-accent/10 text-wc-accent border-wc-accent/20'
        },

        activeCategory: 'all',

        get filteredBuiltIn() {
            if (this.activeCategory === 'all') return this.builtInTemplates;
            if (this.activeCategory === 'custom') return [];
            return this.builtInTemplates.filter(t => t.category === this.activeCategory);
        },

        get filteredCustom() {
            if (this.activeCategory !== 'all' && this.activeCategory !== 'custom') return [];
            return this.customTemplates;
        }
    }"
    class="space-y-6"
>

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Centro de Broadcast</h1>
            <p class="mt-1 text-sm text-wc-text-tertiary">Envia mensajes masivos a tus clientes</p>
        </div>

        @if($sent)
            <div class="flex items-center gap-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20 px-4 py-2.5">
                <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="text-sm font-medium text-emerald-400">Broadcast enviado a {{ $sentCount }} cliente{{ $sentCount !== 1 ? 's' : '' }}</span>
            </div>
        @endif
    </div>

    {{-- Tab bar --}}
    <div class="flex gap-1 rounded-lg border border-wc-border bg-wc-bg-secondary p-1">
        <button
            wire:click="switchTab('compose')"
            class="flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors
                   {{ $activeTab === 'compose' ? 'bg-wc-accent text-white' : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary' }}"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
            </svg>
            Componer
        </button>
        <button
            wire:click="switchTab('history')"
            class="flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors
                   {{ $activeTab === 'history' ? 'bg-wc-accent text-white' : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary' }}"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            Historial
        </button>
        <button
            wire:click="switchTab('templates')"
            class="flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors
                   {{ $activeTab === 'templates' ? 'bg-wc-accent text-white' : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary' }}"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
            Templates
        </button>
    </div>

    {{-- ============================================== --}}
    {{-- COMPOSE TAB --}}
    {{-- ============================================== --}}
    @if($activeTab === 'compose')
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

            {{-- Left: Recipients + Message --}}
            <div class="space-y-5 lg:col-span-7">

                {{-- Recipient Selection --}}
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                    <h3 class="text-sm font-semibold text-wc-text mb-4 flex items-center gap-2">
                        <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        Destinatarios
                    </h3>

                    {{-- Mode selector --}}
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @foreach([
                            'all' => ['label' => 'Todos', 'icon' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z'],
                            'plan' => ['label' => 'Por Plan', 'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z'],
                            'status' => ['label' => 'Por Estado', 'icon' => 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z M6 6h.008v.008H6V6Z'],
                            'individual' => ['label' => 'Individual', 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z']
                        ] as $mode => $data)
                            <button
                                wire:click="$set('recipientMode', '{{ $mode }}')"
                                class="flex flex-col items-center gap-1.5 rounded-lg border p-3 text-center transition-all
                                       {{ $recipientMode === $mode
                                           ? 'border-wc-accent bg-wc-accent/10 text-wc-accent'
                                           : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:border-wc-accent/30 hover:text-wc-text' }}"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $data['icon'] }}" />
                                </svg>
                                <span class="text-xs font-medium">{{ $data['label'] }}</span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Plan filter --}}
                    @if($recipientMode === 'plan')
                        <div class="mt-4 space-y-2">
                            <p class="text-xs font-medium text-wc-text-tertiary">Selecciona planes:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($planTypes as $plan)
                                    <button
                                        wire:click="togglePlan('{{ $plan['value'] }}')"
                                        class="rounded-full border px-3 py-1.5 text-xs font-medium transition-all
                                               {{ in_array($plan['value'], $selectedPlans)
                                                   ? 'border-wc-accent bg-wc-accent text-white'
                                                   : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:border-wc-accent/40' }}"
                                    >
                                        {{ $plan['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Status filter --}}
                    @if($recipientMode === 'status')
                        <div class="mt-4 space-y-2">
                            <p class="text-xs font-medium text-wc-text-tertiary">Selecciona estado:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($statusTypes as $status)
                                    <button
                                        wire:click="$set('selectedStatus', '{{ $status['value'] }}')"
                                        class="rounded-full border px-3 py-1.5 text-xs font-medium transition-all
                                               {{ $selectedStatus === $status['value']
                                                   ? 'border-wc-accent bg-wc-accent text-white'
                                                   : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:border-wc-accent/40' }}"
                                    >
                                        {{ $status['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Individual client selector --}}
                    @if($recipientMode === 'individual')
                        <div class="mt-4 space-y-3">
                            {{-- Search --}}
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="clientSearch"
                                    placeholder="Buscar cliente..."
                                    class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-9 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                                >
                            </div>

                            {{-- Select/Deselect all --}}
                            <div class="flex items-center gap-2">
                                <button wire:click="selectAllClients" class="text-xs font-medium text-wc-accent hover:underline">Seleccionar todos</button>
                                <span class="text-wc-text-tertiary">|</span>
                                <button wire:click="deselectAllClients" class="text-xs font-medium text-wc-text-secondary hover:text-wc-text hover:underline">Deseleccionar</button>
                                @if(count($selectedClientIds) > 0)
                                    <span class="ml-auto rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold text-wc-accent">
                                        {{ count($selectedClientIds) }} seleccionado{{ count($selectedClientIds) !== 1 ? 's' : '' }}
                                    </span>
                                @endif
                            </div>

                            {{-- Client list --}}
                            <div class="max-h-48 space-y-1 overflow-y-auto rounded-lg border border-wc-border bg-wc-bg-secondary p-2">
                                @forelse($allClients as $client)
                                    <button
                                        wire:click="toggleClient({{ $client['id'] }})"
                                        class="flex w-full items-center gap-2.5 rounded-md px-2.5 py-2 text-left transition-colors
                                               {{ in_array($client['id'], $selectedClientIds)
                                                   ? 'bg-wc-accent/10 text-wc-text'
                                                   : 'hover:bg-wc-bg-tertiary text-wc-text-secondary' }}"
                                    >
                                        {{-- Checkbox indicator --}}
                                        <div class="flex h-4 w-4 shrink-0 items-center justify-center rounded border transition-colors
                                                    {{ in_array($client['id'], $selectedClientIds)
                                                        ? 'border-wc-accent bg-wc-accent'
                                                        : 'border-wc-border bg-wc-bg-secondary' }}">
                                            @if(in_array($client['id'], $selectedClientIds))
                                                <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                </svg>
                                            @endif
                                        </div>

                                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                                            <span class="text-xs font-semibold text-wc-accent">{{ $client['initial'] }}</span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-medium truncate">{{ $client['name'] }}</p>
                                            <p class="text-[10px] text-wc-text-tertiary">{{ $client['plan'] }}</p>
                                        </div>
                                    </button>
                                @empty
                                    <p class="py-4 text-center text-xs text-wc-text-tertiary">Sin clientes encontrados</p>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    {{-- Recipient count badge --}}
                    <div class="mt-4 flex items-center gap-2 rounded-lg bg-wc-bg-secondary px-3 py-2">
                        <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                        <span class="text-sm font-medium text-wc-text">
                            {{ $recipientCount }} destinatario{{ $recipientCount !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>

                {{-- Message Compose --}}
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                    <h3 class="text-sm font-semibold text-wc-text mb-4 flex items-center gap-2">
                        <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        Mensaje
                    </h3>

                    <textarea
                        wire:model="message"
                        rows="5"
                        placeholder="Escribe tu mensaje aqui..."
                        class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-none"
                    ></textarea>

                    {{-- Character count --}}
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-[10px] text-wc-text-tertiary">
                            {{ strlen($message) }} caracteres
                        </span>

                        {{-- Save as template button --}}
                        @if(strlen(trim($message)) > 0)
                            <button
                                x-on:click="showSaveTemplate = !showSaveTemplate"
                                class="text-[10px] font-medium text-wc-accent hover:underline"
                            >
                                Guardar como template
                            </button>
                        @endif
                    </div>

                    {{-- Save template inline form --}}
                    <div x-show="showSaveTemplate" x-transition x-cloak class="mt-3 flex items-center gap-2 rounded-lg border border-wc-accent/20 bg-wc-accent/5 p-3">
                        <input
                            type="text"
                            x-model="newTemplateName"
                            placeholder="Nombre del template..."
                            class="flex-1 rounded-md border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"
                        >
                        <button
                            x-on:click="saveTemplate(newTemplateName, $wire.message)"
                            class="rounded-md bg-wc-accent px-3 py-1.5 text-xs font-medium text-white hover:bg-wc-accent-hover transition-colors"
                        >
                            Guardar
                        </button>
                        <button
                            x-on:click="showSaveTemplate = false"
                            class="text-xs text-wc-text-tertiary hover:text-wc-text"
                        >
                            Cancelar
                        </button>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <button
                            wire:click="togglePreview"
                            class="flex items-center gap-2 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            {{ $showPreview ? 'Ocultar preview' : 'Ver preview' }}
                        </button>

                        <div class="flex items-center gap-3">
                            <button
                                wire:click="resetCompose"
                                class="rounded-lg border border-wc-border px-4 py-2.5 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-secondary hover:text-wc-text transition-colors"
                            >
                                Limpiar
                            </button>
                            <button
                                wire:click="sendBroadcast"
                                wire:confirm="Enviar mensaje a {{ $recipientCount }} cliente{{ $recipientCount !== 1 ? 's' : '' }}?"
                                @if($recipientCount === 0 || strlen(trim($message)) === 0) disabled @endif
                                class="flex items-center gap-2 rounded-lg bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                </svg>
                                Enviar Broadcast
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Preview --}}
            <div class="lg:col-span-5">
                @if($showPreview && strlen(trim($message)) > 0)
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                        <h3 class="text-sm font-semibold text-wc-text mb-4 flex items-center gap-2">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            Vista Previa
                        </h3>

                        {{-- Simulated chat bubble --}}
                        <div class="rounded-xl bg-wc-bg-secondary p-4">
                            <div class="flex justify-end">
                                <div class="max-w-[85%] rounded-xl rounded-br-sm bg-wc-accent px-4 py-3 text-white">
                                    <p class="text-sm leading-relaxed whitespace-pre-line">{{ $message }}</p>
                                    <p class="mt-1.5 text-[10px] text-white/60">Ahora</p>
                                </div>
                            </div>
                        </div>

                        {{-- Recipient summary --}}
                        <div class="mt-4 space-y-2 rounded-lg bg-wc-bg-secondary p-3">
                            <p class="text-xs font-medium text-wc-text-secondary">Resumen del envio:</p>
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-wc-accent/10 text-[10px] font-bold text-wc-accent">
                                    {{ $recipientCount }}
                                </span>
                                <span class="text-xs text-wc-text-tertiary">
                                    @if($recipientMode === 'all')
                                        Todos los clientes activos
                                    @elseif($recipientMode === 'plan')
                                        Clientes por plan: {{ implode(', ', $selectedPlans) ?: 'ninguno' }}
                                    @elseif($recipientMode === 'status')
                                        Clientes con estado: {{ $selectedStatus }}
                                    @else
                                        Clientes seleccionados individualmente
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Quick Templates Panel --}}
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                        <h3 class="text-sm font-semibold text-wc-text mb-4 flex items-center gap-2">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                            </svg>
                            Templates Rapidos
                        </h3>
                        <div class="space-y-2">
                            <template x-for="template in builtInTemplates.slice(0, 5)" :key="template.id">
                                <button
                                    x-on:click="$wire.useTemplate(template.message)"
                                    class="flex w-full items-start gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-left transition-all hover:border-wc-accent/30 hover:bg-wc-accent/5"
                                >
                                    <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md" :class="categoryColors[template.category]">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-medium text-wc-text truncate" x-text="template.name"></p>
                                        <p class="mt-0.5 text-[10px] text-wc-text-tertiary line-clamp-2" x-text="template.message"></p>
                                    </div>
                                </button>
                            </template>
                        </div>
                        <button
                            wire:click="switchTab('templates')"
                            class="mt-3 w-full text-center text-xs font-medium text-wc-accent hover:underline"
                        >
                            Ver todos los templates
                        </button>
                    </div>
                @endif
            </div>
        </div>

    {{-- ============================================== --}}
    {{-- HISTORY TAB --}}
    {{-- ============================================== --}}
    @elseif($activeTab === 'history')
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary">
            <div class="border-b border-wc-border px-5 py-4">
                <h3 class="text-sm font-semibold text-wc-text flex items-center gap-2">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Historial de Broadcasts
                </h3>
            </div>

            @if(count($history) > 0)
                <div class="divide-y divide-wc-border">
                    @foreach($history as $entry)
                        <div class="flex items-start gap-4 px-5 py-4 hover:bg-wc-bg-secondary/30 transition-colors">
                            {{-- Timeline dot --}}
                            <div class="mt-1.5 flex flex-col items-center">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full
                                            {{ $entry['recipient_count'] > 1 ? 'bg-wc-accent/10' : 'bg-wc-bg-secondary' }}">
                                    @if($entry['recipient_count'] > 1)
                                        <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                                        </svg>
                                    @else
                                        <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-sm text-wc-text leading-relaxed">{{ $entry['preview'] }}</p>
                                    <div class="shrink-0 text-right">
                                        <p class="text-[10px] text-wc-text-tertiary">{{ $entry['sent_at'] }}</p>
                                        <p class="text-[10px] text-wc-text-tertiary">{{ $entry['sent_ago'] }}</p>
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center gap-3">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-medium text-wc-text-secondary">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                        {{ $entry['recipient_count'] }} destinatario{{ $entry['recipient_count'] !== 1 ? 's' : '' }}
                                    </span>
                                    <button
                                        wire:click="useTemplate('{{ addslashes($entry['message']) }}')"
                                        class="text-[10px] font-medium text-wc-accent hover:underline"
                                    >
                                        Reusar mensaje
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-wc-bg-secondary">
                        <svg class="h-7 w-7 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <p class="mt-4 text-sm font-medium text-wc-text">Sin historial</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">Los broadcasts que envies apareceran aqui</p>
                </div>
            @endif
        </div>

    {{-- ============================================== --}}
    {{-- TEMPLATES TAB --}}
    {{-- ============================================== --}}
    @elseif($activeTab === 'templates')
        <div class="space-y-5">

            {{-- Category filter --}}
            <div class="flex flex-wrap gap-2">
                <button
                    x-on:click="activeCategory = 'all'"
                    :class="activeCategory === 'all' ? 'border-wc-accent bg-wc-accent text-white' : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:border-wc-accent/40'"
                    class="rounded-full border px-3 py-1.5 text-xs font-medium transition-all"
                >
                    Todos
                </button>
                <template x-for="(label, key) in categoryLabels" :key="key">
                    <button
                        x-on:click="activeCategory = key"
                        :class="activeCategory === key ? 'border-wc-accent bg-wc-accent text-white' : 'border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:border-wc-accent/40'"
                        class="rounded-full border px-3 py-1.5 text-xs font-medium transition-all"
                        x-text="label"
                    ></button>
                </template>
            </div>

            {{-- Built-in templates grid --}}
            <div x-show="filteredBuiltIn.length > 0" x-transition>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary mb-3">Templates Predefinidos</h3>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="template in filteredBuiltIn" :key="template.id">
                        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 flex flex-col transition-all hover:border-wc-accent/30">
                            {{-- Category badge --}}
                            <div class="flex items-center justify-between mb-3">
                                <span
                                    class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold"
                                    :class="categoryColors[template.category]"
                                    x-text="categoryLabels[template.category]"
                                ></span>
                            </div>

                            {{-- Title --}}
                            <p class="text-sm font-medium text-wc-text mb-2" x-text="template.name"></p>

                            {{-- Preview --}}
                            <p class="flex-1 text-xs text-wc-text-tertiary leading-relaxed line-clamp-3 mb-4" x-text="template.message"></p>

                            {{-- Use button --}}
                            <button
                                x-on:click="$wire.useTemplate(template.message)"
                                class="mt-auto flex items-center justify-center gap-1.5 rounded-lg border border-wc-accent/30 bg-wc-accent/5 px-3 py-2 text-xs font-medium text-wc-accent hover:bg-wc-accent/10 transition-colors"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                </svg>
                                Usar Template
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Custom templates --}}
            <div x-show="activeCategory === 'all' || activeCategory === 'custom'" x-transition>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Mis Templates</h3>
                    <span class="text-[10px] text-wc-text-tertiary" x-text="customTemplates.length + ' guardado' + (customTemplates.length !== 1 ? 's' : '')"></span>
                </div>

                <template x-if="customTemplates.length > 0">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <template x-for="template in filteredCustom" :key="template.id">
                            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 flex flex-col transition-all hover:border-wc-accent/30">
                                {{-- Category badge + delete --}}
                                <div class="flex items-center justify-between mb-3">
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold bg-wc-accent/10 text-wc-accent border-wc-accent/20">
                                        Personalizado
                                    </span>
                                    <button
                                        x-on:click="if(confirm('Eliminar este template?')) deleteTemplate(template.id)"
                                        class="flex h-6 w-6 items-center justify-center rounded-md text-wc-text-tertiary hover:bg-red-500/10 hover:text-red-400 transition-colors"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>

                                {{-- Title --}}
                                <p class="text-sm font-medium text-wc-text mb-1" x-text="template.name"></p>
                                <p class="text-[10px] text-wc-text-tertiary mb-2" x-text="'Creado: ' + template.created"></p>

                                {{-- Preview --}}
                                <p class="flex-1 text-xs text-wc-text-tertiary leading-relaxed line-clamp-3 mb-4" x-text="template.message"></p>

                                {{-- Use button --}}
                                <button
                                    x-on:click="$wire.useTemplate(template.message)"
                                    class="mt-auto flex items-center justify-center gap-1.5 rounded-lg border border-wc-accent/30 bg-wc-accent/5 px-3 py-2 text-xs font-medium text-wc-accent hover:bg-wc-accent/10 transition-colors"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                    </svg>
                                    Usar Template
                                </button>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="customTemplates.length === 0">
                    <div class="rounded-card border border-dashed border-wc-border bg-wc-bg-tertiary p-8 text-center">
                        <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <p class="mt-3 text-sm font-medium text-wc-text">Sin templates personalizados</p>
                        <p class="mt-1 text-xs text-wc-text-tertiary">Al componer un mensaje, usa "Guardar como template" para crear uno</p>
                    </div>
                </template>
            </div>
        </div>
    @endif

</div>
