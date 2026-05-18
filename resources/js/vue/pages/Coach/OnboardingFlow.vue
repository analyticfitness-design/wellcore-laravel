<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import CoachLayout from '../../layouts/CoachLayout.vue';

const { t } = useI18n();

const stages = computed(() => [
  {
    id: 'preventa',
    num: '01',
    badge: t('coach_growth.flow.stage1_badge'),
    title: t('coach_growth.flow.stage1_title'),
    objective: t('coach_growth.flow.stage1_objective'),
    accent: 'red',
    navLabel: t('coach_growth.flow.nav_preventa'),
    steps: [
      {
        index: t('coach_growth.flow.stage1_step1_index'),
        title: t('coach_growth.flow.stage1_step1_title'),
        tag: t('coach_growth.flow.stage1_step1_tag'),
        quote: {
          lead: t('coach_growth.flow.stage1_step1_quote_lead'),
          em: t('coach_growth.flow.stage1_step1_quote_em'),
        },
        body: t('coach_growth.flow.stage1_step1_body'),
        rule: {
          label: t('coach_growth.flow.stage1_step1_rule_label'),
          text: t('coach_growth.flow.stage1_step1_rule_text'),
        },
        checklist: [
          { strong: t('coach_growth.flow.stage1_step1_check1_strong'), rest: t('coach_growth.flow.stage1_step1_check1_rest') },
          { strong: t('coach_growth.flow.stage1_step1_check2_strong'), rest: t('coach_growth.flow.stage1_step1_check2_rest') },
          { strong: t('coach_growth.flow.stage1_step1_check3_strong'), rest: t('coach_growth.flow.stage1_step1_check3_rest') },
          { strong: t('coach_growth.flow.stage1_step1_check4_strong'), rest: t('coach_growth.flow.stage1_step1_check4_rest') },
        ],
      },
      {
        index: t('coach_growth.flow.stage1_step2_index'),
        title: t('coach_growth.flow.stage1_step2_title'),
        tag: t('coach_growth.flow.stage1_step2_tag'),
        editorial: t('coach_growth.flow.stage1_step2_editorial'),
        body: t('coach_growth.flow.stage1_step2_body'),
        rule: {
          label: t('coach_growth.flow.stage1_step2_rule_label'),
          text: t('coach_growth.flow.stage1_step2_rule_text'),
        },
        checklist: [
          { strong: t('coach_growth.flow.stage1_step2_check1_strong'), rest: t('coach_growth.flow.stage1_step2_check1_rest') },
          { strong: t('coach_growth.flow.stage1_step2_check2_strong'), rest: t('coach_growth.flow.stage1_step2_check2_rest') },
          { strong: t('coach_growth.flow.stage1_step2_check3_strong'), rest: t('coach_growth.flow.stage1_step2_check3_rest') },
          { strong: t('coach_growth.flow.stage1_step2_check4_strong'), rest: t('coach_growth.flow.stage1_step2_check4_rest') },
        ],
      },
      {
        index: t('coach_growth.flow.stage1_step3_index'),
        title: t('coach_growth.flow.stage1_step3_title'),
        tag: t('coach_growth.flow.stage1_step3_tag'),
        quote: {
          lead: t('coach_growth.flow.stage1_step3_quote_lead'),
          em: t('coach_growth.flow.stage1_step3_quote_em'),
        },
        body: t('coach_growth.flow.stage1_step3_body'),
        checklist: [
          { strong: '', rest: t('coach_growth.flow.stage1_step3_check1') },
          { strong: '', rest: t('coach_growth.flow.stage1_step3_check2') },
          { strong: '', rest: t('coach_growth.flow.stage1_step3_check3') },
          { strong: '', rest: t('coach_growth.flow.stage1_step3_check4') },
          { strong: '', rest: t('coach_growth.flow.stage1_step3_check5') },
        ],
        rule: {
          label: t('coach_growth.flow.stage1_step3_rule_label'),
          text: t('coach_growth.flow.stage1_step3_rule_text'),
        },
      },
      {
        index: t('coach_growth.flow.stage1_step4_index'),
        title: t('coach_growth.flow.stage1_step4_title'),
        tag: t('coach_growth.flow.stage1_step4_tag'),
        editorial: t('coach_growth.flow.stage1_step4_editorial'),
        body: t('coach_growth.flow.stage1_step4_body'),
        checklist: [
          { strong: t('coach_growth.flow.stage1_step4_check1_strong'), rest: t('coach_growth.flow.stage1_step4_check1_rest') },
          { strong: t('coach_growth.flow.stage1_step4_check2_strong'), rest: t('coach_growth.flow.stage1_step4_check2_rest') },
          { strong: t('coach_growth.flow.stage1_step4_check3_strong'), rest: t('coach_growth.flow.stage1_step4_check3_rest') },
          { strong: t('coach_growth.flow.stage1_step4_check4_strong'), rest: t('coach_growth.flow.stage1_step4_check4_rest') },
          { strong: t('coach_growth.flow.stage1_step4_check5_strong'), rest: t('coach_growth.flow.stage1_step4_check5_rest') },
        ],
      },
      {
        index: t('coach_growth.flow.stage1_step5_index'),
        title: t('coach_growth.flow.stage1_step5_title'),
        tag: t('coach_growth.flow.stage1_step5_tag'),
        body: t('coach_growth.flow.stage1_step5_body'),
        checklist: [
          { strong: '', rest: t('coach_growth.flow.stage1_step5_check1') },
          { strong: '', rest: t('coach_growth.flow.stage1_step5_check2') },
          { strong: '', rest: t('coach_growth.flow.stage1_step5_check3') },
          { strong: '', rest: t('coach_growth.flow.stage1_step5_check4') },
          { strong: '', rest: t('coach_growth.flow.stage1_step5_check5') },
        ],
        rule: {
          label: t('coach_growth.flow.stage1_step5_rule_label'),
          text: t('coach_growth.flow.stage1_step5_rule_text'),
        },
      },
      {
        index: t('coach_growth.flow.stage1_step6_index'),
        title: t('coach_growth.flow.stage1_step6_title'),
        tag: t('coach_growth.flow.stage1_step6_tag'),
        body: '',
        payments: [
          {
            label: t('coach_growth.flow.stage1_step6_payment_a_label'),
            title: t('coach_growth.flow.stage1_step6_payment_a_title'),
            recommended: true,
            desc: t('coach_growth.flow.stage1_step6_payment_a_desc'),
          },
          {
            label: t('coach_growth.flow.stage1_step6_payment_b_label'),
            title: t('coach_growth.flow.stage1_step6_payment_b_title'),
            desc: t('coach_growth.flow.stage1_step6_payment_b_desc'),
          },
        ],
        rule: {
          label: t('coach_growth.flow.stage1_step6_rule_label'),
          text: t('coach_growth.flow.stage1_step6_rule_text'),
        },
      },
    ],
  },
  {
    id: 'onboarding',
    num: '02',
    badge: t('coach_growth.flow.stage2_badge'),
    title: t('coach_growth.flow.stage2_title'),
    objective: t('coach_growth.flow.stage2_objective'),
    accent: 'gold',
    navLabel: t('coach_growth.flow.nav_onboarding'),
    steps: [
      {
        index: t('coach_growth.flow.stage2_step1_index'),
        title: t('coach_growth.flow.stage2_step1_title'),
        tag: t('coach_growth.flow.stage2_step1_tag'),
        body: '',
        checklist: [
          { strong: t('coach_growth.flow.stage2_step1_check1_strong'), rest: t('coach_growth.flow.stage2_step1_check1_rest') },
          { strong: t('coach_growth.flow.stage2_step1_check2_strong'), rest: t('coach_growth.flow.stage2_step1_check2_rest') },
          { strong: t('coach_growth.flow.stage2_step1_check3_strong'), rest: t('coach_growth.flow.stage2_step1_check3_rest') },
        ],
      },
      {
        index: t('coach_growth.flow.stage2_step2_index'),
        title: t('coach_growth.flow.stage2_step2_title'),
        tag: t('coach_growth.flow.stage2_step2_tag'),
        quote: {
          lead: t('coach_growth.flow.stage2_step2_quote_lead'),
          em: t('coach_growth.flow.stage2_step2_quote_em'),
        },
        body: t('coach_growth.flow.stage2_step2_body'),
        checklist: [
          { strong: t('coach_growth.flow.stage2_step2_check1_strong'), rest: t('coach_growth.flow.stage2_step2_check1_rest') },
          { strong: t('coach_growth.flow.stage2_step2_check2_strong'), rest: t('coach_growth.flow.stage2_step2_check2_rest') },
          { strong: t('coach_growth.flow.stage2_step2_check3_strong'), rest: t('coach_growth.flow.stage2_step2_check3_rest') },
          { strong: t('coach_growth.flow.stage2_step2_check4_strong'), rest: t('coach_growth.flow.stage2_step2_check4_rest') },
          { strong: t('coach_growth.flow.stage2_step2_check5_strong'), rest: t('coach_growth.flow.stage2_step2_check5_rest') },
          { strong: t('coach_growth.flow.stage2_step2_check6_strong'), rest: t('coach_growth.flow.stage2_step2_check6_rest') },
        ],
        rule: {
          label: t('coach_growth.flow.stage2_step2_rule_label'),
          text: t('coach_growth.flow.stage2_step2_rule_text'),
        },
      },
      {
        index: t('coach_growth.flow.stage2_step3_index'),
        title: t('coach_growth.flow.stage2_step3_title'),
        tag: t('coach_growth.flow.stage2_step3_tag'),
        body: t('coach_growth.flow.stage2_step3_body'),
        rule: {
          label: t('coach_growth.flow.stage2_step3_rule_label'),
          text: t('coach_growth.flow.stage2_step3_rule_text'),
        },
      },
    ],
  },
  {
    id: 'activacion',
    num: '03',
    badge: t('coach_growth.flow.stage3_badge'),
    title: t('coach_growth.flow.stage3_title'),
    objective: t('coach_growth.flow.stage3_objective'),
    accent: 'blue',
    navLabel: t('coach_growth.flow.nav_activacion'),
    steps: [
      {
        index: t('coach_growth.flow.stage3_step1_index'),
        title: t('coach_growth.flow.stage3_step1_title'),
        tag: t('coach_growth.flow.stage3_step1_tag'),
        quote: {
          lead: t('coach_growth.flow.stage3_step1_quote_lead'),
          em: t('coach_growth.flow.stage3_step1_quote_em'),
        },
        body: t('coach_growth.flow.stage3_step1_body'),
        checklist: [
          { strong: t('coach_growth.flow.stage3_step1_check1_strong'), rest: t('coach_growth.flow.stage3_step1_check1_rest') },
          { strong: t('coach_growth.flow.stage3_step1_check2_strong'), rest: t('coach_growth.flow.stage3_step1_check2_rest') },
          { strong: t('coach_growth.flow.stage3_step1_check3_strong'), rest: t('coach_growth.flow.stage3_step1_check3_rest') },
          { strong: t('coach_growth.flow.stage3_step1_check4_strong'), rest: t('coach_growth.flow.stage3_step1_check4_rest') },
        ],
      },
      {
        index: t('coach_growth.flow.stage3_step2_index'),
        title: t('coach_growth.flow.stage3_step2_title'),
        tag: t('coach_growth.flow.stage3_step2_tag'),
        editorial: t('coach_growth.flow.stage3_step2_editorial'),
        body: t('coach_growth.flow.stage3_step2_body'),
        checklist: [
          { strong: t('coach_growth.flow.stage3_step2_check1_strong'), rest: t('coach_growth.flow.stage3_step2_check1_rest') },
          { strong: t('coach_growth.flow.stage3_step2_check2_strong'), rest: t('coach_growth.flow.stage3_step2_check2_rest') },
          { strong: t('coach_growth.flow.stage3_step2_check3_strong'), rest: t('coach_growth.flow.stage3_step2_check3_rest') },
          { strong: t('coach_growth.flow.stage3_step2_check4_strong'), rest: t('coach_growth.flow.stage3_step2_check4_rest') },
          { strong: t('coach_growth.flow.stage3_step2_check5_strong'), rest: t('coach_growth.flow.stage3_step2_check5_rest') },
        ],
      },
      {
        index: t('coach_growth.flow.stage3_step3_index'),
        title: t('coach_growth.flow.stage3_step3_title'),
        tag: t('coach_growth.flow.stage3_step3_tag'),
        quote: {
          lead: t('coach_growth.flow.stage3_step3_quote_lead'),
          em: t('coach_growth.flow.stage3_step3_quote_em'),
        },
        body: t('coach_growth.flow.stage3_step3_body'),
        checklist: [
          { strong: t('coach_growth.flow.stage3_step3_check1_strong'), rest: t('coach_growth.flow.stage3_step3_check1_rest') },
          { strong: t('coach_growth.flow.stage3_step3_check2_strong'), rest: t('coach_growth.flow.stage3_step3_check2_rest') },
          { strong: t('coach_growth.flow.stage3_step3_check3_strong'), rest: t('coach_growth.flow.stage3_step3_check3_rest') },
          { strong: t('coach_growth.flow.stage3_step3_check4_strong'), rest: t('coach_growth.flow.stage3_step3_check4_rest') },
          { strong: t('coach_growth.flow.stage3_step3_check5_strong'), rest: t('coach_growth.flow.stage3_step3_check5_rest') },
        ],
      },
    ],
  },
  {
    id: 'activo',
    num: '04',
    badge: t('coach_growth.flow.stage4_badge'),
    title: t('coach_growth.flow.stage4_title'),
    objective: t('coach_growth.flow.stage4_objective'),
    accent: 'green',
    navLabel: t('coach_growth.flow.nav_activo'),
    cycles: [
      {
        tag: t('coach_growth.flow.stage4_cycle1_tag'),
        title: t('coach_growth.flow.stage4_cycle1_title'),
        text: t('coach_growth.flow.stage4_cycle1_text'),
        rule: t('coach_growth.flow.stage4_cycle1_rule'),
      },
      {
        tag: t('coach_growth.flow.stage4_cycle2_tag'),
        title: t('coach_growth.flow.stage4_cycle2_title'),
        text: t('coach_growth.flow.stage4_cycle2_text'),
        rule: t('coach_growth.flow.stage4_cycle2_rule'),
      },
      {
        tag: t('coach_growth.flow.stage4_cycle3_tag'),
        title: t('coach_growth.flow.stage4_cycle3_title'),
        text: t('coach_growth.flow.stage4_cycle3_text'),
        rule: t('coach_growth.flow.stage4_cycle3_rule'),
      },
      {
        tag: t('coach_growth.flow.stage4_cycle4_tag'),
        title: t('coach_growth.flow.stage4_cycle4_title'),
        text: t('coach_growth.flow.stage4_cycle4_text'),
        rule: t('coach_growth.flow.stage4_cycle4_rule'),
      },
    ],
    extraRule: {
      label: t('coach_growth.flow.stage4_extra_label'),
      text: t('coach_growth.flow.stage4_extra_text'),
    },
  },
]);

