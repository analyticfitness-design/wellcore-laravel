<x-layouts.public>
    <x-slot:title>Preguntas Frecuentes - WellCore Fitness</x-slot:title>

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
        <div class="relative mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 sm:py-28 lg:px-8"
             data-animate="fadeInUp">
            <h1 class="font-display text-4xl tracking-wide text-wc-text sm:text-5xl lg:text-6xl">PREGUNTAS <span class="text-gradient-accent">FRECUENTES</span></h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-wc-text-secondary">
                Todo lo que necesitas saber sobre WellCore y nuestros servicios.
            </p>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- FAQ Tabs + Accordion --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-3xl px-4 py-20 sm:px-6 lg:px-8"
             x-data="{ tab: 'general', open: null, search: '' }">

            {{-- Live Search --}}
            <div class="mb-8">
                <div class="relative">
                    <svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input type="text" x-model="search" placeholder="Buscar pregunta..." class="w-full rounded-full border border-wc-border bg-wc-bg-secondary py-3 pl-12 pr-4 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                    <button x-show="search.length > 0" @click="search = ''" class="absolute right-4 top-1/2 -translate-y-1/2 text-wc-text-tertiary hover:text-wc-text" x-transition>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            {{-- Tab Navigation --}}
            <div class="mb-10 scroll-reveal" x-show="search === ''">
                <div class="overflow-x-auto scrollbar-hide">
                    <div class="flex flex-wrap justify-center gap-2 pb-1">
                        {{-- General --}}
                        <button
                            x-on:click="tab = 'general'; open = null"
                            :class="tab === 'general'
                                ? 'rounded-full bg-wc-accent text-white px-4 py-2 text-sm font-medium transition-all duration-300'
                                : 'rounded-full text-wc-text-secondary hover:text-wc-text px-4 py-2 text-sm font-medium transition-all duration-300'"
                            class="shrink-0 inline-flex items-center gap-1.5"
                        >
                            {{-- Globe icon --}}
                            <svg class="h-4 w-4" :class="tab === 'general' ? 'scale-110' : 'scale-100'" style="transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1)" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                            </svg>
                            General
                        </button>
                        {{-- Planes --}}
                        <button
                            x-on:click="tab = 'planes'; open = null"
                            :class="tab === 'planes'
                                ? 'rounded-full bg-wc-accent text-white px-4 py-2 text-sm font-medium transition-all duration-300'
                                : 'rounded-full text-wc-text-secondary hover:text-wc-text px-4 py-2 text-sm font-medium transition-all duration-300'"
                            class="shrink-0 inline-flex items-center gap-1.5"
                        >
                            {{-- Credit-card icon --}}
                            <svg class="h-4 w-4" :class="tab === 'planes' ? 'scale-110' : 'scale-100'" style="transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1)" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                            </svg>
                            Planes
                        </button>
                        {{-- Pagos --}}
                        <button
                            x-on:click="tab = 'pagos'; open = null"
                            :class="tab === 'pagos'
                                ? 'rounded-full bg-wc-accent text-white px-4 py-2 text-sm font-medium transition-all duration-300'
                                : 'rounded-full text-wc-text-secondary hover:text-wc-text px-4 py-2 text-sm font-medium transition-all duration-300'"
                            class="shrink-0 inline-flex items-center gap-1.5"
                        >
                            {{-- Banknotes icon --}}
                            <svg class="h-4 w-4" :class="tab === 'pagos' ? 'scale-110' : 'scale-100'" style="transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1)" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                            </svg>
                            Pagos
                        </button>
                        {{-- Entrenamiento --}}
                        <button
                            x-on:click="tab = 'entrenamiento'; open = null"
                            :class="tab === 'entrenamiento'
                                ? 'rounded-full bg-wc-accent text-white px-4 py-2 text-sm font-medium transition-all duration-300'
                                : 'rounded-full text-wc-text-secondary hover:text-wc-text px-4 py-2 text-sm font-medium transition-all duration-300'"
                            class="shrink-0 inline-flex items-center gap-1.5"
                        >
                            {{-- Fire icon --}}
                            <svg class="h-4 w-4" :class="tab === 'entrenamiento' ? 'scale-110' : 'scale-100'" style="transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1)" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" />
                            </svg>
                            Entrenamiento
                        </button>
                        {{-- Soporte --}}
                        <button
                            x-on:click="tab = 'soporte'; open = null"
                            :class="tab === 'soporte'
                                ? 'rounded-full bg-wc-accent text-white px-4 py-2 text-sm font-medium transition-all duration-300'
                                : 'rounded-full text-wc-text-secondary hover:text-wc-text px-4 py-2 text-sm font-medium transition-all duration-300'"
                            class="shrink-0 inline-flex items-center gap-1.5"
                        >
                            {{-- Chat-bubble icon --}}
                            <svg class="h-4 w-4" :class="tab === 'soporte' ? 'scale-110' : 'scale-100'" style="transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1)" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                            </svg>
                            Soporte
                        </button>
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- SEARCH MODE: show all matching items regardless of tab        --}}
            {{-- ============================================================ --}}
            <div x-show="search !== ''" x-cloak class="divide-y divide-wc-border">

                {{-- G1 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Que es WellCore Fitness?" class="scroll-reveal" style="animation-delay:0ms">
                    <button x-on:click="open = open === 'g1' ? null : 'g1'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Que es WellCore Fitness?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'g1' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'g1'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">WellCore es una plataforma de coaching fitness online basada en ciencia. Cada programa es 100% personalizado por un coach certificado, adaptado a tus objetivos, nivel de experiencia y estilo de vida.</p></div></div>
                </div>

                {{-- G2 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Como funciona el coaching online?" class="scroll-reveal" style="animation-delay:50ms">
                    <button x-on:click="open = open === 'g2' ? null : 'g2'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Como funciona el coaching online?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'g2' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'g2'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Al inscribirte recibes un programa de entrenamiento y nutricion personalizado. Tu coach monitorea tu progreso, ajusta el plan y se comunica contigo via chat. Los check-ins semanales permiten hacer seguimiento continuo de tu avance.</p></div></div>
                </div>

                {{-- G3 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Necesito experiencia previa?" class="scroll-reveal" style="animation-delay:100ms">
                    <button x-on:click="open = open === 'g3' ? null : 'g3'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Necesito experiencia previa?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'g3' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'g3'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">No. Nuestros programas se adaptan a cualquier nivel de experiencia. Los principiantes reciben guias detalladas de ejecucion y videos de referencia para cada ejercicio.</p></div></div>
                </div>

                {{-- G4 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Puedo entrenar en casa o necesito gimnasio?" class="scroll-reveal" style="animation-delay:150ms">
                    <button x-on:click="open = open === 'g4' ? null : 'g4'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Puedo entrenar en casa o necesito gimnasio?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'g4' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'g4'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Ambos. Al iniciar indicas tu equipamiento disponible y tu coach adapta el programa. La mayoria de planes estan optimizados para gimnasio, pero tambien hay alternativas para entrenamiento en casa.</p></div></div>
                </div>

                {{-- G5 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="En que paises estan disponibles?" class="scroll-reveal" style="animation-delay:200ms">
                    <button x-on:click="open = open === 'g5' ? null : 'g5'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">En que paises estan disponibles?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'g5' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'g5'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Operamos en toda LATAM: Colombia, Mexico, Chile, Peru, Argentina y Ecuador. El coaching es 100% online, asi que puedes entrenar desde cualquier lugar.</p></div></div>
                </div>

                {{-- P1 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Que planes ofrecen?" class="scroll-reveal" style="animation-delay:250ms">
                    <button x-on:click="open = open === 'p1' ? null : 'p1'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Que planes ofrecen?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'p1' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'p1'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Tres planes principales: <strong>Esencial</strong> ($65/mes, programacion basica), <strong>Metodo</strong> ($95/mes, seguimiento semanal + comunidad) y <strong>Elite</strong> ($150/mes, coaching 1:1 + video check-ins). Tambien ofrecemos <strong>RISE</strong> (programa intensivo de 12 semanas) y <strong>Presencial</strong> (solo en Bucaramanga).</p></div></div>
                </div>

                {{-- P2 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Que plan me conviene?" class="scroll-reveal" style="animation-delay:300ms">
                    <button x-on:click="open = open === 'p2' ? null : 'p2'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Que plan me conviene?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'p2' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'p2'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary"><strong>Esencial</strong> si ya tienes experiencia y solo necesitas programacion. <strong>Metodo</strong> es el mas popular, ideal con seguimiento semanal y acceso a comunidad. <strong>Elite</strong> para quienes buscan el maximo nivel de personalizacion con coaching 1:1.</p></div></div>
                </div>

                {{-- P3 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Que es el programa RISE?" class="scroll-reveal" style="animation-delay:350ms">
                    <button x-on:click="open = open === 'p3' ? null : 'p3'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Que es el programa RISE?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'p3' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'p3'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Programa intensivo de 12 semanas con entrenamiento periodizado, nutricion detallada, check-ins semanales obligatorios y comunidad exclusiva. Disenado para una transformacion acelerada con resultados visibles.</p></div></div>
                </div>

                {{-- P4 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Cada cuanto se actualiza mi programa?" class="scroll-reveal" style="animation-delay:400ms">
                    <button x-on:click="open = open === 'p4' ? null : 'p4'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Cada cuanto se actualiza mi programa?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'p4' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'p4'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">En el plan <strong>Esencial</strong>, los ajustes son mensuales. En <strong>Metodo</strong> y <strong>Elite</strong>, los ajustes son semanales basados en tus check-ins. El programa de entrenamiento se renueva cada 4-6 semanas segun la periodizacion planificada.</p></div></div>
                </div>

                {{-- P5 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Puedo cambiar de plan?" class="scroll-reveal" style="animation-delay:450ms">
                    <button x-on:click="open = open === 'p5' ? null : 'p5'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Puedo cambiar de plan?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'p5' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'p5'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Si. Puedes subir o bajar de plan en cualquier momento. El cambio aplica en el siguiente periodo de facturacion.</p></div></div>
                </div>

                {{-- PA1 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Cuales son los metodos de pago?" class="scroll-reveal" style="animation-delay:500ms">
                    <button x-on:click="open = open === 'pa1' ? null : 'pa1'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Cuales son los metodos de pago?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'pa1' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'pa1'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Tarjeta de credito/debito y transferencias bancarias via Wompi. Precios en USD. Para Colombia tambien aceptamos COP al tipo de cambio del dia.</p></div></div>
                </div>

                {{-- PA2 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Puedo cancelar en cualquier momento?" class="scroll-reveal" style="animation-delay:550ms">
                    <button x-on:click="open = open === 'pa2' ? null : 'pa2'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Puedo cancelar en cualquier momento?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'pa2' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'pa2'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Si. La cancelacion es efectiva al final del periodo vigente. Sin contratos ni penalizaciones. RISE es la excepcion ya que es un compromiso de 12 semanas.</p></div></div>
                </div>

                {{-- PA3 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Ofrecen reembolsos?" class="scroll-reveal" style="animation-delay:600ms">
                    <button x-on:click="open = open === 'pa3' ? null : 'pa3'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Ofrecen reembolsos?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'pa3' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'pa3'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Garantia de 7 dias para nuevos clientes. Despues de ese periodo, no hay reembolso pero mantienes acceso hasta el fin del periodo pagado. Consulta nuestra <a href="{{ route('reembolsos') }}" class="text-wc-accent hover:underline">politica de reembolso</a> completa.</p></div></div>
                </div>

                {{-- PA4 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="El pago es mensual o anual?" class="scroll-reveal" style="animation-delay:650ms">
                    <button x-on:click="open = open === 'pa4' ? null : 'pa4'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">El pago es mensual o anual?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'pa4' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'pa4'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Mensual automatico. No ofrecemos planes anuales actualmente.</p></div></div>
                </div>

                {{-- PA5 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Que pasa si falla mi pago?" class="scroll-reveal" style="animation-delay:700ms">
                    <button x-on:click="open = open === 'pa5' ? null : 'pa5'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Que pasa si falla mi pago?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'pa5' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'pa5'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Se reintenta automaticamente. Si persiste, recibes una notificacion para actualizar tu metodo de pago. El acceso se pausa tras 5 dias sin pago exitoso.</p></div></div>
                </div>

                {{-- E1 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Que incluye el plan nutricional?" class="scroll-reveal" style="animation-delay:750ms">
                    <button x-on:click="open = open === 'e1' ? null : 'e1'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Que incluye el plan nutricional?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'e1' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'e1'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Calculo de macros personalizado, estructura de comidas adaptada a tu horario, lista de alimentos y alternativas. Es un marco flexible, no una dieta rigida. Los ajustes se hacen segun tu progreso.</p></div></div>
                </div>

                {{-- E2 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Como son los check-ins?" class="scroll-reveal" style="animation-delay:800ms">
                    <button x-on:click="open = open === 'e2' ? null : 'e2'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Como son los check-ins?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'e2' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'e2'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Formulario semanal con peso, medidas, fotos de progreso y feedback del entrenamiento. Tu coach revisa toda la informacion y ajusta el programa si es necesario.</p></div></div>
                </div>

                {{-- E3 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Puedo hacer cardio y pesas al mismo tiempo?" class="scroll-reveal" style="animation-delay:850ms">
                    <button x-on:click="open = open === 'e3' ? null : 'e3'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Puedo hacer cardio y pesas al mismo tiempo?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'e3' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'e3'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Si. El programa integra ambos segun tu objetivo. Para ganancia muscular se prioriza fuerza; para perdida de grasa se balancea cardio y pesas de forma estrategica.</p></div></div>
                </div>

                {{-- E4 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Que hago si tengo una lesion?" class="scroll-reveal" style="animation-delay:900ms">
                    <button x-on:click="open = open === 'e4' ? null : 'e4'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Que hago si tengo una lesion?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'e4' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'e4'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Informa a tu coach inmediatamente. Se ajusta el programa para trabajar alrededor de la lesion. En casos graves se recomienda consulta medica antes de continuar el entrenamiento.</p></div></div>
                </div>

                {{-- E5 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Cuanto tiempo dura cada sesion de entrenamiento?" class="scroll-reveal" style="animation-delay:950ms">
                    <button x-on:click="open = open === 'e5' ? null : 'e5'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Cuanto tiempo dura cada sesion de entrenamiento?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 'e5' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 'e5'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Tipicamente 45-75 minutos dependiendo del plan y objetivo. Incluye calentamiento y vuelta a la calma. Frecuencia sugerida: 3-6 dias por semana.</p></div></div>
                </div>

                {{-- S1 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Como contacto a soporte?" class="scroll-reveal" style="animation-delay:1000ms">
                    <button x-on:click="open = open === 's1' ? null : 's1'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Como contacto a soporte?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 's1' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 's1'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Email <a href="mailto:info@wellcorefitness.com" class="text-wc-accent hover:underline">info@wellcorefitness.com</a>, chat dentro del dashboard, o WhatsApp. Los clientes Elite tienen soporte prioritario con respuesta en menos de 12 horas.</p></div></div>
                </div>

                {{-- S2 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Puedo cambiar de coach?" class="scroll-reveal" style="animation-delay:1050ms">
                    <button x-on:click="open = open === 's2' ? null : 's2'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Puedo cambiar de coach?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 's2' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 's2'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Si. Solicita el cambio a traves de soporte y se asigna un nuevo coach en 48 horas. Sin costo adicional.</p></div></div>
                </div>

                {{-- S3 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Que pasa si no puedo entrenar por viaje o enfermedad?" class="scroll-reveal" style="animation-delay:1100ms">
                    <button x-on:click="open = open === 's3' ? null : 's3'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Que pasa si no puedo entrenar por viaje o enfermedad?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 's3' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 's3'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Puedes pausar tu plan hasta por 30 dias sin costo. Contacta a soporte para activar la pausa y se retoma cuando estes listo.</p></div></div>
                </div>

                {{-- S4 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Como accedo a la plataforma?" class="scroll-reveal" style="animation-delay:1150ms">
                    <button x-on:click="open = open === 's4' ? null : 's4'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Como accedo a la plataforma?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 's4' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 's4'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Via web en wellcorefitness.com. Inicia sesion con tu email y contrasena. Puedes instalar la app como PWA en tu telefono para acceso rapido desde la pantalla de inicio.</p></div></div>
                </div>

                {{-- S5 search --}}
                <div x-show="search === '' || $el.dataset.question.toLowerCase().includes(search.toLowerCase())" data-question="Ofrecen soporte en otros idiomas?" class="scroll-reveal" style="animation-delay:1200ms">
                    <button x-on:click="open = open === 's5' ? null : 's5'" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2">
                        <span class="text-sm font-semibold text-wc-text">Ofrecen soporte en otros idiomas?</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open === 's5' }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open === 's5'" x-collapse x-cloak><div class="pb-5 px-2"><p class="text-sm text-wc-text-secondary">Actualmente solo en espanol. Estamos trabajando en soporte en ingles y portugues para 2026.</p></div></div>
                </div>

                {{-- No results message --}}
                <div x-show="search !== '' && !$el.previousElementSibling" x-cloak class="py-12 text-center">
                    <p class="text-sm text-wc-text-tertiary">No se encontraron resultados para "<span x-text="search" class="text-wc-text"></span>".</p>
                </div>

            </div>

            {{-- ============================================================ --}}
            {{-- TAB 1: GENERAL --}}
            {{-- ============================================================ --}}
            <div x-show="tab === 'general' && search === ''" x-cloak class="divide-y divide-wc-border">

                {{-- G1 --}}
                <div class="scroll-reveal" style="animation-delay:0ms" data-question="Que es WellCore Fitness?">
                    <button
                        x-on:click="open = open === 'g1' ? null : 'g1'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que es WellCore Fitness?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'g1' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'g1'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                WellCore es una plataforma de coaching fitness online basada en ciencia. Cada programa
                                es 100% personalizado por un coach certificado, adaptado a tus objetivos, nivel de
                                experiencia y estilo de vida.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- G2 --}}
                <div class="scroll-reveal" style="animation-delay:50ms" data-question="Como funciona el coaching online?">
                    <button
                        x-on:click="open = open === 'g2' ? null : 'g2'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Como funciona el coaching online?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'g2' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'g2'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Al inscribirte recibes un programa de entrenamiento y nutricion personalizado. Tu coach
                                monitorea tu progreso, ajusta el plan y se comunica contigo via chat. Los check-ins
                                semanales permiten hacer seguimiento continuo de tu avance.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- G3 --}}
                <div class="scroll-reveal" style="animation-delay:100ms" data-question="Necesito experiencia previa?">
                    <button
                        x-on:click="open = open === 'g3' ? null : 'g3'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Necesito experiencia previa?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'g3' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'g3'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                No. Nuestros programas se adaptan a cualquier nivel de experiencia. Los principiantes
                                reciben guias detalladas de ejecucion y videos de referencia para cada ejercicio.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- G4 --}}
                <div class="scroll-reveal" style="animation-delay:150ms" data-question="Puedo entrenar en casa o necesito gimnasio?">
                    <button
                        x-on:click="open = open === 'g4' ? null : 'g4'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Puedo entrenar en casa o necesito gimnasio?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'g4' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'g4'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Ambos. Al iniciar indicas tu equipamiento disponible y tu coach adapta el programa.
                                La mayoria de planes estan optimizados para gimnasio, pero tambien hay alternativas
                                para entrenamiento en casa.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- G5 --}}
                <div class="scroll-reveal" style="animation-delay:200ms" data-question="En que paises estan disponibles?">
                    <button
                        x-on:click="open = open === 'g5' ? null : 'g5'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">En que paises estan disponibles?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'g5' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'g5'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Operamos en toda LATAM: Colombia, Mexico, Chile, Peru, Argentina y Ecuador. El coaching
                                es 100% online, asi que puedes entrenar desde cualquier lugar.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ============================================================ --}}
            {{-- TAB 2: PLANES --}}
            {{-- ============================================================ --}}
            <div x-show="tab === 'planes' && search === ''" x-cloak class="divide-y divide-wc-border">

                {{-- P1 --}}
                <div class="scroll-reveal" style="animation-delay:0ms" data-question="Que planes ofrecen?">
                    <button
                        x-on:click="open = open === 'p1' ? null : 'p1'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que planes ofrecen?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'p1' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'p1'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Tres planes principales: <strong>Esencial</strong> ($65/mes, programacion basica),
                                <strong>Metodo</strong> ($95/mes, seguimiento semanal + comunidad) y
                                <strong>Elite</strong> ($150/mes, coaching 1:1 + video check-ins). Tambien ofrecemos
                                <strong>RISE</strong> (programa intensivo de 12 semanas) y <strong>Presencial</strong>
                                (solo en Bucaramanga).
                            </p>
                        </div>
                    </div>
                </div>

                {{-- P2 --}}
                <div class="scroll-reveal" style="animation-delay:50ms" data-question="Que plan me conviene?">
                    <button
                        x-on:click="open = open === 'p2' ? null : 'p2'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que plan me conviene?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'p2' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'p2'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                <strong>Esencial</strong> si ya tienes experiencia y solo necesitas programacion.
                                <strong>Metodo</strong> es el mas popular, ideal con seguimiento semanal y acceso a
                                comunidad. <strong>Elite</strong> para quienes buscan el maximo nivel de personalizacion
                                con coaching 1:1.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- P3 --}}
                <div class="scroll-reveal" style="animation-delay:100ms" data-question="Que es el programa RISE?">
                    <button
                        x-on:click="open = open === 'p3' ? null : 'p3'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que es el programa RISE?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'p3' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'p3'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Programa intensivo de 12 semanas con entrenamiento periodizado, nutricion detallada,
                                check-ins semanales obligatorios y comunidad exclusiva. Disenado para una transformacion
                                acelerada con resultados visibles.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- P4 --}}
                <div class="scroll-reveal" style="animation-delay:150ms" data-question="Cada cuanto se actualiza mi programa?">
                    <button
                        x-on:click="open = open === 'p4' ? null : 'p4'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Cada cuanto se actualiza mi programa?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'p4' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'p4'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                En el plan <strong>Esencial</strong>, los ajustes son mensuales. En
                                <strong>Metodo</strong> y <strong>Elite</strong>, los ajustes son semanales basados en
                                tus check-ins. El programa de entrenamiento se renueva cada 4-6 semanas segun la
                                periodizacion planificada.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- P5 --}}
                <div class="scroll-reveal" style="animation-delay:200ms" data-question="Puedo cambiar de plan?">
                    <button
                        x-on:click="open = open === 'p5' ? null : 'p5'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Puedo cambiar de plan?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'p5' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'p5'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Si. Puedes subir o bajar de plan en cualquier momento. El cambio aplica en el siguiente
                                periodo de facturacion.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ============================================================ --}}
            {{-- TAB 3: PAGOS --}}
            {{-- ============================================================ --}}
            <div x-show="tab === 'pagos' && search === ''" x-cloak class="divide-y divide-wc-border">

                {{-- PA1 --}}
                <div class="scroll-reveal" style="animation-delay:0ms" data-question="Cuales son los metodos de pago?">
                    <button
                        x-on:click="open = open === 'pa1' ? null : 'pa1'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Cuales son los metodos de pago?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'pa1' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'pa1'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Tarjeta de credito/debito y transferencias bancarias via Wompi. Precios en USD. Para
                                Colombia tambien aceptamos COP al tipo de cambio del dia.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- PA2 --}}
                <div class="scroll-reveal" style="animation-delay:50ms" data-question="Puedo cancelar en cualquier momento?">
                    <button
                        x-on:click="open = open === 'pa2' ? null : 'pa2'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Puedo cancelar en cualquier momento?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'pa2' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'pa2'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Si. La cancelacion es efectiva al final del periodo vigente. Sin contratos ni
                                penalizaciones. RISE es la excepcion ya que es un compromiso de 12 semanas.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- PA3 --}}
                <div class="scroll-reveal" style="animation-delay:100ms" data-question="Ofrecen reembolsos?">
                    <button
                        x-on:click="open = open === 'pa3' ? null : 'pa3'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Ofrecen reembolsos?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'pa3' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'pa3'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Garantia de 7 dias para nuevos clientes. Despues de ese periodo, no hay reembolso
                                pero mantienes acceso hasta el fin del periodo pagado. Consulta nuestra
                                <a href="{{ route('reembolsos') }}" class="text-wc-accent hover:underline">politica de reembolso</a>
                                completa.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- PA4 --}}
                <div class="scroll-reveal" style="animation-delay:150ms" data-question="El pago es mensual o anual?">
                    <button
                        x-on:click="open = open === 'pa4' ? null : 'pa4'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">El pago es mensual o anual?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'pa4' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'pa4'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Mensual automatico. No ofrecemos planes anuales actualmente.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- PA5 --}}
                <div class="scroll-reveal" style="animation-delay:200ms" data-question="Que pasa si falla mi pago?">
                    <button
                        x-on:click="open = open === 'pa5' ? null : 'pa5'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que pasa si falla mi pago?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'pa5' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'pa5'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Se reintenta automaticamente. Si persiste, recibes una notificacion para actualizar tu
                                metodo de pago. El acceso se pausa tras 5 dias sin pago exitoso.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ============================================================ --}}
            {{-- TAB 4: ENTRENAMIENTO --}}
            {{-- ============================================================ --}}
            <div x-show="tab === 'entrenamiento' && search === ''" x-cloak class="divide-y divide-wc-border">

                {{-- E1 --}}
                <div class="scroll-reveal" style="animation-delay:0ms" data-question="Que incluye el plan nutricional?">
                    <button
                        x-on:click="open = open === 'e1' ? null : 'e1'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que incluye el plan nutricional?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'e1' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'e1'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Calculo de macros personalizado, estructura de comidas adaptada a tu horario, lista de
                                alimentos y alternativas. Es un marco flexible, no una dieta rigida. Los ajustes se
                                hacen segun tu progreso.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- E2 --}}
                <div class="scroll-reveal" style="animation-delay:50ms" data-question="Como son los check-ins?">
                    <button
                        x-on:click="open = open === 'e2' ? null : 'e2'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Como son los check-ins?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'e2' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'e2'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Formulario semanal con peso, medidas, fotos de progreso y feedback del entrenamiento.
                                Tu coach revisa toda la informacion y ajusta el programa si es necesario.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- E3 --}}
                <div class="scroll-reveal" style="animation-delay:100ms" data-question="Puedo hacer cardio y pesas al mismo tiempo?">
                    <button
                        x-on:click="open = open === 'e3' ? null : 'e3'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Puedo hacer cardio y pesas al mismo tiempo?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'e3' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'e3'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Si. El programa integra ambos segun tu objetivo. Para ganancia muscular se prioriza
                                fuerza; para perdida de grasa se balancea cardio y pesas de forma estrategica.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- E4 --}}
                <div class="scroll-reveal" style="animation-delay:150ms" data-question="Que hago si tengo una lesion?">
                    <button
                        x-on:click="open = open === 'e4' ? null : 'e4'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que hago si tengo una lesion?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'e4' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'e4'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Informa a tu coach inmediatamente. Se ajusta el programa para trabajar alrededor de la
                                lesion. En casos graves se recomienda consulta medica antes de continuar el
                                entrenamiento.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- E5 --}}
                <div class="scroll-reveal" style="animation-delay:200ms" data-question="Cuanto tiempo dura cada sesion de entrenamiento?">
                    <button
                        x-on:click="open = open === 'e5' ? null : 'e5'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Cuanto tiempo dura cada sesion de entrenamiento?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 'e5' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'e5'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Tipicamente 45-75 minutos dependiendo del plan y objetivo. Incluye calentamiento y
                                vuelta a la calma. Frecuencia sugerida: 3-6 dias por semana.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ============================================================ --}}
            {{-- TAB 5: SOPORTE --}}
            {{-- ============================================================ --}}
            <div x-show="tab === 'soporte' && search === ''" x-cloak class="divide-y divide-wc-border">

                {{-- S1 --}}
                <div class="scroll-reveal" style="animation-delay:0ms" data-question="Como contacto a soporte?">
                    <button
                        x-on:click="open = open === 's1' ? null : 's1'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Como contacto a soporte?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 's1' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 's1'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Email <a href="mailto:info@wellcorefitness.com" class="text-wc-accent hover:underline">info@wellcorefitness.com</a>,
                                chat dentro del dashboard, o WhatsApp. Los clientes Elite tienen soporte prioritario
                                con respuesta en menos de 12 horas.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- S2 --}}
                <div class="scroll-reveal" style="animation-delay:50ms" data-question="Puedo cambiar de coach?">
                    <button
                        x-on:click="open = open === 's2' ? null : 's2'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Puedo cambiar de coach?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 's2' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 's2'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Si. Solicita el cambio a traves de soporte y se asigna un nuevo coach en 48 horas. Sin
                                costo adicional.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- S3 --}}
                <div class="scroll-reveal" style="animation-delay:100ms" data-question="Que pasa si no puedo entrenar por viaje o enfermedad?">
                    <button
                        x-on:click="open = open === 's3' ? null : 's3'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que pasa si no puedo entrenar por viaje o enfermedad?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 's3' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 's3'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Puedes pausar tu plan hasta por 30 dias sin costo. Contacta a soporte para activar la
                                pausa y se retoma cuando estes listo.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- S4 --}}
                <div class="scroll-reveal" style="animation-delay:150ms" data-question="Como accedo a la plataforma?">
                    <button
                        x-on:click="open = open === 's4' ? null : 's4'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Como accedo a la plataforma?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 's4' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 's4'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Via web en wellcorefitness.com. Inicia sesion con tu email y contrasena. Puedes instalar
                                la app como PWA en tu telefono para acceso rapido desde la pantalla de inicio.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- S5 --}}
                <div class="scroll-reveal" style="animation-delay:200ms" data-question="Ofrecen soporte en otros idiomas?">
                    <button
                        x-on:click="open = open === 's5' ? null : 's5'"
                        class="flex w-full items-center justify-between py-5 text-left transition-colors hover:bg-wc-bg-secondary/50 rounded-lg px-2"
                    >
                        <span class="text-sm font-semibold text-wc-text">Ofrecen soporte en otros idiomas?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                            :class="{ 'rotate-180': open === 's5' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 's5'" x-collapse x-cloak>
                        <div class="pb-5 px-2">
                            <p class="text-sm text-wc-text-secondary">
                                Actualmente solo en espanol. Estamos trabajando en soporte en ingles y portugues para
                                2026.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- No results (search mode, DOM-based detection) --}}
            <div x-show="search !== ''" x-cloak class="mt-2">
                <template x-if="search !== ''">
                    <div>
                        <p
                            x-show="$el.closest('[x-data]').querySelectorAll('[data-question]').length > 0 && [...$el.closest('[x-data]').querySelectorAll('[data-question]')].filter(el => el.dataset.question.toLowerCase().includes(search.toLowerCase())).length === 0"
                            class="py-12 text-center text-sm text-wc-text-tertiary"
                        >
                            No se encontraron resultados para "<span x-text="search" class="text-wc-text font-medium"></span>".
                        </p>
                    </div>
                </template>
            </div>

        </div>
    </section>

    <div class="section-divider"></div>

    {{-- Contact CTA --}}
    <section class="relative overflow-hidden bg-wc-bg-tertiary">
        {{-- Decorative orbs --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0">
            <div class="absolute -left-20 top-1/2 h-64 w-64 -translate-y-1/2 rounded-full bg-wc-accent/5 blur-3xl"></div>
            <div class="absolute -right-20 top-1/2 h-64 w-64 -translate-y-1/2 rounded-full bg-wc-accent/5 blur-3xl"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 lg:px-8">
            <h2 class="font-display text-2xl tracking-wide text-wc-text sm:text-3xl">NO ENCONTRASTE TU <span class="text-gradient-accent">RESPUESTA?</span></h2>
            <p class="mt-4 text-wc-text-secondary">
                Escribenos directamente y resolveremos tus dudas.
            </p>
            <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="mailto:info@wellcorefitness.com" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 font-semibold text-white shadow-lg shadow-wc-accent/20 transition-all hover:shadow-wc-accent/40">
                    Contactar
                </a>
                <a href="https://wa.me/573001234567" target="_blank" rel="noopener noreferrer" class="btn-press inline-flex items-center justify-center gap-2 rounded-full px-8 py-3.5 font-semibold text-wc-text transition-all hover:bg-green-500/10 hover:text-green-400 hover:shadow-lg hover:shadow-green-500/10">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    WhatsApp
                </a>
            </div>
        </div>
    </section>

</x-layouts.public>
