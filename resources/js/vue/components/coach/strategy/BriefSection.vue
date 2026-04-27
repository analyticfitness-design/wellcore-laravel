<script setup>
import { computed } from 'vue';

const props = defineProps({
    brief: { type: Object, required: true },
});

const offerLabels = {
    esencial: 'Esencial',
    metodo: 'Método',
    elite: 'Elite',
    presencial: 'Presencial',
    otro: 'Otro',
};

const offerLabel = computed(() => offerLabels[props.brief.priority_offer] ?? props.brief.priority_offer);
</script>

<template>
    <article
        class="rounded-xl border border-wc-border bg-wc-bg-secondary py-10 pl-12 pr-6 transition-all duration-[240ms] ease-[cubic-bezier(0.16,1,0.3,1)] hover:translate-y-[-2px] hover:shadow-[0_20px_40px_-15px_rgba(220,38,38,0.25)]"
    >
        <div class="grid gap-10 lg:grid-cols-2">
            <div class="space-y-6">
                <div>
                    <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary">
                        Brief
                    </span>
                    <h3 class="mt-2 font-display text-3xl uppercase leading-tight tracking-tight text-wc-text">
                        {{ brief.title }}
                    </h3>
                </div>

                <p class="text-base leading-relaxed text-wc-text-secondary">
                    {{ brief.objective }}
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <span
                        class="inline-flex items-center rounded-full border border-wc-accent/40 bg-wc-accent/10 px-3 py-1 font-mono text-[10px] uppercase tracking-[0.2em] text-wc-accent"
                    >
                        Oferta · {{ offerLabel }}
                    </span>
                    <span
                        class="inline-flex items-center rounded-full border border-wc-border px-3 py-1 font-mono text-[10px] uppercase tracking-[0.2em] text-wc-text-secondary"
                    >
                        Métrica · {{ brief.target_metric }}
                    </span>
                </div>
            </div>

            <div class="space-y-8 border-l border-wc-border pl-10">
                <div>
                    <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary">
                        Mensaje clave
                    </span>
                    <blockquote
                        class="mt-3 border-l-4 border-wc-accent pl-5 font-editorial text-2xl italic leading-snug text-wc-text"
                    >
                        {{ brief.key_message }}
                    </blockquote>
                </div>

                <div>
                    <span class="font-mono text-[10px] uppercase tracking-[0.25em] text-wc-text-tertiary">
                        Encuadre / framing
                    </span>
                    <p class="mt-3 font-editorial text-lg italic leading-relaxed text-wc-text-secondary">
                        {{ brief.framing_copy }}
                    </p>
                </div>
            </div>
        </div>
    </article>
</template>