const activeIndex = ref(0);
const activeStage = computed(() => stages.value[activeIndex.value]);
const isFirst = computed(() => activeIndex.value === 0);
const isLast = computed(() => activeIndex.value === stages.value.length - 1);

function setActive(index) {
  if (index === activeIndex.value) return;
  activeIndex.value = index;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function goPrev() { if (!isFirst.value) setActive(activeIndex.value - 1); }
function goNext() { if (!isLast.value) setActive(activeIndex.value + 1); }
</script>

<template>
  <CoachLayout>
    <div class="onboarding-flow space-y-10 pb-16 anim-entry anim-entry-2">

      <!-- ── HERO ── -->
      <header class="hero-block">
        <p class="hero-eyebrow">{{ t('coach_growth.flow.hero_eyebrow') }}</p>
        <h1 class="hero-title">
          {{ t('coach_growth.flow.hero_title_lead') }}<br>
          <em>{{ t('coach_growth.flow.hero_title_em') }}</em>
        </h1>
        <p class="hero-subtitle">
          {{ t('coach_growth.flow.hero_subtitle') }}
        </p>
      </header>

      <!-- ── STAGE NAV ── -->
      <nav class="stage-nav-grid">
        <button
          v-for="(s, idx) in stages"
          :key="s.id"
          @click="setActive(idx)"
          :class="['stage-nav-tab', activeIndex === idx ? `is-active accent-${s.accent}` : '']"
        >
          <span class="stage-nav-num">{{ s.num }}</span>
          <span class="stage-nav-label">{{ s.navLabel }}</span>
        </button>
      </nav>

      <!-- ── ACTIVE STAGE ── -->
      <Transition name="stage-fade" mode="out-in">
        <section :key="activeStage.id" class="stage-section">
          <!-- Stage header -->
          <div class="stage-header">
            <div class="stage-number-bg">{{ activeStage.num }}</div>
            <div class="stage-header-text">
              <span :class="['stage-badge', `accent-${activeStage.accent}`]">{{ activeStage.badge }}</span>
              <h2 class="stage-title">{{ activeStage.title }}</h2>
              <p class="stage-objective"><strong>{{ t('coach_growth.flow.objective_label') }}</strong> {{ activeStage.objective }}</p>
            </div>
          </div>

          <!-- Steps (Etapas 1, 2, 3) -->
          <div v-if="activeStage.steps" class="space-y-3">
            <article
              v-for="(step, idx) in activeStage.steps"
              :key="idx"
              class="step-card"
            >
              <header class="step-card-header">
                <span class="step-index">{{ step.index }}</span>
                <h3 class="step-title">{{ step.title }}</h3>
                <span :class="['step-tag', `accent-${activeStage.accent}`]">{{ step.tag }}</span>
              </header>

              <div class="step-card-body">
                <blockquote v-if="step.quote" class="pull-quote">
                  <p class="pull-quote-text">
                    {{ step.quote.lead }}<br>
                    <em>{{ step.quote.em }}</em>
                  </p>
                </blockquote>

                <p v-if="step.editorial" class="editorial-note">"{{ step.editorial }}"</p>

                <p v-if="step.body" class="step-body-text" v-html="step.body.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')"></p>

                <div v-if="step.payments" class="payment-grid">
                  <div
                    v-for="(p, pi) in step.payments"
                    :key="pi"
                    :class="['payment-option', p.recommended ? 'recommended' : '']"
                  >
                    <p class="payment-option-label">{{ p.label }}</p>
                    <p class="payment-option-title">{{ p.title }}</p>
                    <p class="payment-option-desc">{{ p.desc }}</p>
                  </div>
                </div>

                <div v-if="step.rule" class="rule-block">
                  <p class="rule-block-label">{{ step.rule.label }}</p>
                  <p class="rule-block-text">{{ step.rule.text }}</p>
                </div>

                <ul v-if="step.checklist" class="checklist">
                  <li v-for="(item, ci) in step.checklist" :key="ci">
                    <strong v-if="item.strong">{{ item.strong }}</strong>{{ item.rest }}
                  </li>
                </ul>
              </div>
            </article>
          </div>

          <!-- Cycles (Etapa 4) -->
          <div v-if="activeStage.cycles" class="cycle-grid">
            <article v-for="(c, ci) in activeStage.cycles" :key="ci" class="cycle-card">
              <span :class="['cycle-tag', `accent-${activeStage.accent}`]">{{ c.tag }}</span>
              <h3 class="cycle-title">{{ c.title }}</h3>
              <p class="cycle-text">{{ c.text }}</p>
              <p class="cycle-rule"><strong>·</strong> {{ c.rule }}</p>
            </article>
          </div>

          <!-- Extra rule -->
          <div v-if="activeStage.extraRule" class="rule-block mt-4">
            <p class="rule-block-label">{{ activeStage.extraRule.label }}</p>
            <p class="rule-block-text">{{ activeStage.extraRule.text }}</p>
          </div>

          <!-- Prev / Next nav -->
          <div class="stage-pager">
            <button
              type="button"
              class="pager-btn"
              :disabled="isFirst"
              @click="goPrev"
            >
              <span class="pager-arrow">←</span>
              <span class="pager-text">
                <span class="pager-eyebrow">{{ t('coach_growth.flow.pager_prev') }}</span>
                <span class="pager-label">{{ isFirst ? t('coach_growth.flow.pager_dash') : stages[activeIndex - 1].badge }}</span>
              </span>
            </button>
            <button
              type="button"
              class="pager-btn pager-btn-next"
              :disabled="isLast"
              @click="goNext"
            >
              <span class="pager-text pager-text-right">
                <span class="pager-eyebrow">{{ t('coach_growth.flow.pager_next') }}</span>
                <span class="pager-label">{{ isLast ? t('coach_growth.flow.pager_dash') : stages[activeIndex + 1].badge }}</span>
              </span>
              <span class="pager-arrow">→</span>
            </button>
          </div>
        </section>
      </Transition>

      <!-- ── SUMMARY ── -->
      <div class="summary-divider">
        <span>{{ t('coach_growth.flow.summary_title') }}</span>
      </div>

      <div class="summary-table">
        <header class="summary-header">
          <p>{{ t('coach_growth.flow.summary_subtitle') }}</p>
        </header>
        <div class="summary-row">
          <div class="summary-stage accent-red">{{ t('coach_growth.flow.summary_preventa') }}</div>
          <div class="summary-pills">
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_01_contenido') }}</span>
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_02_contacto') }}</span>
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_03_diagnostico') }}</span>
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_04_solucion') }}</span>
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_05_oferta') }}</span>
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_06_pago') }}</span>
          </div>
          <div class="summary-count">6</div>
        </div>
        <div class="summary-row">
          <div class="summary-stage accent-gold">{{ t('coach_growth.flow.summary_onboarding') }}</div>
          <div class="summary-pills">
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_acceso') }}</span>
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_entrevista') }}</span>
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_ticket') }}</span>
          </div>
          <div class="summary-count">3</div>
        </div>
        <div class="summary-row">
          <div class="summary-stage accent-blue">{{ t('coach_growth.flow.summary_activacion') }}</div>
          <div class="summary-pills">
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_presentar') }}</span>
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_orientar') }}</span>
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_primera_semana') }}</span>
          </div>
          <div class="summary-count">3</div>
        </div>
        <div class="summary-row">
          <div class="summary-stage accent-green">{{ t('coach_growth.flow.summary_activo') }}</div>
          <div class="summary-pills">
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_checkin') }}</span>
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_revision') }}</span>
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_chat') }}</span>
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_renovacion') }}</span>
            <span class="summary-pill">{{ t('coach_growth.flow.summary_pill_referidos') }}</span>
          </div>
          <div class="summary-count">∞</div>
        </div>
      </div>

      <!-- ── POWERED BY ── -->
      <div class="powered-bar">
        <span>{{ t('coach_growth.flow.powered_word') }}</span><span class="dot"></span>
        <span>{{ t('coach_growth.flow.powered_by') }}</span><span class="dot"></span>
        <span class="brand">{{ t('coach_growth.flow.powered_brand') }}</span><span class="dot"></span>
        <span>{{ t('coach_growth.flow.powered_tagline') }}</span>
      </div>

    </div>
  </CoachLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;1,300;1,400;1,500&display=swap');

.onboarding-flow {
  --of-red: #DC2626;
  --of-red-dim: rgba(220,38,38,0.10);
  --of-gold: #D4A80E;
  --of-blue: #38BDF8;
  --of-green: #34D399;
  --font-edit: 'Fraunces', Georgia, serif;
  max-width: 960px;
  margin: 0 auto;
}

/* ── HERO ── */
.hero-block {
  padding-bottom: 28px;
  border-bottom: 1px solid var(--color-wc-border);
}
.hero-eyebrow {
  font-family: var(--wc-font-display, inherit);
  font-size: 10px;
  letter-spacing: 3px;
  color: var(--of-red);
  text-transform: uppercase;
  margin-bottom: 14px;
}
.hero-title {
  font-family: var(--wc-font-display, 'Oswald', Impact, sans-serif);
  font-size: clamp(34px, 6vw, 64px);
  font-weight: 700;
  letter-spacing: 2px;
  line-height: 1.0;
  color: var(--color-wc-text);
  text-transform: uppercase;
  margin-bottom: 16px;
}
.hero-title em {
  color: var(--of-red);
  font-style: normal;
}
.hero-subtitle {
  font-size: 15px;
  line-height: 1.7;
  color: var(--color-wc-text-secondary);
  max-width: 600px;
}

/* ── STAGE NAV ── */
.stage-nav-grid {
  position: sticky;
  top: 64px;
  z-index: 20;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  background: var(--color-wc-bg-secondary);
  border: 1px solid var(--color-wc-border);
  border-radius: 14px;
  overflow: hidden;
  backdrop-filter: blur(10px);
}
.stage-nav-tab {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 10px;
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 11px;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
  background: transparent;
  border: none;
  border-right: 1px solid var(--color-wc-border);
  cursor: pointer;
  transition: color .2s, background .2s;
}
.stage-nav-tab:last-child { border-right: none; }
.stage-nav-tab:hover { color: var(--color-wc-text); }
.stage-nav-num {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 22px; height: 22px;
  border-radius: 6px;
  font-size: 10px;
  font-weight: 700;
  background: var(--color-wc-bg-tertiary);
  color: var(--color-wc-text-tertiary);
  flex-shrink: 0;
}
.stage-nav-tab.is-active.accent-red { color: var(--of-red); background: var(--of-red-dim); }
.stage-nav-tab.is-active.accent-red .stage-nav-num { background: rgba(220,38,38,0.18); color: var(--of-red); }
.stage-nav-tab.is-active.accent-gold { color: var(--of-gold); background: rgba(212,168,14,0.10); }
.stage-nav-tab.is-active.accent-gold .stage-nav-num { background: rgba(212,168,14,0.20); color: var(--of-gold); }
.stage-nav-tab.is-active.accent-blue { color: var(--of-blue); background: rgba(56,189,248,0.10); }
.stage-nav-tab.is-active.accent-blue .stage-nav-num { background: rgba(56,189,248,0.20); color: var(--of-blue); }
.stage-nav-tab.is-active.accent-green { color: var(--of-green); background: rgba(52,211,153,0.10); }
.stage-nav-tab.is-active.accent-green .stage-nav-num { background: rgba(52,211,153,0.20); color: var(--of-green); }

/* ── STAGE SECTION ── */
.stage-section {
  scroll-margin-top: 120px;
}
.stage-header {
  display: flex;
  align-items: flex-end;
  gap: 24px;
  margin-bottom: 28px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--color-wc-border);
  position: relative;
}
.stage-number-bg {
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: clamp(60px, 10vw, 110px);
  font-weight: 700;
  line-height: 0.85;
  color: rgba(255,255,255,0.04);
  letter-spacing: -2px;
  flex-shrink: 0;
}
.stage-header-text { flex: 1; min-width: 0; }
.stage-badge {
  display: inline-block;
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 10px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  padding: 4px 10px;
  border-radius: 999px;
  margin-bottom: 12px;
}
.stage-badge.accent-red { color: var(--of-red); background: var(--of-red-dim); border: 1px solid rgba(220,38,38,0.30); }
.stage-badge.accent-gold { color: var(--of-gold); background: rgba(212,168,14,0.10); border: 1px solid rgba(212,168,14,0.30); }
.stage-badge.accent-blue { color: var(--of-blue); background: rgba(56,189,248,0.10); border: 1px solid rgba(56,189,248,0.30); }
.stage-badge.accent-green { color: var(--of-green); background: rgba(52,211,153,0.10); border: 1px solid rgba(52,211,153,0.30); }
.stage-title {
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: clamp(24px, 4vw, 36px);
  font-weight: 700;
  letter-spacing: 1px;
  line-height: 1.05;
  text-transform: uppercase;
  color: var(--color-wc-text);
  margin-bottom: 10px;
}
.stage-objective {
  font-size: 14px;
  line-height: 1.6;
  color: var(--color-wc-text-secondary);
}
.stage-objective strong { color: var(--color-wc-text); }

