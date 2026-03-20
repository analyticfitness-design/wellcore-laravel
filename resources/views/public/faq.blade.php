<x-layouts.public>
    <x-slot:title>Preguntas Frecuentes - WellCore Fitness</x-slot:title>

    {{-- Header --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-20 lg:px-8">
            <h1 class="font-display text-4xl tracking-wide text-wc-text sm:text-5xl lg:text-6xl">PREGUNTAS FRECUENTES</h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-wc-text-secondary">
                Todo lo que necesitas saber sobre WellCore y nuestros servicios.
            </p>
        </div>
    </section>

    {{-- FAQ Accordion --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">

            <div class="space-y-4" x-data="{ active: null }">

                {{-- Question 1 --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary">
                    <button
                        x-on:click="active = active === 1 ? null : 1"
                        class="flex w-full items-center justify-between px-6 py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Como funciona el coaching online?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': active === 1 }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="active === 1" x-collapse x-cloak>
                        <div class="border-t border-wc-border px-6 py-5">
                            <p class="text-sm text-wc-text-secondary">
                                Al inscribirte, recibes un programa de entrenamiento y nutricion completamente
                                personalizado a traves de nuestra plataforma. Tu coach te asigna el plan, monitorea
                                tu progreso y realiza ajustes segun los datos que reportas. La comunicacion es
                                constante a traves de chat y, en planes superiores, mediante video llamadas.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Question 2 --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary">
                    <button
                        x-on:click="active = active === 2 ? null : 2"
                        class="flex w-full items-center justify-between px-6 py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que plan me conviene mas?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': active === 2 }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="active === 2" x-collapse x-cloak>
                        <div class="border-t border-wc-border px-6 py-5">
                            <p class="text-sm text-wc-text-secondary">
                                Depende de tu nivel de experiencia y del acompanamiento que necesites. El plan
                                <strong>Esencial</strong> es ideal si ya tienes experiencia y solo necesitas
                                programacion. El <strong>Metodo</strong> es nuestro plan mas popular porque incluye
                                seguimiento semanal y comunidad. El <strong>Elite</strong> es para quienes quieren
                                el maximo nivel de personalizacion con coaching 1:1 y video check-ins.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Question 3 --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary">
                    <button
                        x-on:click="active = active === 3 ? null : 3"
                        class="flex w-full items-center justify-between px-6 py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Cuales son los metodos de pago?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': active === 3 }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="active === 3" x-collapse x-cloak>
                        <div class="border-t border-wc-border px-6 py-5">
                            <p class="text-sm text-wc-text-secondary">
                                Aceptamos pagos con tarjeta de credito/debito y transferencias bancarias. Todos los
                                precios estan en USD. El pago se realiza mensualmente de forma automatica. Para
                                clientes en Colombia, tambien aceptamos pagos en pesos colombianos al tipo de
                                cambio del dia.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Question 4 --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary">
                    <button
                        x-on:click="active = active === 4 ? null : 4"
                        class="flex w-full items-center justify-between px-6 py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Puedo cancelar mi suscripcion en cualquier momento?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': active === 4 }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="active === 4" x-collapse x-cloak>
                        <div class="border-t border-wc-border px-6 py-5">
                            <p class="text-sm text-wc-text-secondary">
                                Si. Puedes cancelar tu plan en cualquier momento. La cancelacion es efectiva al
                                final del periodo de facturacion actual. No hay contratos de permanencia ni
                                penalizaciones. El programa RISE es la excepcion, ya que es un programa de 12
                                semanas con compromiso completo.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Question 5 --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary">
                    <button
                        x-on:click="active = active === 5 ? null : 5"
                        class="flex w-full items-center justify-between px-6 py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Necesito experiencia previa en el gimnasio?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': active === 5 }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="active === 5" x-collapse x-cloak>
                        <div class="border-t border-wc-border px-6 py-5">
                            <p class="text-sm text-wc-text-secondary">
                                No. Nuestros programas se adaptan a cualquier nivel de experiencia. Al iniciar,
                                evaluamos tu condicion fisica, historial de entrenamiento y objetivos para crear
                                un programa adecuado. Los principiantes reciben guias detalladas de ejecucion
                                y videos de referencia para cada ejercicio.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Question 6 --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary">
                    <button
                        x-on:click="active = active === 6 ? null : 6"
                        class="flex w-full items-center justify-between px-6 py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que incluye el plan nutricional?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': active === 6 }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="active === 6" x-collapse x-cloak>
                        <div class="border-t border-wc-border px-6 py-5">
                            <p class="text-sm text-wc-text-secondary">
                                El plan nutricional incluye calculo de macronutrientes personalizado, estructura
                                de comidas adaptada a tu horario, lista de alimentos recomendados y alternativas.
                                No es una dieta rigida sino un marco flexible que puedes adaptar a tu estilo de
                                vida. Los ajustes se realizan segun tu progreso y feedback.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Question 7 --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary">
                    <button
                        x-on:click="active = active === 7 ? null : 7"
                        class="flex w-full items-center justify-between px-6 py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Cada cuanto se actualiza mi programa?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': active === 7 }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="active === 7" x-collapse x-cloak>
                        <div class="border-t border-wc-border px-6 py-5">
                            <p class="text-sm text-wc-text-secondary">
                                La frecuencia de actualizacion depende del plan. En el plan <strong>Esencial</strong>,
                                los ajustes son mensuales. En el <strong>Metodo</strong> y <strong>Elite</strong>,
                                los ajustes son semanales basados en tus check-ins. El programa de entrenamiento
                                se renueva cada 4-6 semanas segun la periodizacion planificada.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Question 8 --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary">
                    <button
                        x-on:click="active = active === 8 ? null : 8"
                        class="flex w-full items-center justify-between px-6 py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Que es el programa RISE?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': active === 8 }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="active === 8" x-collapse x-cloak>
                        <div class="border-t border-wc-border px-6 py-5">
                            <p class="text-sm text-wc-text-secondary">
                                RISE es nuestro programa intensivo de 12 semanas disenado para una transformacion
                                acelerada. Incluye entrenamiento periodizado, nutricion detallada, guia de
                                suplementacion, check-ins semanales obligatorios y acceso a una comunidad exclusiva
                                de participantes. Es ideal para quienes buscan un compromiso fuerte con resultados
                                visibles en un periodo definido.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Question 9 --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary">
                    <button
                        x-on:click="active = active === 9 ? null : 9"
                        class="flex w-full items-center justify-between px-6 py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Puedo entrenar en casa o necesito gimnasio?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': active === 9 }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="active === 9" x-collapse x-cloak>
                        <div class="border-t border-wc-border px-6 py-5">
                            <p class="text-sm text-wc-text-secondary">
                                Tenemos programas para ambos entornos. Al iniciar, indicas tu equipamiento
                                disponible y tu coach adapta el programa. La mayoria de planes estan optimizados
                                para gimnasio, pero tambien ofrecemos alternativas para entrenamiento en casa
                                con equipamiento minimo.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Question 10 --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary">
                    <button
                        x-on:click="active = active === 10 ? null : 10"
                        class="flex w-full items-center justify-between px-6 py-5 text-left"
                    >
                        <span class="text-sm font-semibold text-wc-text">Como contacto a soporte?</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                            :class="{ 'rotate-180': active === 10 }"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="active === 10" x-collapse x-cloak>
                        <div class="border-t border-wc-border px-6 py-5">
                            <p class="text-sm text-wc-text-secondary">
                                Puedes escribirnos a info@wellcorefitness.com o usar el sistema de mensajes
                                dentro de tu dashboard una vez inscrito. Para clientes Elite, el soporte es
                                prioritario con respuesta en menos de 12 horas.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Contact CTA --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h2 class="font-display text-2xl tracking-wide text-wc-text sm:text-3xl">NO ENCONTRASTE TU RESPUESTA?</h2>
            <p class="mt-4 text-wc-text-secondary">
                Escribenos directamente y resolveremos tus dudas.
            </p>
            <div class="mt-8">
                <a href="mailto:info@wellcorefitness.com" class="inline-flex items-center justify-center rounded-lg bg-wc-accent px-8 py-3 text-base font-medium text-white hover:bg-wc-accent-hover">
                    Contactar
                </a>
            </div>
        </div>
    </section>

</x-layouts.public>
