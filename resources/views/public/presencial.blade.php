<x-layouts.public>
    <x-slot:title>Entrenamiento Presencial Bogota - WellCore Fitness</x-slot:title>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-wc-bg-tertiary">
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-24 text-center sm:px-6 sm:py-32 lg:px-8">
            <span class="inline-flex rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-semibold text-wc-accent">Bogota, Colombia</span>
            <h1 class="mt-4 font-display text-5xl tracking-wide text-wc-text sm:text-6xl lg:text-7xl">
                ENTRENAMIENTO<br>PRESENCIAL
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-wc-text-secondary">
                Coaching 1:1 en persona. El mismo metodo basado en ciencia de WellCore, con la ventaja del acompañamiento presencial.
            </p>
            <div class="mt-8">
                <a href="{{ route('presencial.form') }}" class="rounded-full bg-wc-accent px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover">
                    Inscribirme
                </a>
            </div>
        </div>
    </section>

    {{-- Info --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <div>
                    <h2 class="font-display text-3xl tracking-wide text-wc-text">COMO FUNCIONA</h2>
                    <div class="mt-6 space-y-4 text-wc-text-secondary">
                        <p>El plan presencial te da acceso a sesiones de entrenamiento 1:1 con tu coach en Bogota. Cada sesion es personalizada y supervisada para garantizar tecnica perfecta y progresion optima.</p>
                        <p>Ademas de las sesiones presenciales, recibes tu programa completo en la plataforma WellCore para los dias que entrenas solo, mas plan nutricional y seguimiento semanal.</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    @php
                        $features = [
                            ['num' => '3-5', 'label' => 'Sesiones/semana'],
                            ['num' => '60', 'label' => 'Minutos/sesion'],
                            ['num' => '1:1', 'label' => 'Coach dedicado'],
                            ['num' => '24/7', 'label' => 'App + soporte'],
                        ];
                    @endphp
                    @foreach($features as $f)
                        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
                            <span class="font-data text-2xl font-bold text-wc-accent">{{ $f['num'] }}</span>
                            <p class="mt-1 text-xs text-wc-text-secondary">{{ $f['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Schedule --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text">HORARIOS DISPONIBLES</h2>
            <div class="mx-auto mt-10 max-w-2xl overflow-hidden rounded-xl border border-wc-border">
                <table class="w-full text-sm">
                    <thead class="bg-wc-bg-secondary">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-wc-text">Horario</th>
                            <th class="px-6 py-3 text-left font-semibold text-wc-text">Dias</th>
                            <th class="px-6 py-3 text-left font-semibold text-wc-text">Disponibilidad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-wc-border">
                        <tr>
                            <td class="px-6 py-3 text-wc-text">6:00 - 8:00 AM</td>
                            <td class="px-6 py-3 text-wc-text-secondary">Lun - Vie</td>
                            <td class="px-6 py-3"><span class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">Disponible</span></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-wc-text">9:00 - 11:00 AM</td>
                            <td class="px-6 py-3 text-wc-text-secondary">Lun - Vie</td>
                            <td class="px-6 py-3"><span class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">Disponible</span></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-wc-text">5:00 - 8:00 PM</td>
                            <td class="px-6 py-3 text-wc-text-secondary">Lun - Vie</td>
                            <td class="px-6 py-3"><span class="inline-flex rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-400">Limitado</span></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-3 text-wc-text">8:00 - 12:00 PM</td>
                            <td class="px-6 py-3 text-wc-text-secondary">Sabados</td>
                            <td class="px-6 py-3"><span class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">Disponible</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Pricing --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text">PRECIOS</h2>
            <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-3">
                @php
                    $plans = [
                        ['name' => '3 Sesiones/Semana', 'price' => '450.000', 'features' => ['12 sesiones presenciales/mes', 'Programa en app', 'Plan nutricional', 'Check-in semanal']],
                        ['name' => '4 Sesiones/Semana', 'price' => '550.000', 'popular' => true, 'features' => ['16 sesiones presenciales/mes', 'Programa en app', 'Plan nutricional', 'Check-in semanal', 'Video analisis tecnica']],
                        ['name' => '5 Sesiones/Semana', 'price' => '650.000', 'features' => ['20 sesiones presenciales/mes', 'Programa en app', 'Plan nutricional + suplementacion', 'Check-in semanal', 'Video analisis tecnica', 'Soporte prioritario']],
                    ];
                @endphp
                @foreach($plans as $plan)
                    <div class="relative rounded-xl border {{ isset($plan['popular']) ? 'border-wc-accent' : 'border-wc-border' }} bg-wc-bg-tertiary p-8">
                        @if(isset($plan['popular']))
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-wc-accent px-3 py-0.5 text-[10px] font-semibold text-white">MAS POPULAR</span>
                        @endif
                        <h3 class="text-sm font-semibold text-wc-text">{{ $plan['name'] }}</h3>
                        <div class="mt-3">
                            <span class="font-data text-3xl font-bold text-wc-text">${{ $plan['price'] }}</span>
                            <span class="text-sm text-wc-text-tertiary">COP/mes</span>
                        </div>
                        <ul class="mt-6 space-y-3">
                            @foreach($plan['features'] as $feature)
                                <li class="flex items-start gap-2 text-sm text-wc-text-secondary">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('presencial.form') }}" class="mt-8 block rounded-lg {{ isset($plan['popular']) ? 'bg-wc-accent text-white hover:bg-wc-accent-hover' : 'border border-wc-border bg-wc-bg-secondary text-wc-text hover:bg-wc-bg' }} px-6 py-3 text-center text-sm font-medium">
                            Inscribirme
                        </a>
                    </div>
                @endforeach
            </div>
            <p class="mt-6 text-center text-xs text-wc-text-tertiary">Ubicacion: Zona norte de Bogota. Direccion exacta se comparte al confirmar inscripcion.</p>
        </div>
    </section>
</x-layouts.public>
