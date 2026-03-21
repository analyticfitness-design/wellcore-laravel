<x-layouts.public>
    <x-slot:title>Se Coach WellCore - WellCore Fitness</x-slot:title>
    <x-slot:description>Unete al equipo de coaches WellCore. Trabajo remoto, herramientas propias, comunidad y comisiones competitivas.</x-slot:description>

    {{-- Hero --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 sm:py-28 lg:px-8">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
                <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
            <h1 class="mt-6 font-display text-4xl tracking-wide text-wc-text sm:text-5xl lg:text-6xl">SE COACH WELLCORE</h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-wc-text-secondary">
                Unete al equipo de coaches que esta transformando el fitness en Latinoamerica.
                Trabaja con herramientas de ultima generacion, una comunidad de profesionales
                comprometidos y un modelo de negocio disenado para que crezcas.
            </p>
            <div class="mt-10">
                <a href="{{ route('coaches.apply') }}" class="inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover">
                    Aplica Ahora
                    <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Benefits --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">POR QUE SER COACH WELLCORE</h2>
                <p class="mt-4 text-wc-text-secondary">Beneficios de formar parte de nuestro equipo.</p>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Flexibilidad --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Trabajo Remoto Flexible</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Trabaja desde cualquier lugar, en tus horarios. Tu decides como organizar tu semana y tus sesiones con clientes.
                    </p>
                </div>

                {{-- Herramientas --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.049.58.025 1.194-.14 1.743" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Plataforma y Herramientas Propias</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Acceso completo a nuestra plataforma con generador de planes AI, seguimiento de clientes, check-ins y mensajeria integrada.
                    </p>
                </div>

                {{-- Comunidad --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
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
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Ingresos Competitivos</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Comision del 40% sobre los clientes asignados. Modelo transparente con pagos puntuales y sin sorpresas.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Requirements --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">REQUISITOS</h2>
                <p class="mt-4 text-wc-text-secondary">Lo que buscamos en un coach WellCore.</p>

                <div class="mt-10 space-y-4">
                    <div class="flex items-start gap-4 rounded-lg border border-wc-border bg-wc-bg p-5">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-wc-text">Certificacion en entrenamiento personal o nutricion</h4>
                            <p class="mt-1 text-sm text-wc-text-tertiary">Titulo o certificacion reconocida en el area de fitness, entrenamiento o nutricion.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 rounded-lg border border-wc-border bg-wc-bg p-5">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-wc-text">2+ anos de experiencia en coaching</h4>
                            <p class="mt-1 text-sm text-wc-text-tertiary">Experiencia demostrable trabajando con clientes en entrenamiento y/o nutricion.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 rounded-lg border border-wc-border bg-wc-bg p-5">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-wc-text">Conocimiento de periodizacion y nutricion basada en evidencia</h4>
                            <p class="mt-1 text-sm text-wc-text-tertiary">Entendimiento solido de principios de programacion de entrenamiento y nutricion cientifica.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 rounded-lg border border-wc-border bg-wc-bg p-5">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-wc-text">Habilidades de comunicacion y seguimiento</h4>
                            <p class="mt-1 text-sm text-wc-text-tertiary">Capacidad de comunicarte de forma clara, empatica y constante con tus clientes.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 rounded-lg border border-wc-border bg-wc-bg p-5">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-wc-text">Disponibilidad para capacitacion inicial</h4>
                            <p class="mt-1 text-sm text-wc-text-tertiary">Completar nuestro programa de onboarding para conocer la plataforma y el metodo WellCore.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Process --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">PROCESO DE SELECCION</h2>
                <p class="mt-4 text-wc-text-secondary">Tres pasos sencillos para unirte al equipo.</p>
            </div>

            <div class="mx-auto mt-14 grid max-w-4xl grid-cols-1 gap-8 md:grid-cols-3">
                {{-- Step 1 --}}
                <div class="relative rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent font-display text-xl text-white">1</div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Aplica</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Completa el formulario de aplicacion con tu informacion profesional y experiencia.
                    </p>
                </div>

                {{-- Step 2 --}}
                <div class="relative rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent font-display text-xl text-white">2</div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Entrevista</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Revisamos tu aplicacion y te contactamos para una entrevista virtual con nuestro equipo.
                    </p>
                </div>

                {{-- Step 3 --}}
                <div class="relative rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent font-display text-xl text-white">3</div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Comienza</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Completas el onboarding, recibes acceso a la plataforma y comienzas a recibir clientes.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">LISTO PARA COMENZAR?</h2>
            <p class="mx-auto mt-4 max-w-lg text-wc-text-secondary">
                Si cumples con los requisitos y quieres formar parte de un equipo basado en ciencia, aplica hoy.
            </p>
            <div class="mt-8">
                <a href="{{ route('coaches.apply') }}" class="inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover">
                    Aplica como Coach
                    <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

</x-layouts.public>
