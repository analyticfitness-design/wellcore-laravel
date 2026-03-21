<x-layouts.public>
    <x-slot:title>Sé Coach WellCore - WellCore Fitness</x-slot:title>
    <x-slot:description>Únete al equipo de coaches WellCore. Trabajo remoto, herramientas propias, comunidad y comisiones competitivas.</x-slot:description>

    {{-- Hero --}}
    <section class="hero-gradient relative overflow-hidden bg-wc-bg-tertiary">
        {{-- Parallax decorative orbs --}}
        <div class="parallax-hero" aria-hidden="true">
            <div class="parallax-orb parallax-orb-1" data-parallax-speed="0.2"></div>
            <div class="parallax-orb parallax-orb-2" data-parallax-speed="0.35"></div>
            <div class="parallax-orb parallax-orb-3" data-parallax-speed="0.15"></div>
            <div class="parallax-orb parallax-orb-4" data-parallax-speed="0.25"></div>
            <div class="parallax-orb parallax-orb-5" data-parallax-speed="0.1"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 sm:py-28 lg:px-8" data-animate="fadeInUp">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
                <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
            <h1 class="mt-6 font-display text-4xl tracking-wide text-wc-text sm:text-5xl lg:text-6xl">SÉ COACH <span class="text-gradient-accent">WELLCORE</span></h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-wc-text-secondary">
                Únete al equipo de coaches que está transformando el fitness en Latinoamérica.
                Trabaja con herramientas de última generación, una comunidad de profesionales
                comprometidos y un modelo de negocio diseñado para que crezcas.
            </p>
            <div class="mt-10">
                <a href="{{ route('coaches.apply') }}" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover">
                    Aplica Ahora
                    <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Section Divider --}}
    <div class="h-px bg-gradient-to-r from-transparent via-wc-border to-transparent" aria-hidden="true"></div>

    {{-- Benefits --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center" data-animate="fadeInUp">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">POR QUÉ SER COACH WELLCORE</h2>
                <p class="mt-4 text-wc-text-secondary">Beneficios de formar parte de nuestro equipo.</p>
            </div>

            <div class="stagger-grid mt-14 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Flexibilidad --}}
                <div class="scroll-reveal-scale card-hover-lift card-glow rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center" data-animate="scaleIn" data-delay="100">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Trabajo Remoto Flexible</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Trabaja desde cualquier lugar, en tus horarios. Tú decides cómo organizar tu semana y tus sesiones con clientes.
                    </p>
                </div>

                {{-- Herramientas --}}
                <div class="scroll-reveal-scale card-hover-lift card-glow rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center" data-animate="scaleIn" data-delay="200">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.049.58.025 1.194-.14 1.743" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Plataforma y Herramientas Propias</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Acceso completo a nuestra plataforma con generador de planes AI, seguimiento de clientes, check-ins y mensajería integrada.
                    </p>
                </div>

                {{-- Comunidad --}}
                <div class="scroll-reveal-scale card-hover-lift card-glow rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center" data-animate="scaleIn" data-delay="300">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Comunidad de Coaches</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Forma parte de una red de profesionales comprometidos. Comparte conocimiento, resuelve dudas y crece junto a otros coaches.
                    </p>
                </div>

                {{-- Ingresos --}}
                <div class="scroll-reveal-scale card-hover-lift card-glow rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center" data-animate="scaleIn" data-delay="400">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Ingresos Competitivos</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Comisión del 40% sobre los clientes asignados. Modelo transparente con pagos puntuales y sin sorpresas.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Section Divider --}}
    <div class="h-px bg-gradient-to-r from-transparent via-wc-border to-transparent" aria-hidden="true"></div>

    {{-- Tu Portal de Coach — Dashboard Mockups --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">

                {{-- Left — Description --}}
                <div data-animate="slideInLeft">
                    <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Herramientas</p>
                    <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">TU PORTAL DE COACH</h2>
                    <p class="mt-4 max-w-lg text-sm text-wc-text-tertiary">
                        Accede a herramientas profesionales diseñadas para coaches de alto rendimiento. Todo lo que necesitas para gestionar tus clientes en un solo lugar.
                    </p>

                    <div class="mt-8 space-y-4">
                        <div class="scroll-reveal flex items-center gap-3" data-delay="100">
                            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
                                <svg class="h-3.5 w-3.5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </div>
                            <span class="text-sm text-wc-text-secondary">Dashboard con métricas en tiempo real</span>
                        </div>
                        <div class="scroll-reveal flex items-center gap-3" data-delay="200">
                            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
                                <svg class="h-3.5 w-3.5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </div>
                            <span class="text-sm text-wc-text-secondary">Centro de mensajes y broadcast</span>
                        </div>
                        <div class="scroll-reveal flex items-center gap-3" data-delay="300">
                            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
                                <svg class="h-3.5 w-3.5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </div>
                            <span class="text-sm text-wc-text-secondary">Generador de planes con IA</span>
                        </div>
                        <div class="scroll-reveal flex items-center gap-3" data-delay="400">
                            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
                                <svg class="h-3.5 w-3.5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </div>
                            <span class="text-sm text-wc-text-secondary">Analíticas de rendimiento por cliente</span>
                        </div>
                    </div>
                </div>

                {{-- Right — Mockups --}}
                <div class="space-y-6" data-animate="slideInRight">

                    {{-- Mockup 1: Coach Dashboard (Browser Window) --}}
                    <div class="animate-float-slow rounded-xl border border-wc-border bg-wc-bg shadow-2xl shadow-black/10">
                        {{-- Browser chrome --}}
                        <div class="flex items-center gap-2 border-b border-wc-border px-4 py-3">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                            <div class="ml-3 flex-1 rounded-md bg-wc-bg-secondary px-3 py-1">
                                <span class="text-xs text-wc-text-tertiary">coach.wellcorefitness.com</span>
                            </div>
                        </div>
                        {{-- Dashboard content --}}
                        <div class="space-y-3 p-4">
                            {{-- Header --}}
                            <div class="flex items-center justify-between">
                                <h3 class="font-display text-sm tracking-wide text-wc-text sm:text-base">DASHBOARD COACH <span class="text-wc-text-tertiary">&mdash;</span> <span class="text-wc-text-secondary">Marzo 2026</span></h3>
                            </div>

                            {{-- Stats row --}}
                            <div class="grid grid-cols-3 gap-2">
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2.5 text-center">
                                    <p class="font-data text-lg font-bold text-wc-accent">18</p>
                                    <p class="text-[10px] text-wc-text-tertiary">Clientes</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2.5 text-center">
                                    <p class="font-data text-lg font-bold text-emerald-400">$5.7M</p>
                                    <p class="text-[10px] text-wc-text-tertiary">Ingresos/mes</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2.5 text-center">
                                    <p class="font-data text-lg font-bold text-wc-text">91%</p>
                                    <p class="text-[10px] text-wc-text-tertiary">Adherencia promedio</p>
                                </div>
                            </div>

                            {{-- Client list --}}
                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                <p class="text-xs font-semibold text-wc-text">Clientes activos</p>
                                <div class="mt-2 space-y-1.5">
                                    @foreach([
                                        ['María G.', 'Método', '88%', 'text-amber-400', 'bg-amber-400/10 text-amber-400'],
                                        ['Juan R.', 'Elite', '94%', 'text-emerald-400', 'bg-emerald-400/10 text-emerald-400'],
                                        ['Andrea M.', 'Método', '100%', 'text-emerald-400', 'bg-emerald-400/10 text-emerald-400'],
                                        ['Carlos P.', 'Esencial', '76%', 'text-amber-400', 'bg-amber-400/10 text-amber-400'],
                                    ] as [$clientName, $plan, $adherence, $color, $badgeClass])
                                    <div class="flex items-center justify-between rounded bg-wc-bg px-2.5 py-1.5">
                                        <div class="flex items-center gap-2">
                                            <div class="h-5 w-5 rounded-full bg-wc-accent/10"></div>
                                            <span class="text-[11px] text-wc-text-secondary">{{ $clientName }}</span>
                                            <span class="rounded-full px-1.5 py-0.5 text-[9px] font-semibold {{ $badgeClass }}">{{ $plan }}</span>
                                        </div>
                                        <span class="font-data text-[11px] font-semibold {{ $color }}">{{ $adherence }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Recent activity --}}
                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                <p class="text-xs font-semibold text-wc-text">Actividad reciente</p>
                                <div class="mt-2 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 w-1.5 rounded-full bg-wc-accent"></div>
                                            <span class="text-[11px] text-wc-text-secondary">María envió check-in</span>
                                        </div>
                                        <span class="text-[10px] text-wc-text-tertiary">2m</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 w-1.5 rounded-full bg-emerald-400"></div>
                                            <span class="text-[11px] text-wc-text-secondary">Juan completó semana 8</span>
                                        </div>
                                        <span class="text-[10px] text-wc-text-tertiary">1h</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div>
                                            <span class="text-[11px] text-wc-text-secondary">Andrea subió fotos</span>
                                        </div>
                                        <span class="text-[10px] text-wc-text-tertiary">3h</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Mockup 2: Phone (Coach Mobile — Messages) --}}
                    <div class="flex justify-center">
                        <div class="animate-float mx-auto w-[280px] rounded-[2.5rem] border-[6px] border-wc-border bg-wc-bg-tertiary p-2 shadow-2xl">
                            {{-- Notch --}}
                            <div class="mx-auto mb-2 h-5 w-24 rounded-full bg-wc-bg-secondary"></div>
                            {{-- Screen content --}}
                            <div class="space-y-3 rounded-[2rem] bg-wc-bg p-4">
                                {{-- Title --}}
                                <div class="flex items-center justify-between">
                                    <p class="font-display text-lg tracking-wide text-wc-text">MENSAJES</p>
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-wc-accent text-[10px] font-bold text-white">3</span>
                                </div>

                                {{-- Conversation previews --}}
                                <div class="space-y-2">
                                    {{-- Message 1 (unread) --}}
                                    <div class="rounded-lg border border-wc-accent/30 bg-wc-accent/5 p-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="h-6 w-6 rounded-full bg-wc-accent/20"></div>
                                                <span class="text-[11px] font-semibold text-wc-text">María G.</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[9px] text-wc-text-tertiary">5m</span>
                                                <div class="h-2 w-2 rounded-full bg-wc-accent"></div>
                                            </div>
                                        </div>
                                        <p class="mt-1.5 text-[10px] text-wc-text-secondary">Coach, completé el entrenamiento de hoy!</p>
                                    </div>

                                    {{-- Message 2 --}}
                                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="h-6 w-6 rounded-full bg-wc-accent/10"></div>
                                                <span class="text-[11px] font-semibold text-wc-text">Juan R.</span>
                                            </div>
                                            <span class="text-[9px] text-wc-text-tertiary">1h</span>
                                        </div>
                                        <p class="mt-1.5 text-[10px] text-wc-text-secondary">Puedo cambiar el ejercicio de...</p>
                                    </div>

                                    {{-- Message 3 --}}
                                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="h-6 w-6 rounded-full bg-wc-accent/10"></div>
                                                <span class="text-[11px] font-semibold text-wc-text">Andrea M.</span>
                                            </div>
                                            <span class="text-[9px] text-wc-text-tertiary">3h</span>
                                        </div>
                                        <p class="mt-1.5 text-[10px] text-wc-text-secondary">Foto de progreso semana 4</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- Section Divider --}}
    <div class="h-px bg-gradient-to-r from-transparent via-wc-border to-transparent" aria-hidden="true"></div>

    {{-- Requirements --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <div data-animate="fadeInUp">
                    <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">REQUISITOS</h2>
                    <p class="mt-4 text-wc-text-secondary">Lo que buscamos en un coach WellCore.</p>
                </div>

                <div class="stagger-grid mt-10 space-y-4">
                    <div class="scroll-reveal card-hover-lift flex items-start gap-4 rounded-lg border border-wc-border bg-wc-bg-tertiary p-5" data-animate="fadeInUp" data-delay="100">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-wc-text">Certificación en entrenamiento personal o nutrición</h4>
                            <p class="mt-1 text-sm text-wc-text-tertiary">Título o certificación reconocida en el área de fitness, entrenamiento o nutrición.</p>
                        </div>
                    </div>

                    <div class="scroll-reveal card-hover-lift flex items-start gap-4 rounded-lg border border-wc-border bg-wc-bg-tertiary p-5" data-animate="fadeInUp" data-delay="200">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-wc-text">2+ años de experiencia en coaching</h4>
                            <p class="mt-1 text-sm text-wc-text-tertiary">Experiencia demostrable trabajando con clientes en entrenamiento y/o nutrición.</p>
                        </div>
                    </div>

                    <div class="scroll-reveal card-hover-lift flex items-start gap-4 rounded-lg border border-wc-border bg-wc-bg-tertiary p-5" data-animate="fadeInUp" data-delay="300">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-wc-text">Conocimiento de periodización y nutrición basada en evidencia</h4>
                            <p class="mt-1 text-sm text-wc-text-tertiary">Entendimiento sólido de principios de programación de entrenamiento y nutrición científica.</p>
                        </div>
                    </div>

                    <div class="scroll-reveal card-hover-lift flex items-start gap-4 rounded-lg border border-wc-border bg-wc-bg-tertiary p-5" data-animate="fadeInUp" data-delay="400">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-wc-text">Habilidades de comunicación y seguimiento</h4>
                            <p class="mt-1 text-sm text-wc-text-tertiary">Capacidad de comunicarte de forma clara, empática y constante con tus clientes.</p>
                        </div>
                    </div>

                    <div class="scroll-reveal card-hover-lift flex items-start gap-4 rounded-lg border border-wc-border bg-wc-bg-tertiary p-5" data-animate="fadeInUp" data-delay="500">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-wc-text">Disponibilidad para capacitación inicial</h4>
                            <p class="mt-1 text-sm text-wc-text-tertiary">Completar nuestro programa de onboarding para conocer la plataforma y el método WellCore.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Section Divider --}}
    <div class="h-px bg-gradient-to-r from-transparent via-wc-border to-transparent" aria-hidden="true"></div>

    {{-- Process --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center" data-animate="fadeInUp">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">PROCESO DE SELECCIÓN</h2>
                <p class="mt-4 text-wc-text-secondary">Tres pasos sencillos para unirte al equipo.</p>
            </div>

            <div class="relative mx-auto mt-14 grid max-w-4xl grid-cols-1 gap-8 md:grid-cols-3">
                {{-- Connector line (desktop only) --}}
                <div class="hidden lg:block absolute top-6 left-[calc(16.67%+1.5rem)] right-[calc(16.67%+1.5rem)] h-px bg-wc-border" aria-hidden="true"></div>

                {{-- Step 1 --}}
                <div class="stagger-grid card-hover-lift relative rounded-xl border border-wc-border bg-wc-bg p-8 text-center" data-animate="slideInUp" data-delay="100">
                    <div class="pulse-glow mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent font-display text-xl text-white">1</div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Aplica</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Completa el formulario de aplicación con tu información profesional y experiencia.
                    </p>
                </div>

                {{-- Step 2 --}}
                <div class="stagger-grid card-hover-lift relative rounded-xl border border-wc-border bg-wc-bg p-8 text-center" data-animate="slideInUp" data-delay="200">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent font-display text-xl text-white">2</div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Entrevista</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Revisamos tu aplicación y te contactamos para una entrevista virtual con nuestro equipo.
                    </p>
                </div>

                {{-- Step 3 --}}
                <div class="stagger-grid card-hover-lift relative rounded-xl border border-wc-border bg-wc-bg p-8 text-center" data-animate="slideInUp" data-delay="300">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent font-display text-xl text-white">3</div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Comienza</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Completas el onboarding, recibes acceso a la plataforma y comienzas a recibir clientes.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Section Divider --}}
    <div class="h-px bg-gradient-to-r from-transparent via-wc-border to-transparent" aria-hidden="true"></div>

    {{-- CTA --}}
    <section class="relative overflow-hidden bg-wc-bg">
        {{-- Gradient background orbs --}}
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -left-32 -top-32 h-80 w-80 rounded-full bg-wc-accent/5 blur-3xl"></div>
            <div class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-wc-accent/8 blur-3xl"></div>
            <div class="absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-wc-accent/3 blur-2xl"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 lg:px-8" data-animate="zoomIn">
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">LISTO PARA COMENZAR?</h2>
            <p class="mx-auto mt-4 max-w-lg text-wc-text-secondary">
                Si cumples con los requisitos y quieres formar parte de un equipo basado en ciencia, aplica hoy.
            </p>
            <div class="mt-8">
                <a href="{{ route('coaches.apply') }}" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover">
                    Aplica como Coach
                    <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

</x-layouts.public>