/* ── STEP CARD ── */
.step-card {
  border: 1px solid var(--b1, var(--color-wc-border));
  background: var(--s2, var(--color-wc-bg-secondary));
  border-radius: 14px;
  overflow: hidden;
  box-shadow: var(--shadow-card-ios);
}
.step-card-header {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
  padding: 14px 18px;
  border-bottom: 1px solid var(--color-wc-border);
  background: rgba(255,255,255,0.015);
}
.step-index {
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 10px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
}
.step-title {
  flex: 1;
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 16px;
  font-weight: 600;
  letter-spacing: .5px;
  text-transform: uppercase;
  color: var(--color-wc-text);
  min-width: 200px;
}
.step-tag {
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 9px;
  letter-spacing: 1px;
  text-transform: uppercase;
  padding: 3px 9px;
  border-radius: 999px;
  margin-left: auto;
}
.step-tag.accent-red { color: var(--of-red); background: var(--of-red-dim); }
.step-tag.accent-gold { color: var(--of-gold); background: rgba(212,168,14,0.10); }
.step-tag.accent-blue { color: var(--of-blue); background: rgba(56,189,248,0.10); }
.step-tag.accent-green { color: var(--of-green); background: rgba(52,211,153,0.10); }

.step-card-body { padding: 18px; }
.step-body-text {
  font-size: 14px;
  line-height: 1.75;
  color: var(--color-wc-text-secondary);
  margin-bottom: 14px;
}
.step-body-text strong { color: var(--color-wc-text); font-weight: 600; }

