<script setup>
import { ref, watch } from 'vue';
import JsonSection from './JsonSection.vue';

const props = defineProps({
    content: { type: Object, required: true },
});
const emit = defineEmits(['save']);

const PRIORITY_OPTIONS = [
    { value: 'esencial', label: 'Esencial' },
    { value: 'metodo', label: 'Metodo' },
    { value: 'elite', label: 'Elite' },
    { value: 'presencial', label: 'Presencial' },
    { value: 'otro', label: 'Otro' },
];

const working = ref(deepClone(props.content));
const saveStatus = ref({ section: '', state: '' });

const bankAltHooks = ref('');
const bankAltCtas = ref('');
const bankAltCaptions = ref('');

watch(() => props.content, (val) => {
    working.value = deepClone(val);
}, { deep: true });

watch(working, (val) => {
    bankAltHooks.value = (val.bank?.alt_hooks ?? []).join('\n');
    bankAltCtas.value = (val.bank?.alt_ctas ?? []).join('\n');
    bankAltCaptions.value = (val.bank?.alt_captions ?? []).join('\n');
}, { immediate: true, deep: true });

function deepClone(obj) {
    return JSON.parse(JSON.stringify(obj ?? {}));
}

function flash(section, state) {
    saveStatus.value = { section, state };
    setTimeout(() => {
        if (saveStatus.value.section === section) saveStatus.value = { section: '', state: '' };
    }, 2500);
}

function emitSave(section) {
    emit('save', deepClone(working.value));
    flash(section, 'saved');
}

function saveBrief() {
    if (!working.value.brief?.title?.trim()) {
        alert('Title del brief es requerido');
        flash('brief', 'invalid');
        return;
    }
    emitSave('brief');
}

function onJsonSave(section, parsed) {
    if (section === 'reels') {
        if (!Array.isArray(parsed) || parsed.length !== 2) {
            alert('Reels debe ser array de 2');
            flash(section, 'invalid');
            return;
        }
        working.value.reels = parsed;
    } else if (section === 'stories') {
        if (!Array.isArray(parsed) || parsed.length !== 7) {
            alert('Stories debe ser array de 7 dias');
            flash(section, 'invalid');
            return;
        }
        working.value.stories = parsed;
    } else if (section === 'checklist') {
        if (!parsed?.phases || !Array.isArray(parsed.phases) || parsed.phases.length !== 4) {
            alert('Checklist debe contener 4 phases');
            flash(section, 'invalid');
            return;
        }
        working.value.checklist = parsed;
    } else if (section === 'hashtags') {
        if (!parsed?.sets || !Array.isArray(parsed.sets)) {
            alert('Hashtags debe contener sets[]');
            flash(section, 'invalid');
            return;
        }
        working.value.hashtags = parsed;
    }
    emitSave(section);
}

function saveBank() {
    working.value.bank = working.value.bank ?? {};
    working.value.bank.alt_hooks = bankAltHooks.value.split('\n').map((s) => s.trim()).filter(Boolean);
    working.value.bank.alt_ctas = bankAltCtas.value.split('\n').map((s) => s.trim()).filter(Boolean);
    working.value.bank.alt_captions = bankAltCaptions.value.split('\n').map((s) => s.trim()).filter(Boolean);
    emitSave('bank');
}

function statusLabel(section) {
    if (saveStatus.value.section !== section) return '';
    if (saveStatus.value.state === 'saved') return 'Guardado';
    if (saveStatus.value.state === 'invalid') return 'Invalido';
    return '';
}
function statusClass(section) {
    if (saveStatus.value.section !== section) return '';
    if (saveStatus.value.state === 'saved') return 'text-green-400';
    if (saveStatus.value.state === 'invalid') return 'text-red-400';
    return '';
}
</script>

