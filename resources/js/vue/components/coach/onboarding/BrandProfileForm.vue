<script setup>
import { reactive, ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import { useCoachStrategyStore } from '../../../stores/coachStrategy';
import ProfileSection01Identity from './ProfileSection01Identity.vue';
import ProfileSection02Specialty from './ProfileSection02Specialty.vue';
import ProfileSection03Audience from './ProfileSection03Audience.vue';
import ProfileSection04MethodsTopics from './ProfileSection04MethodsTopics.vue';
import ProfileSection05Voice from './ProfileSection05Voice.vue';
import ProfileSection06OffersAndPosts from './ProfileSection06OffersAndPosts.vue';

const { t } = useI18n();

const props = defineProps({
    initial: { type: Object, default: null },
    isSaving: { type: Boolean, default: false },
});
const emit = defineEmits(['submit']);

const store = useCoachStrategyStore();

function emptyForm() {
    return {
        brand_name: '',
        city: null,
        country_code: null,
        specialty_primary: null,
        specialty_primary_other: null,
        specialty_secondary: null,
        specialty_secondary_other: null,
        differentiator: '',
        audience_age_range: null,
        audience_gender: null,
        audience_pain_main: '',
        audience_offer_main: null,
        preferred_methodologies: [],
        preferred_methodologies_other: [],
        content_topics: [],
        content_topics_other: [],
        voice_adjectives: [],
        voice_samples: [],
        active_offers: [],
        top_working_posts: [],
    };
}

const form = reactive(emptyForm());
const errorMessages = ref([]);
const saveStatus = ref(t('coach_growth.onboarding_form.save_status_auto'));
let savedTimer = null;
let hydrated = false;

function hydrateFrom(initial) {
    if (!initial) return;
    const base = emptyForm();
    Object.keys(base).forEach((key) => {
        if (initial[key] !== undefined && initial[key] !== null) {
            form[key] = initial[key];
        } else if (Array.isArray(base[key])) {
            form[key] = [];
        }
    });
}

onMounted(() => {
    if (props.initial) {
        hydrateFrom(props.initial);
        hydrated = true;
    }
});

watch(
    () => props.initial,
    (val) => {
        if (val && !hydrated) {
            hydrateFrom(val);
            hydrated = true;
        }
    }
);

function debounce(fn, ms) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), ms);
    };
}

const debouncedSaveDraft = debounce(async () => {
    if (!hydrated && !props.initial) {
        // Permitir guardado aunque no haya initial (primer onboarding)
    }
    saveStatus.value = t('coach_growth.onboarding_form.save_status_saving');
    try {
        await store.saveProfileDraft({ ...form });
        saveStatus.value = t('coach_growth.onboarding_form.save_status_saved');
        if (savedTimer) clearTimeout(savedTimer);
        savedTimer = setTimeout(() => {
            saveStatus.value = t('coach_growth.onboarding_form.save_status_auto');
        }, 2000);
    } catch (e) {
        // Silent fail
        saveStatus.value = t('coach_growth.onboarding_form.save_status_auto');
    }
}, 500);

watch(
    form,
    () => {
        debouncedSaveDraft();
    },
    { deep: true }
);

onBeforeUnmount(() => {
    if (savedTimer) clearTimeout(savedTimer);
});

function validate() {
    const errors = [];
    if (!form.brand_name || form.brand_name.length < 1) {
        errors.push(t('coach_growth.onboarding_form.err_brand_name'));
    }
    if (!form.specialty_primary) {
        errors.push(t('coach_growth.onboarding_form.err_specialty_primary'));
    }
    if (form.specialty_primary === 'otro' && !form.specialty_primary_other) {
        errors.push(t('coach_growth.onboarding_form.err_specialty_primary_other'));
    }
    if (!form.differentiator || form.differentiator.length < 20) {
        errors.push(t('coach_growth.onboarding_form.err_differentiator'));
    }
    if (!form.audience_age_range) {
        errors.push(t('coach_growth.onboarding_form.err_age_range'));
    }
    if (!form.audience_gender) {
        errors.push(t('coach_growth.onboarding_form.err_gender'));
    }
    if (!form.audience_pain_main || form.audience_pain_main.length < 1) {
        errors.push(t('coach_growth.onboarding_form.err_pain_main'));
    }
    if (!form.audience_offer_main) {
        errors.push(t('coach_growth.onboarding_form.err_offer_main'));
    }
    if (!Array.isArray(form.preferred_methodologies) || form.preferred_methodologies.length < 1) {
        errors.push(t('coach_growth.onboarding_form.err_methodologies'));
    }
    if (!Array.isArray(form.content_topics) || form.content_topics.length < 1) {
        errors.push(t('coach_growth.onboarding_form.err_topics'));
    }
    if (!Array.isArray(form.voice_adjectives) || form.voice_adjectives.length !== 3) {
        errors.push(t('coach_growth.onboarding_form.err_voice_adj'));
    }
    if (!Array.isArray(form.active_offers) || form.active_offers.length < 1) {
        errors.push(t('coach_growth.onboarding_form.err_active_offers'));
    } else {
        form.active_offers.forEach((o, i) => {
            if (!o.name) errors.push(t('coach_growth.onboarding_form.err_offer_name', { n: i + 1 }));
            if (o.price === null || o.price === undefined || isNaN(Number(o.price))) {
                errors.push(t('coach_growth.onboarding_form.err_offer_price', { n: i + 1 }));
            }
        });
    }
    return errors;
}

function handleSubmit() {
    const errors = validate();
    errorMessages.value = errors;
    if (errors.length > 0) return;
    emit('submit', { ...form });
}
</script>

<template>
  <form @submit.prevent="handleSubmit" class="space-y-12">
    <ProfileSection01Identity v-model="form" />
    <ProfileSection02Specialty v-model="form" />
    <ProfileSection03Audience v-model="form" />
    <ProfileSection04MethodsTopics v-model="form" />
    <ProfileSection05Voice v-model="form" />
    <ProfileSection06OffersAndPosts v-model="form" />

    <div
      v-if="errorMessages.length"
      class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-400"
    >
      <p class="font-mono uppercase text-xs mb-2 tracking-[0.15em]">{{ t('coach_growth.onboarding_form.errors_title') }}</p>
      <ul class="list-disc pl-5 space-y-1">
        <li v-for="msg in errorMessages" :key="msg">{{ msg }}</li>
      </ul>
    </div>

    <div class="sticky bottom-6 flex items-center justify-between rounded-xl border border-wc-border bg-wc-bg-secondary p-4 shadow-lg">
      <span class="font-mono text-xs uppercase tracking-[0.15em] text-wc-text-tertiary">
        {{ saveStatus }}
      </span>
      <button
        type="submit"
        :disabled="isSaving"
        class="rounded-lg bg-wc-accent px-6 py-3 font-display text-sm uppercase tracking-wide text-white transition-opacity hover:opacity-90 disabled:opacity-50"
      >
        {{ t('coach_growth.onboarding_form.submit_btn') }}
      </button>
    </div>
  </form>
</template>