/* ── PULL QUOTE ── */
.pull-quote {
  border-left: 3px solid var(--of-red);
  background: var(--of-red-dim);
  padding: 14px 16px;
  border-radius: 0 10px 10px 0;
  margin-bottom: 14px;
}
.pull-quote-text {
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 18px;
  letter-spacing: .3px;
  line-height: 1.3;
  color: var(--color-wc-text);
  text-transform: uppercase;
  font-weight: 700;
}
.pull-quote-text em {
  font-family: var(--font-edit);
  font-style: italic;
  font-weight: 400;
  text-transform: none;
  letter-spacing: 0;
  color: var(--of-red);
}

/* ── EDITORIAL NOTE ── */
.editorial-note {
  font-family: var(--font-edit);
  font-style: italic;
  font-size: 15px;
  line-height: 1.7;
  color: var(--of-gold);
  opacity: .95;
  margin-bottom: 14px;
  padding-left: 12px;
  border-left: 2px solid rgba(212,168,14,0.30);
}

/* ── RULE BLOCK ── */
.rule-block {
  background: rgba(255,255,255,0.02);
  border: 1px solid var(--color-wc-border);
  border-radius: 10px;
  padding: 12px 14px;
  margin-bottom: 14px;
}
.rule-block-label {
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 9px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--of-red);
  margin-bottom: 6px;
}
.rule-block-text {
  font-size: 13px;
  line-height: 1.65;
  color: var(--color-wc-text-secondary);
}

