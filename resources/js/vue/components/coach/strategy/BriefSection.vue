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

const weekNumber = computed(() => props.brief.week_number ?? props.brief.week ?? null);

// Stopwords de español que nunca queremos resaltar
const STOPWORDS = new Set([
    'ENTRENAR','SIN','ES','SOLO','ACUMULADO','CON','DE','LA','EL','UN','UNA','POR','PARA','QUE','TU','TUS','MIS','NO','SI',
    'DEL','LOS','LAS','UNOS','UNAS','SE','SU','SUS','ME','TE','LE','NOS','OS','LES',
    'HAY','SON','SOY','ERES','SEMOS','ESTA','ESTE','ESTOS','ESTAS','ESO','ESA','PERO','MAS','MUY','YA','MÁS',
]);

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

const keyMessageWithKw = computed(() => {
    const raw = (props.brief.key_message ?? '').toString().trim();
    if (!raw) return '';

    // Si contiene **markdown bold**, ese gana
    if (/\*\*[^*]+\*\*/.test(raw)) {
        const safe = escapeHtml(raw).toUpperCase();
        // Después de escape, los * siguen siendo *
        const html = safe.replace(/\*\*([^*]+)\*\*/g, (_, w) => `<span class="kw">${w}</span>`);
        return html.replace(/\.\s+/g, '.<br>').replace(/,\s+/g, ',<br>');
    }

    // Fallback: detectar la palabra más larga ≥6 letras que no sea stopword
    const safe = escapeHtml(raw).toUpperCase();
    const words = safe.match(/[A-ZÁÉÍÓÚÜÑ]+/g) || [];
    let target = null;
    for (const w of words) {
        if (w.length < 6) continue;
        if (STOPWORDS.has(w)) continue;
        if (!target || w.length > target.length) target = w;
    }

    let html = safe;
    if (target) {
        // Reemplazar la primera ocurrencia exacta
        const re = new RegExp(`\\b${target}\\b`);
        html = html.replace(re, `<span class="kw">${target}</span>`);
    }

    // Wrap por línea
    return html.replace(/\.\s+/g, '.<br>').replace(/,\s+/g, ',<br>');
});

const citation = computed(() => {
    return weekNumber.value ? `— Mensaje clave · Semana ${weekNumber.value}` : '— Mensaje clave';
});
</script>

<template>
    <article class="card">
        <div class="pull-quote-row">
            <p class="pull-quote" v-html="keyMessageWithKw"></p>
            <p class="pull-quote-cite">{{ citation }}</p>
        </div>
        <div class="brief-grid">
            <div class="brief-left">
                <span class="mono-label">Brief</span>
                <h3 class="brief-h">{{ brief.title }}</h3>
                <p class="brief-obj">{{ brief.objective }}</p>
                <div class="chips">
                    <span class="chip chip-red">Oferta · {{ offerLabel }}</span>
                    <span v-if="brief.target_metric" class="chip chip-dim">Métrica · {{ brief.target_metric }}</span>
                </div>
            </div>
            <div class="brief-right">
                <span class="mono-label">Encuadre / framing</span>
                <p class="framing">{{ brief.framing_copy }}</p>
            </div>
        </div>
    </article>
</template>
