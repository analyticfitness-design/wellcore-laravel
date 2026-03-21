<x-layouts.public>
    <x-slot:title>Nosotros - WellCore Fitness</x-slot:title>

    {{-- 1. HERO --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 sm:py-28 lg:px-8">
            <h1 class="font-display text-5xl tracking-wide text-wc-text sm:text-6xl lg:text-7xl">NOSOTROS</h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-wc-text-secondary sm:text-xl">
                Coaching fitness basado en ciencia, no en tendencias. Resultados sostenibles, no milagros.
            </p>
            <div class="mx-auto mt-8 h-1 w-16 rounded-full bg-wc-accent"></div>
        </div>
    </section>

    {{-- 2. MISSION / VISION --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">NUESTRA ESENCIA</h2>
                <p class="mt-2 text-wc-text-secondary">Lo que nos mueve y hacia donde vamos.</p>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-8 lg:grid-cols-2">
                {{-- Mision --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.047 8.287 8.287 0 009 9.601a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.468 5.99 5.99 0 00-1.925 3.547 5.975 5.975 0 01-2.133-1.001A3.75 3.75 0 0012 18z" />
                            </svg>
                        </div>
                        <h3 class="font-display text-2xl tracking-wide text-wc-text">NUESTRA MISION</h3>
                    </div>
                    <p class="mt-6 text-wc-text-secondary leading-relaxed">
                        Brindar coaching fitness de la mas alta calidad, combinando ciencia del ejercicio,
                        nutricion basada en datos y seguimiento constante. Cada cliente recibe un programa
                        completamente personalizado para alcanzar resultados sostenibles.
                    </p>
                    <p class="mt-4 text-wc-text-secondary leading-relaxed">
                        No vendemos transformaciones magicas ni promesas de resultados rapidos. Ofrecemos un
                        sistema probado, respaldado por evidencia cientifica, que funciona cuando te comprometes
                        con el proceso.
                    </p>
                </div>

                {{-- Vision --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="font-display text-2xl tracking-wide text-wc-text">NUESTRA VISION</h3>
                    </div>
                    <p class="mt-6 text-wc-text-secondary leading-relaxed">
                        Ser la plataforma de coaching fitness numero uno en Latinoamerica. Creemos que el acceso
                        a coaching de calidad no deberia ser un privilegio, sino una oportunidad disponible para
                        cualquier persona comprometida con su salud.
                    </p>
                    <p class="mt-4 text-wc-text-secondary leading-relaxed">
                        Democratizar el acceso a coaching personalizado, basado en ciencia, con tecnologia que
                        conecta a coaches certificados con personas que buscan transformar su vida de forma real
                        y sostenible.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. TEAM --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">NUESTRO EQUIPO</h2>
                <p class="mt-4 text-wc-text-secondary">Las personas detras de WellCore.</p>
            </div>

            {{-- Founder - Full Feature Card --}}
            <div class="mt-14 rounded-xl border border-wc-border bg-wc-bg p-8 sm:p-10">
                <div class="flex flex-col items-center gap-8 md:flex-row md:items-start">
                    <div class="flex-shrink-0">
                        <div class="flex h-28 w-28 items-center justify-center rounded-full border-2 border-wc-accent bg-wc-accent/10">
                            <span class="font-display text-4xl text-wc-accent">DE</span>
                        </div>
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <h3 class="font-display text-2xl tracking-wide text-wc-text">DANIEL ESPARZA</h3>
                        <p class="mt-1 text-sm font-semibold uppercase tracking-wider text-wc-accent">Fundador y Head Coach</p>
                        <p class="mt-4 text-wc-text-secondary leading-relaxed">
                            Especialista en fisiologia del ejercicio y nutricion deportiva con mas de 8 años de
                            experiencia en coaching personalizado. Certificado NSCA (National Strength and
                            Conditioning Association). Ha trabajado con cientos de clientes en Latinoamerica,
                            desarrollando un metodo basado en ciencia que prioriza resultados sostenibles sobre
                            soluciones rapidas.
                        </p>
                        <div class="mt-6 flex flex-wrap justify-center gap-3 md:justify-start">
                            <span class="inline-flex items-center rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1 text-xs font-medium text-wc-text-secondary">8+ años experiencia</span>
                            <span class="inline-flex items-center rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1 text-xs font-medium text-wc-text-secondary">Certificado NSCA</span>
                            <span class="inline-flex items-center rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1 text-xs font-medium text-wc-text-secondary">Fisiologia del ejercicio</span>
                            <span class="inline-flex items-center rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1 text-xs font-medium text-wc-text-secondary">Nutricion deportiva</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Other Coaches - Compact Cards --}}
            <div class="mt-8 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Coach 2: Nutricionista --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg p-8 text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border-2 border-wc-accent/30 bg-wc-accent/10">
                        <span class="font-display text-2xl text-wc-accent">NC</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Nutricion Coach</h3>
                    <p class="mt-1 text-sm font-semibold uppercase tracking-wider text-wc-accent">Nutricionista</p>
                    <p class="mt-4 text-sm text-wc-text-secondary leading-relaxed">
                        Profesional en nutricion clinica y deportiva. Responsable de los protocolos nutricionales
                        basados en evidencia cientifica de WellCore. Disenador de planes alimenticios personalizados
                        segun la fisiologia individual de cada cliente.
                    </p>
                </div>

                {{-- Coach 3: Strength Coach --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg p-8 text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border-2 border-wc-accent/30 bg-wc-accent/10">
                        <span class="font-display text-2xl text-wc-accent">SC</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Strength Coach</h3>
                    <p class="mt-1 text-sm font-semibold uppercase tracking-wider text-wc-accent">Entrenamiento de Fuerza</p>
                    <p class="mt-4 text-sm text-wc-text-secondary leading-relaxed">
                        Certificado CSCS (Certified Strength and Conditioning Specialist). Especialista en
                        periodizacion de entrenamiento y programacion de fuerza. Responsable de la estructura
                        de los programas de entrenamiento de WellCore.
                    </p>
                </div>

                {{-- Coach 4: Mindset Coach --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg p-8 text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border-2 border-wc-accent/30 bg-wc-accent/10">
                        <span class="font-display text-2xl text-wc-accent">MC</span>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Coach Mindset</h3>
                    <p class="mt-1 text-sm font-semibold uppercase tracking-wider text-wc-accent">Psicologia Deportiva</p>
                    <p class="mt-4 text-sm text-wc-text-secondary leading-relaxed">
                        Especialista en psicologia deportiva, adherencia y construccion de habitos sostenibles.
                        Trabaja con cada cliente para desarrollar la mentalidad necesaria para mantener la
                        consistencia y alcanzar objetivos a largo plazo.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. TIMELINE 2024-2026 --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">NUESTRA HISTORIA</h2>
                <p class="mt-4 text-wc-text-secondary">El camino de WellCore desde 2024.</p>
            </div>

            <div class="relative mx-auto mt-14 max-w-3xl">
                {{-- Vertical Line --}}
                <div class="absolute left-4 top-0 h-full w-px bg-wc-border sm:left-1/2 sm:-translate-x-px"></div>

                {{-- 2024 Q1 --}}
                <div class="relative mb-12 flex items-start gap-6 sm:gap-0">
                    <div class="hidden sm:block sm:w-1/2 sm:pr-12 sm:text-right">
                        <span class="font-data text-sm font-semibold text-wc-accent">2024 - Q1</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">Fundacion de WellCore</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            Nace WellCore con la mision de ofrecer coaching fitness basado en ciencia.
                            Primeros 10 clientes con programas completamente personalizados.
                        </p>
                    </div>
                    <div class="relative z-10 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-2 border-wc-accent bg-wc-bg sm:absolute sm:left-1/2 sm:-translate-x-1/2">
                        <div class="h-3 w-3 rounded-full bg-wc-accent"></div>
                    </div>
                    <div class="flex-1 sm:hidden">
                        <span class="font-data text-sm font-semibold text-wc-accent">2024 - Q1</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">Fundacion de WellCore</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            Nace WellCore con la mision de ofrecer coaching fitness basado en ciencia.
                            Primeros 10 clientes con programas completamente personalizados.
                        </p>
                    </div>
                    <div class="hidden sm:block sm:w-1/2 sm:pl-12"></div>
                </div>

                {{-- 2024 Q3 --}}
                <div class="relative mb-12 flex items-start gap-6 sm:gap-0">
                    <div class="hidden sm:block sm:w-1/2 sm:pr-12"></div>
                    <div class="relative z-10 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-2 border-wc-accent bg-wc-bg sm:absolute sm:left-1/2 sm:-translate-x-1/2">
                        <div class="h-3 w-3 rounded-full bg-wc-accent"></div>
                    </div>
                    <div class="flex-1 sm:w-1/2 sm:pl-12">
                        <span class="font-data text-sm font-semibold text-wc-accent">2024 - Q3</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">Plataforma Digital</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            Lanzamiento de la plataforma digital para gestion de clientes, seguimiento de
                            progreso y comunicacion coach-cliente. Superamos los 50 clientes activos.
                        </p>
                    </div>
                </div>

                {{-- 2025 Q1 --}}
                <div class="relative mb-12 flex items-start gap-6 sm:gap-0">
                    <div class="hidden sm:block sm:w-1/2 sm:pr-12 sm:text-right">
                        <span class="font-data text-sm font-semibold text-wc-accent">2025 - Q1</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">Programa RISE</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            Lanzamiento del programa RISE, nuestro metodo insignia de transformacion integral.
                            Expansion a 5 paises de Latinoamerica.
                        </p>
                    </div>
                    <div class="relative z-10 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-2 border-wc-accent bg-wc-bg sm:absolute sm:left-1/2 sm:-translate-x-1/2">
                        <div class="h-3 w-3 rounded-full bg-wc-accent"></div>
                    </div>
                    <div class="flex-1 sm:hidden">
                        <span class="font-data text-sm font-semibold text-wc-accent">2025 - Q1</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">Programa RISE</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            Lanzamiento del programa RISE, nuestro metodo insignia de transformacion integral.
                            Expansion a 5 paises de Latinoamerica.
                        </p>
                    </div>
                    <div class="hidden sm:block sm:w-1/2 sm:pl-12"></div>
                </div>

                {{-- 2025 Q3 --}}
                <div class="relative mb-12 flex items-start gap-6 sm:gap-0">
                    <div class="hidden sm:block sm:w-1/2 sm:pr-12"></div>
                    <div class="relative z-10 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-2 border-wc-accent bg-wc-bg sm:absolute sm:left-1/2 sm:-translate-x-1/2">
                        <div class="h-3 w-3 rounded-full bg-wc-accent"></div>
                    </div>
                    <div class="flex-1 sm:w-1/2 sm:pl-12">
                        <span class="font-data text-sm font-semibold text-wc-accent">2025 - Q3</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">App Movil</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            Lanzamiento de la aplicacion movil para acceso a entrenamientos, nutricion y
                            seguimiento en tiempo real. Superamos los 200 clientes activos.
                        </p>
                    </div>
                </div>

                {{-- 2026 Q1 --}}
                <div class="relative flex items-start gap-6 sm:gap-0">
                    <div class="hidden sm:block sm:w-1/2 sm:pr-12 sm:text-right">
                        <span class="font-data text-sm font-semibold text-wc-accent">2026 - Q1</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">Presencial Bogota</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            Lanzamiento del plan presencial en Bogota, Colombia. WellCore supera los 500 clientes
                            activos en toda Latinoamerica.
                        </p>
                    </div>
                    <div class="relative z-10 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-2 border-wc-accent bg-wc-accent sm:absolute sm:left-1/2 sm:-translate-x-1/2">
                        <div class="h-3 w-3 rounded-full bg-white"></div>
                    </div>
                    <div class="flex-1 sm:hidden">
                        <span class="font-data text-sm font-semibold text-wc-accent">2026 - Q1</span>
                        <h3 class="mt-1 text-lg font-semibold text-wc-text">Presencial Bogota</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">
                            Lanzamiento del plan presencial en Bogota, Colombia. WellCore supera los 500 clientes
                            activos en toda Latinoamerica.
                        </p>
                    </div>
                    <div class="hidden sm:block sm:w-1/2 sm:pl-12"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. STATS GRID --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">EN NUMEROS</h2>
                <p class="mt-2 text-wc-text-secondary">WellCore en cifras.</p>
            </div>

            <div class="mt-14 grid grid-cols-2 gap-8 lg:grid-cols-4">
                {{-- Clientes --}}
                <div class="text-center">
                    <p class="font-data text-4xl font-bold text-wc-accent sm:text-5xl">500+</p>
                    <p class="mt-2 text-sm font-medium text-wc-text-secondary">Clientes activos</p>
                </div>

                {{-- Paises --}}
                <div class="text-center">
                    <p class="font-data text-4xl font-bold text-wc-accent sm:text-5xl">5</p>
                    <p class="mt-2 text-sm font-medium text-wc-text-secondary">Paises</p>
                </div>

                {{-- Coaches --}}
                <div class="text-center">
                    <p class="font-data text-4xl font-bold text-wc-accent sm:text-5xl">15+</p>
                    <p class="mt-2 text-sm font-medium text-wc-text-secondary">Coaches certificados</p>
                </div>

                {{-- Adherencia --}}
                <div class="text-center">
                    <p class="font-data text-4xl font-bold text-wc-accent sm:text-5xl">94%</p>
                    <p class="mt-2 text-sm font-medium text-wc-text-secondary">Tasa de adherencia</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 6. VALUES GRID --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">NUESTROS VALORES</h2>
                <p class="mt-4 text-wc-text-secondary">Los principios que guian todo lo que hacemos.</p>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                {{-- Ciencia --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Ciencia</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Cada decision esta respaldada por evidencia cientifica. No seguimos modas, seguimos la investigacion.
                    </p>
                </div>

                {{-- Transparencia --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Transparencia</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Comunicacion clara y directa, sin promesas falsas. Honestidad sobre lo que funciona y lo que no.
                    </p>
                </div>

                {{-- Personalizacion --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Personalizacion</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Cada programa es unico. No usamos plantillas genericas. Tu plan se adapta a tu fisiologia y objetivos.
                    </p>
                </div>

                {{-- Comunidad --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Comunidad</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Crecimiento colectivo. La comunidad WellCore es un pilar fundamental del metodo y del proceso.
                    </p>
                </div>

                {{-- Resultados --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Resultados</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Datos reales, progreso medible. El progreso se rastrea con metricas objetivas, no con percepciones subjetivas.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 7. CTA --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 lg:px-8">
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">FORMA PARTE DE WELLCORE</h2>
            <p class="mx-auto mt-4 max-w-lg text-wc-text-secondary">
                Conoce nuestros planes y comienza tu proceso con un equipo que prioriza la ciencia y los resultados.
            </p>
            <div class="mt-8">
                <a href="{{ route('planes') }}" class="inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 font-semibold text-white shadow-lg shadow-wc-accent/20">
                    Ver Planes
                </a>
            </div>
        </div>
    </section>

</x-layouts.public>