/* ── CHECKLIST ── */
.checklist { list-style: none; padding: 0; margin: 0; }
.checklist li {
  position: relative;
  padding: 10px 0 10px 22px;
  font-size: 14px;
  line-height: 1.65;
  color: var(--color-wc-text-secondary);
  border-bottom: 1px solid rgba(255,255,255,0.04);
}
.checklist li:last-child { border-bottom: none; }
.checklist li::before {
  content: '';
  position: absolute;
  left: 0;
  top: 18px;
  width: 10px;
  height: 1px;
  background: var(--of-red);
}
.checklist li strong { color: var(--color-wc-text); font-weight: 600; }

/* ── PAYMENTS ── */
.payment-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-bottom: 14px;
}
.payment-option {
  background: rgba(255,255,255,0.02);
  border: 1px solid var(--color-wc-border);
  border-radius: 10px;
  padding: 14px;
}
.payment-option.recommended {
  border-color: rgba(220,38,38,0.40);
  background: var(--of-red-dim);
}
.payment-option-label {
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 9px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
  margin-bottom: 6px;
}
.payment-option.recommended .payment-option-label { color: var(--of-red); }
.payment-option-title {
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 16px;
  font-weight: 600;
  letter-spacing: .5px;
  color: var(--color-wc-text);
  text-transform: uppercase;
  margin-bottom: 6px;
}
.payment-option-desc {
  font-size: 13px;
  line-height: 1.6;
  color: var(--color-wc-text-secondary);
}

