<x-layouts.public>
    <x-slot:title>Preguntas Frecuentes - WellCore Fitness</x-slot:title>

    {{-- Header --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 sm:py-28 lg:px-8">
            <h1 class="font-display text-4xl tracking-wide text-wc-text sm:text-5xl lg:text-6xl">PREGUNTAS FRECUENTES</h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-wc-text-secondary">
                Todo lo que necesitas saber sobre WellCore y nuestros servicios.
            </p>
        </div>
    </section>

    {{-- FAQ Tabs + Accordion --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-3xl px-4 py-20 sm:px-6 lg:px-8"
             x-data="{ tab: 'general', open: null }">

            {{-- Tab Navigation --}}
            <div class="mb-10 flex flex-wrap justify-center gap-2">
                <button
                    x-on:click="tab = 'general'; open = null"
                    :class="tab === 'general'
                        ? 'rounded-full bg-wc-accent text-white px-4 py-2 text-sm font-medium'
                        : 'rounded-full text-wc-text-secondary hover:text-wc-text px-4 py-2 text-sm font-medium'"
                    class="shrink-0 transition-colors"
                >
                    General
                </button>
                <button
                    x-on:click="tab = 'planes'; open = null"
                    :class="tab === 'planes'
                        ? 'rounded-full bg-wc-accent text-white px-4 py-2 text-sm font-medium'
                        : 'rounded-full text-wc-text-secondary hover:text-wc-text px-4 py-2 text-sm font-medium'"
                    class="shrink-0 transition-colors"
                >
                    Planes
                </button>
                <button
                    x-on:click="tab = 'pagos'; open = null"
                    :class="tab === 'pagos'
                        ? 'rounded-full bg-wc-accent text-white px-4 py-2 text-sm font-medium'
                        : 'rounded-full text-wc-text-secondary hover:text-wc-text px-4 py-2 text-sm font-medium'"
                    class="shrink-0 transition-colors"
                >
                    Pagos
                </button>
                <button
                    x-on:click="tab = 'entrenamiento'; open = null"
                    :class="tab === 'entrenamiento'
                        ? 'rounded-full bg-wc-accent text-white px-4 py-2 text-sm font-medium'
                        : 'rounded-full text-wc-text-secondary hover:text-wc-text px-4 py-2 text-sm font-medium'"
                    class="shrink-0 transition-colors"
                >
                    Entrenamiento
                </button>
                <button
                    x-on:click="tab = 'soporte'; open = null"
                    :class="tab === 'soporte'
                        ? 'rounded-full bg-wc-accent text-white px-4 py-2 text-sm font-medium'
                        : 'rounded-full text-wc-text-secondary hover:text-wc-text px-4 py-2 text-sm font-medium'"
                    class="shrink-0 transition-colors"
                >
                    Soporte
                </button>
            </div>

            {{-- ============================================================ --}}
            {{-- TAB 1: GENERAL --}}
            {{-- ============================================================ --}}
            <div x-show="tab === 'general'" x-cloak class="divide-y divide-wc-border">

                {{-- G1 --}}
                <div>
                    <button
                        x-on:click="open = open === 'g1' ? null : 'g1'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que es WellCore Fitness?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'g1' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'g1'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                WellCore es una plataforma de coaching fitness online basada en ciencia. Cada programa
                                es 100% personalizado por un coach certificado, adaptado a tus objetivos, nivel de
                                experiencia y estilo de vida.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- G2 --}}
                <div>
                    <button
                        x-on:click="open = open === 'g2' ? null : 'g2'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Como funciona el coaching online?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'g2' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'g2'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Al inscribirte recibes un programa de entrenamiento y nutricion personalizado. Tu coach
                                monitorea tu progreso, ajusta el plan y se comunica contigo via chat. Los check-ins
                                semanales permiten hacer seguimiento continuo de tu avance.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- G3 --}}
                <div>
                    <button
                        x-on:click="open = open === 'g3' ? null : 'g3'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Necesito experiencia previa?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'g3' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'g3'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                No. Nuestros programas se adaptan a cualquier nivel de experiencia. Los principiantes
                                reciben guias detalladas de ejecucion y videos de referencia para cada ejercicio.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- G4 --}}
                <div>
                    <button
                        x-on:click="open = open === 'g4' ? null : 'g4'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Puedo entrenar en casa o necesito gimnasio?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'g4' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'g4'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Ambos. Al iniciar indicas tu equipamiento disponible y tu coach adapta el programa.
                                La mayoria de planes estan optimizados para gimnasio, pero tambien hay alternativas
                                para entrenamiento en casa.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- G5 --}}
                <div>
                    <button
                        x-on:click="open = open === 'g5' ? null : 'g5'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">En que paises estan disponibles?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'g5' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'g5'" x-collapse x-cloak>
                        <div class="pb-5">
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
            <div x-show="tab === 'planes'" x-cloak class="divide-y divide-wc-border">

                {{-- P1 --}}
                <div>
                    <button
                        x-on:click="open = open === 'p1' ? null : 'p1'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que planes ofrecen?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'p1' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'p1'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Tres planes principales: <strong>Esencial</strong> ($65/mes, programacion basica),
                                <strong>Metodo</strong> ($95/mes, seguimiento semanal + comunidad) y
                                <strong>Elite</strong> ($150/mes, coaching 1:1 + video check-ins). Tambien ofrecemos
                                <strong>RISE</strong> (programa intensivo de 12 semanas) y <strong>Presencial</strong>
                                (solo en Bogota).
                            </p>
                        </div>
                    </div>
                </div>

                {{-- P2 --}}
                <div>
                    <button
                        x-on:click="open = open === 'p2' ? null : 'p2'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que plan me conviene?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'p2' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'p2'" x-collapse x-cloak>
                        <div class="pb-5">
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
                <div>
                    <button
                        x-on:click="open = open === 'p3' ? null : 'p3'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que es el programa RISE?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'p3' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'p3'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Programa intensivo de 12 semanas con entrenamiento periodizado, nutricion detallada,
                                check-ins semanales obligatorios y comunidad exclusiva. Disenado para una transformacion
                                acelerada con resultados visibles.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- P4 --}}
                <div>
                    <button
                        x-on:click="open = open === 'p4' ? null : 'p4'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Cada cuanto se actualiza mi programa?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'p4' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'p4'" x-collapse x-cloak>
                        <div class="pb-5">
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
                <div>
                    <button
                        x-on:click="open = open === 'p5' ? null : 'p5'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Puedo cambiar de plan?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'p5' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'p5'" x-collapse x-cloak>
                        <div class="pb-5">
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
            <div x-show="tab === 'pagos'" x-cloak class="divide-y divide-wc-border">

                {{-- PA1 --}}
                <div>
                    <button
                        x-on:click="open = open === 'pa1' ? null : 'pa1'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Cuales son los metodos de pago?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'pa1' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'pa1'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Tarjeta de credito/debito y transferencias bancarias via Wompi. Precios en USD. Para
                                Colombia tambien aceptamos COP al tipo de cambio del dia.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- PA2 --}}
                <div>
                    <button
                        x-on:click="open = open === 'pa2' ? null : 'pa2'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Puedo cancelar en cualquier momento?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'pa2' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'pa2'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Si. La cancelacion es efectiva al final del periodo vigente. Sin contratos ni
                                penalizaciones. RISE es la excepcion ya que es un compromiso de 12 semanas.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- PA3 --}}
                <div>
                    <button
                        x-on:click="open = open === 'pa3' ? null : 'pa3'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Ofrecen reembolsos?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'pa3' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'pa3'" x-collapse x-cloak>
                        <div class="pb-5">
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
                <div>
                    <button
                        x-on:click="open = open === 'pa4' ? null : 'pa4'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">El pago es mensual o anual?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'pa4' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'pa4'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Mensual automatico. No ofrecemos planes anuales actualmente.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- PA5 --}}
                <div>
                    <button
                        x-on:click="open = open === 'pa5' ? null : 'pa5'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que pasa si falla mi pago?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'pa5' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'pa5'" x-collapse x-cloak>
                        <div class="pb-5">
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
            <div x-show="tab === 'entrenamiento'" x-cloak class="divide-y divide-wc-border">

                {{-- E1 --}}
                <div>
                    <button
                        x-on:click="open = open === 'e1' ? null : 'e1'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que incluye el plan nutricional?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'e1' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'e1'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Calculo de macros personalizado, estructura de comidas adaptada a tu horario, lista de
                                alimentos y alternativas. Es un marco flexible, no una dieta rigida. Los ajustes se
                                hacen segun tu progreso.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- E2 --}}
                <div>
                    <button
                        x-on:click="open = open === 'e2' ? null : 'e2'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Como son los check-ins?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'e2' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'e2'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Formulario semanal con peso, medidas, fotos de progreso y feedback del entrenamiento.
                                Tu coach revisa toda la informacion y ajusta el programa si es necesario.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- E3 --}}
                <div>
                    <button
                        x-on:click="open = open === 'e3' ? null : 'e3'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Puedo hacer cardio y pesas al mismo tiempo?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'e3' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'e3'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Si. El programa integra ambos segun tu objetivo. Para ganancia muscular se prioriza
                                fuerza; para perdida de grasa se balancea cardio y pesas de forma estrategica.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- E4 --}}
                <div>
                    <button
                        x-on:click="open = open === 'e4' ? null : 'e4'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que hago si tengo una lesion?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'e4' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'e4'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Informa a tu coach inmediatamente. Se ajusta el programa para trabajar alrededor de la
                                lesion. En casos graves se recomienda consulta medica antes de continuar el
                                entrenamiento.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- E5 --}}
                <div>
                    <button
                        x-on:click="open = open === 'e5' ? null : 'e5'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Cuanto tiempo dura cada sesion de entrenamiento?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 'e5' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 'e5'" x-collapse x-cloak>
                        <div class="pb-5">
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
            <div x-show="tab === 'soporte'" x-cloak class="divide-y divide-wc-border">

                {{-- S1 --}}
                <div>
                    <button
                        x-on:click="open = open === 's1' ? null : 's1'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Como contacto a soporte?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 's1' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 's1'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Email <a href="mailto:info@wellcorefitness.com" class="text-wc-accent hover:underline">info@wellcorefitness.com</a>,
                                chat dentro del dashboard, o WhatsApp. Los clientes Elite tienen soporte prioritario
                                con respuesta en menos de 12 horas.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- S2 --}}
                <div>
                    <button
                        x-on:click="open = open === 's2' ? null : 's2'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Puedo cambiar de coach?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 's2' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 's2'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Si. Solicita el cambio a traves de soporte y se asigna un nuevo coach en 48 horas. Sin
                                costo adicional.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- S3 --}}
                <div>
                    <button
                        x-on:click="open = open === 's3' ? null : 's3'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que pasa si no puedo entrenar por viaje o enfermedad?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 's3' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 's3'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Puedes pausar tu plan hasta por 30 dias sin costo. Contacta a soporte para activar la
                                pausa y se retoma cuando estes listo.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- S4 --}}
                <div>
                    <button
                        x-on:click="open = open === 's4' ? null : 's4'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Como accedo a la plataforma?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 's4' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 's4'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Via web en wellcorefitness.com. Inicia sesion con tu email y contrasena. Puedes instalar
                                la app como PWA en tu telefono para acceso rapido desde la pantalla de inicio.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- S5 --}}
                <div>
                    <button
                        x-on:click="open = open === 's5' ? null : 's5'"
                        class="flex w-full items-center justify-between py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Ofrecen soporte en otros idiomas?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': open === 's5' }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open === 's5'" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm text-wc-text-secondary">
                                Actualmente solo en espanol. Estamos trabajando en soporte en ingles y portugues para
                                2026.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    {{-- Contact CTA --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 lg:px-8">
            <h2 class="font-display text-2xl tracking-wide text-wc-text sm:text-3xl">NO ENCONTRASTE TU RESPUESTA?</h2>
            <p class="mt-4 text-wc-text-secondary">
                Escribenos directamente y resolveremos tus dudas.
            </p>
            <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="mailto:info@wellcorefitness.com" class="inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 font-semibold text-white shadow-lg shadow-wc-accent/20">
                    Contactar
                </a>
                <a href="https://wa.me/573001234567" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-full px-8 py-3.5 font-semibold text-wc-text hover:bg-wc-bg-secondary">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    WhatsApp
                </a>
            </div>
        </div>
    </section>

</x-layouts.public>