<template>
  <div class="space-y-6">
    <section class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
      <div class="flex items-center justify-between">
        <h3 class="font-display text-xl uppercase tracking-tight text-wc-text">Brief</h3>
        <span class="font-mono text-[10px] uppercase" :class="statusClass('brief')">{{ statusLabel('brief') }}</span>
      </div>
      <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
        <label class="block">
          <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Title</span>
          <input v-model="working.brief.title" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text" />
        </label>
        <label class="block">
          <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Priority offer</span>
          <select v-model="working.brief.priority_offer" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text">
            <option v-for="opt in PRIORITY_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
        </label>
        <label class="block md:col-span-2">
          <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Objective</span>
          <textarea v-model="working.brief.objective" rows="2" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text"></textarea>
        </label>
        <label class="block md:col-span-2">
          <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Key message</span>
          <textarea v-model="working.brief.key_message" rows="2" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text"></textarea>
        </label>
        <label class="block">
          <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Target metric</span>
          <input v-model="working.brief.target_metric" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text" />
        </label>
        <label class="block">
          <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Weekly theme</span>
          <input v-model="working.brief.weekly_theme" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text" />
        </label>
        <label class="block md:col-span-2">
          <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Framing copy</span>
          <textarea v-model="working.brief.framing_copy" rows="2" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text"></textarea>
        </label>
      </div>
      <div class="mt-4 flex justify-end">
        <button @click="saveBrief" class="rounded-lg border border-wc-border bg-wc-bg px-4 py-1.5 text-xs font-medium text-wc-text hover:border-wc-accent hover:text-wc-accent">Guardar brief</button>
      </div>
    </section>

    <JsonSection title="Reels (2)" description="Edita el JSON estructurado de los 2 reels. Mantén las claves: hook, caption, music_note, production_notes, timecode_table."
      :value="working.reels" :rows="14" :status-label="statusLabel('reels')" :status-class="statusClass('reels')"
      @save="(p) => onJsonSave('reels', p)" />

    <JsonSection title="Stories (7 días)" description="Array de 7 días. Cada día contiene slides con texto editable."
      :value="working.stories" :rows="14" :status-label="statusLabel('stories')" :status-class="statusClass('stories')"
      @save="(p) => onJsonSave('stories', p)" />

    <JsonSection title="Checklist (4 fases)" description="Fases readonly por defecto; edita JSON solo si necesitas cambiar items."
      :value="working.checklist" :rows="10" :status-label="statusLabel('checklist')" :status-class="statusClass('checklist')"
      @save="(p) => onJsonSave('checklist', p)" />

    <section class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5">
      <div class="flex items-center justify-between">
        <h3 class="font-display text-xl uppercase tracking-tight text-wc-text">Bank</h3>
        <span class="font-mono text-[10px] uppercase" :class="statusClass('bank')">{{ statusLabel('bank') }}</span>
      </div>
      <div class="mt-4 grid grid-cols-1 gap-3">
        <label class="block">
          <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Alt hooks (uno por linea)</span>
          <textarea v-model="bankAltHooks" rows="5" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text"></textarea>
        </label>
        <label class="block">
          <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Alt CTAs (uno por linea)</span>
          <textarea v-model="bankAltCtas" rows="3" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text"></textarea>
        </label>
        <label class="block">
          <span class="font-mono text-[10px] uppercase tracking-[0.15em] text-wc-text-tertiary">Alt captions (uno por linea)</span>
          <textarea v-model="bankAltCaptions" rows="3" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text"></textarea>
        </label>
      </div>
      <div class="mt-4 flex justify-end">
        <button @click="saveBank" class="rounded-lg border border-wc-border bg-wc-bg px-4 py-1.5 text-xs font-medium text-wc-text hover:border-wc-accent hover:text-wc-accent">Guardar bank</button>
      </div>
    </section>

    <JsonSection title="Hashtags" description="Sets de hashtags. Edita el JSON: { sets: [{ name, tags: [] }] }."
      :value="working.hashtags" :rows="10" :status-label="statusLabel('hashtags')" :status-class="statusClass('hashtags')"
      @save="(p) => onJsonSave('hashtags', p)" />
  </div>
</template>