/* ── CYCLE GRID ── */
.cycle-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.cycle-card {
  border: 1px solid var(--b1, var(--color-wc-border));
  background: var(--s2, var(--color-wc-bg-secondary));
  border-radius: 14px;
  padding: 18px;
  box-shadow: var(--shadow-card-ios);
}
.cycle-tag {
  display: inline-block;
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 9px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  padding: 3px 9px;
  border-radius: 999px;
  margin-bottom: 10px;
}
.cycle-tag.accent-green { color: var(--of-green); background: rgba(52,211,153,0.10); }
.cycle-title {
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 18px;
  font-weight: 700;
  letter-spacing: .5px;
  text-transform: uppercase;
  color: var(--color-wc-text);
  margin-bottom: 10px;
}
.cycle-text {
  font-size: 13.5px;
  line-height: 1.7;
  color: var(--color-wc-text-secondary);
  margin-bottom: 12px;
}
.cycle-rule {
  font-size: 13px;
  line-height: 1.6;
  color: var(--color-wc-text);
  padding-top: 10px;
  border-top: 1px solid var(--color-wc-border);
}
.cycle-rule strong { color: var(--of-red); margin-right: 4px; }

/* ── SUMMARY ── */
.summary-divider {
  display: flex;
  align-items: center;
  gap: 16px;
  margin: 32px 0 20px;
}
.summary-divider::before, .summary-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--color-wc-border);
}
.summary-divider span {
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 11px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
}
.summary-table {
  border: 1px solid var(--b1, var(--color-wc-border));
  border-radius: 14px;
  overflow: hidden;
  background: var(--s2, var(--color-wc-bg-secondary));
  box-shadow: var(--shadow-card-ios);
}
.summary-header {
  padding: 14px 18px;
  background: rgba(255,255,255,0.02);
  border-bottom: 1px solid var(--color-wc-border);
}
.summary-header p {
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 12px;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  color: var(--color-wc-text);
}
.summary-row {
  display: grid;
  grid-template-columns: 140px 1fr 60px;
  align-items: center;
  border-bottom: 1px solid var(--color-wc-border);
}
.summary-row:last-child { border-bottom: none; }
.summary-stage {
  padding: 14px 16px;
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 13px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  font-weight: 600;
  border-right: 1px solid var(--color-wc-border);
}
.summary-stage.accent-red { color: var(--of-red); }
.summary-stage.accent-gold { color: var(--of-gold); }
.summary-stage.accent-blue { color: var(--of-blue); }
.summary-stage.accent-green { color: var(--of-green); }
.summary-pills {
  padding: 14px 16px;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.summary-pill {
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 10px;
  letter-spacing: .8px;
  text-transform: uppercase;
  padding: 4px 10px;
  border-radius: 999px;
  background: var(--color-wc-bg-tertiary);
  color: var(--color-wc-text-secondary);
  border: 1px solid var(--color-wc-border);
}
.summary-count {
  text-align: center;
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 22px;
  font-weight: 700;
  color: var(--color-wc-text);
  border-left: 1px solid var(--color-wc-border);
  padding: 14px 8px;
}

/* ── POWERED BAR ── */
.powered-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-wrap: wrap;
  gap: 8px 12px;
  padding: 18px 14px;
  border: 1px solid var(--b1, var(--color-wc-border));
  border-radius: 14px;
  background: var(--s2, var(--color-wc-bg-secondary));
  box-shadow: var(--shadow-card-ios);
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 10px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
}
.powered-bar .dot {
  width: 3px;
  height: 3px;
  background: var(--color-wc-text-tertiary);
  border-radius: 50%;
  opacity: .5;
}
.powered-bar .brand { color: var(--of-red); font-weight: 700; }

