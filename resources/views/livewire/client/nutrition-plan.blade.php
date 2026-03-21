<div>
    <div class="mb-8">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">PLAN DE NUTRICION</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Tu plan nutricional personalizado</p>
    </div>

    @if($plan)
        {{-- Macros Summary --}}
        @if(isset($plan['macros']))
            <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach(['calorias' => 'Calorias', 'proteina' => 'Proteina', 'carbohidratos' => 'Carbos', 'grasas' => 'Grasas'] as $key => $label)
                    @if(isset($plan['macros'][$key]))
                        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
                            <p class="font-data text-2xl font-bold text-wc-text">{{ $plan['macros'][$key] }}</p>
                            <p class="text-xs text-wc-text-tertiary">{{ $label }}</p>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- Macros Donut Chart --}}
        @if($hasMacros)
            <div class="mb-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-5"
                 x-data
                 x-init="
                    $nextTick(() => {
                        const canvas = document.getElementById('macrosChart');
                        if (canvas && window.Chart) {
                            new Chart(canvas, {
                                type: 'doughnut',
                                data: {
                                    labels: ['Proteina', 'Carbohidratos', 'Grasas'],
                                    datasets: [{
                                        data: [{{ $proteinGrams }}, {{ $carbGrams }}, {{ $fatGrams }}],
                                        backgroundColor: ['#DC2626', '#3B82F6', '#FBBF24'],
                                        borderColor: '#18181B',
                                        borderWidth: 3,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: {
                                            position: 'bottom',
                                            labels: {
                                                color: '#FAFAFA',
                                                padding: 16,
                                                font: { size: 12 },
                                                generateLabels: function(chart) {
                                                    const data = chart.data;
                                                    return data.labels.map((label, i) => ({
                                                        text: label + ' (' + data.datasets[0].data[i] + 'g)',
                                                        fillStyle: data.datasets[0].backgroundColor[i],
                                                        strokeStyle: data.datasets[0].backgroundColor[i],
                                                        lineWidth: 0,
                                                        hidden: false,
                                                        index: i
                                                    }));
                                                }
                                            }
                                        }
                                    },
                                    cutout: '65%',
                                }
                            });
                        }
                    });
                 "
            >
                <h3 class="mb-4 text-center font-display text-lg tracking-wide text-wc-text">DISTRIBUCION DE MACROS</h3>
                <div class="mx-auto max-w-xs">
                    <canvas id="macrosChart" width="200" height="200"></canvas>
                </div>
            </div>
        @else
            <div class="mb-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
                <p class="text-sm text-wc-text-secondary">Sin datos de macros. Tu coach asignara tu plan.</p>
            </div>
        @endif

        {{-- Meals --}}
        @if(isset($plan['comidas']))
            <div class="space-y-4">
                @foreach($plan['comidas'] as $i => $comida)
                    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="font-display text-lg tracking-wide text-wc-text">
                                {{ strtoupper($comida['nombre'] ?? 'COMIDA ' . ($i + 1)) }}
                            </h3>
                            @if(isset($comida['hora']))
                                <span class="text-sm text-wc-text-tertiary">{{ $comida['hora'] }}</span>
                            @endif
                        </div>
                        @if(isset($comida['alimentos']))
                            <ul class="mt-3 space-y-1.5">
                                @foreach($comida['alimentos'] as $alimento)
                                    <li class="flex items-center gap-2 text-sm text-wc-text-secondary">
                                        <span class="h-1.5 w-1.5 rounded-full bg-wc-accent"></span>
                                        {{ is_array($alimento) ? ($alimento['nombre'] ?? '') . ' — ' . ($alimento['cantidad'] ?? '') : $alimento }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        @if(isset($comida['notas']))
                            <p class="mt-3 text-xs italic text-wc-text-tertiary">{{ $comida['notas'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            {{-- Raw content display --}}
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
                <div class="prose prose-sm max-w-none text-wc-text-secondary">
                    {!! is_string($plan) ? nl2br(e($plan)) : '<pre>' . json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>' !!}
                </div>
            </div>
        @endif
    @else
        {{-- Empty State --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
            </svg>
            <h3 class="mt-4 font-display text-xl text-wc-text">SIN PLAN ASIGNADO</h3>
            <p class="mt-2 text-sm text-wc-text-secondary">Tu coach aun no ha asignado un plan de nutricion. Contactalo para mas informacion.</p>
        </div>
    @endif
</div>
