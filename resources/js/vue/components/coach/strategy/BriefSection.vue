<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    brief: { type: Object, required: true },
});

const offerKeys = {
    esencial: 'brief_offer_esencial',
    metodo: 'brief_offer_metodo',
    elite: 'brief_offer_elite',
    presencial: 'brief_offer_presencial',
    otro: 'brief_offer_otro',
};

const offerLabel = computed(() => {
    const key = offerKeys[props.brief.priority_offer];
    return key ? t(`coach_growth.strategy.${key}`) : props.brief.priority_offer;
});

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
    return weekNumber.value
        ? t('coach_growth.strategy.brief_citation_full', { week: weekNumber.value })
        : t('coach_growth.strategy.brief_citation_short');
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
                <span class="mono-label">{{ t('coach_growth.strategy.brief_label') }}</span>
                <h3 class="brief-h">{{ brief.title }}</h3>
                <p class="brief-obj">{{ brief.objective }}</p>
                <div class="chips">
                    <span class="chip chip-red">{{ t('coach_growth.strategy.brief_offer_chip', { label: offerLabel }) }}</span>
                    <span v-if="brief.target_metric" class="chip chip-dim">{{ t('coach_growth.strategy.brief_metric_chip', { metric: brief.target_metric }) }}</span>
                </div>
            </div>
            <div class="brief-right">
                <span class="mono-label">{{ t('coach_growth.strategy.brief_framing_label') }}</span>
                <p class="framing">{{ brief.framing_copy }}</p>
            </div>
        </div>
    </article>
</template>