/* ── STAGE PAGER (Prev / Next) ── */
.stage-pager {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-top: 28px;
  padding-top: 24px;
  border-top: 1px solid var(--color-wc-border);
}
.pager-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 18px;
  background: var(--color-wc-bg-secondary);
  border: 1px solid var(--color-wc-border);
  border-radius: 12px;
  color: var(--color-wc-text);
  cursor: pointer;
  transition: border-color .18s, background .18s, transform .18s;
  text-align: left;
  font-family: inherit;
}
.pager-btn:hover:not(:disabled) {
  border-color: var(--of-red);
  background: rgba(220,38,38,0.06);
}
.pager-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}
.pager-btn-next { justify-content: flex-end; text-align: right; }
.pager-arrow {
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 22px;
  font-weight: 600;
  color: var(--of-red);
  flex-shrink: 0;
  line-height: 1;
}
.pager-btn:disabled .pager-arrow { color: var(--color-wc-text-tertiary); }
.pager-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}
.pager-text-right { align-items: flex-end; }
.pager-eyebrow {
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 9px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--color-wc-text-tertiary);
}
.pager-label {
  font-family: var(--wc-font-display, 'Oswald', sans-serif);
  font-size: 12px;
  letter-spacing: 0.6px;
  text-transform: uppercase;
  font-weight: 600;
  color: var(--color-wc-text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

/* ── STAGE FADE TRANSITION ── */
.stage-fade-enter-active,
.stage-fade-leave-active {
  transition: opacity .28s ease, transform .28s ease;
}
.stage-fade-enter-from {
  opacity: 0;
  transform: translateY(12px);
}
.stage-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* ── MOBILE ── */
@media (max-width: 640px) {
  .stage-nav-grid {
    grid-template-columns: 1fr 1fr;
  }
  .stage-nav-tab {
    border-bottom: 1px solid var(--color-wc-border);
    padding: 10px 8px;
    font-size: 10px;
    letter-spacing: .8px;
  }
  .stage-nav-tab:nth-child(2),
  .stage-nav-tab:nth-child(4) { border-right: none; }
  .stage-nav-tab:nth-child(3),
  .stage-nav-tab:nth-child(4) { border-bottom: none; }

  .stage-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0;
  }
  .stage-number-bg {
    font-size: 60px;
    line-height: 1;
    margin-bottom: -8px;
  }

  .step-card-header {
    padding: 12px 14px;
    gap: 8px;
  }
  .step-index { font-size: 9px; flex-basis: 100%; }
  .step-title { font-size: 14px; min-width: 0; flex: 1; }
  .step-tag { margin-left: 0; }
  .step-card-body { padding: 14px; }

  .pull-quote-text { font-size: 16px; }
  .editorial-note { font-size: 14px; }

  .payment-grid { grid-template-columns: 1fr; }
  .cycle-grid { grid-template-columns: 1fr; }

  .summary-row {
    grid-template-columns: 1fr;
  }
  .summary-stage {
    border-right: none;
    border-bottom: 1px solid var(--color-wc-border);
    padding: 10px 14px;
  }
  .summary-pills {
    padding: 12px 14px;
  }
  .summary-count {
    border-left: none;
    border-top: 1px solid var(--color-wc-border);
    padding: 10px;
    font-size: 18px;
  }

  .checklist li { padding: 10px 0 10px 18px; font-size: 13.5px; }
  .checklist li::before { top: 16px; }

  .powered-bar { font-size: 9px; letter-spacing: 1.5px; }

  .stage-pager { gap: 8px; margin-top: 22px; padding-top: 18px; }
  .pager-btn { padding: 12px 14px; gap: 10px; }
  .pager-arrow { font-size: 20px; }
  .pager-eyebrow { font-size: 8px; letter-spacing: 1.2px; }
  .pager-label { font-size: 10px; letter-spacing: 0.4px; }
}
</style>
