<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import CoachLayout from '../../layouts/CoachLayout.vue';

const stages = [
  {
    id: 'preventa',
    num: '01',
    badge: 'Etapa 1 · Preventa',
    title: 'Atraer, conectar y convertir.',
    objective: 'Que el prospecto llegue al DM, sienta que lo entiendes y quiera tu servicio — sin que tengas que presionarlo.',
    accent: 'red',
    steps: [
      {
        index: 'Paso 01', title: 'Contenido con intención', tag: 'Redes',
        quote: { lead: 'El contenido educa.', em: 'El DM convierte.' },
        body: 'Publica contenido de valor — educativo, motivacional, resultados de clientes — con un CTA claro que dirija al DM. No vendes directamente en la publicación. Posicionas. Generas la curiosidad suficiente para que el prospecto dé el primer paso.',
        rule: { label: 'Regla de oro', text: 'Todo post tiene un propósito único. Si no sabes exactamente qué quieres que haga el que lo ve al terminar de leerlo, el post está incompleto.' },
        checklist: [
          { strong: 'Tip de entrenamiento o nutrición', rest: ' — dato verificable, no motivación genérica' },
          { strong: 'Resultado de cliente', rest: ' — con nombre (con permiso) y contexto real' },
          { strong: 'BTS del trabajo del coach', rest: ' — día típico, preparación de plan, consulta' },
          { strong: 'CTA único al final', rest: ' — "Escríbeme", "Manda DM", "Pregúntame cómo"' },
        ],
      },
      {
        index: 'Paso 02', title: 'Primer contacto humano', tag: 'DM',
        editorial: 'Cuando alguien llega al DM, lo primero que hago es conocerle. No vendo todavía. Primero escucho.',
        body: 'Cuando alguien llega al DM — o tú inicias la conversación — la prioridad es **conectar antes de presentar el servicio.** El objetivo en este punto no es hablar de lo que ofreces. Es conocer a la persona, mostrar interés genuino y generar confianza.',
        rule: { label: 'Apertura sugerida', text: '"Hola, ¿cómo has estado? Vi que le dabas like a mis publicaciones sobre entrenamiento, ¿hace cuánto llevas activo en el gym?"' },
        checklist: [
          { strong: 'Habla a la persona', rest: ', no sobre el servicio' },
          { strong: 'Usa su nombre', rest: ' desde el primer mensaje' },
          { strong: 'Haz una pregunta abierta', rest: ' — que ella/él hable primero' },
          { strong: 'No lances el precio', rest: ' ni la oferta en el primer contacto' },
        ],
      },
      {
        index: 'Paso 03', title: 'Diagnóstico de situación', tag: 'Escucha',
        quote: { lead: 'El que más habla aquí es el prospecto,', em: 'no el coach.' },
        body: 'Haz preguntas estratégicas para entender el punto de partida real. La persona habla — tú escuchas para identificar el dolor específico. Sin diagnóstico real, cualquier presentación del servicio suena genérica.',
        checklist: [
          { strong: '', rest: '¿Hace cuánto tiempo entrenas o haces ejercicio?' },
          { strong: '', rest: '¿Qué has intentado antes para mejorar tu físico o tu salud?' },
          { strong: '', rest: '¿Qué resultados esperabas y qué pasó realmente?' },
          { strong: '', rest: '¿Qué es lo que más te frustra de tu proceso actual?' },
          { strong: '', rest: '¿Qué aspiras cambiar o lograr en los próximos meses?' },
        ],
        rule: { label: 'Nunca', text: 'No interrumpas con la oferta antes de tener el panorama completo. Si ya sabes el problema antes de que termine de hablar, esperas igual.' },
      },
      {
        index: 'Paso 04', title: 'Presentación de solución', tag: 'Propuesta',
        editorial: 'No presento el servicio de WellCore. Le cuento qué puedo hacer por ella específicamente, con lo que me acaba de decir.',
        body: 'Con base en lo que escuchaste, conecta cada ventaja del servicio directamente con los dolores que el prospecto ya expresó. No un guión genérico — una respuesta personalizada a lo que acabas de escuchar.',
        checklist: [
          { strong: 'Seguimiento 1 a 1', rest: ' — chat directo contigo, sin intermediarios ni bots' },
          { strong: 'Plan 100% personalizado', rest: ' — funciona desde principiante hasta avanzado' },
          { strong: 'Nutrición adaptada a tu vida real', rest: ' — sin regímenes imposibles ni prohibiciones extremas' },
          { strong: 'Revisión mensual', rest: ' — el plan evoluciona contigo, no es estático' },
          { strong: 'Siempre hay una persona detrás', rest: ' — tú, respondiendo con criterio, no respuestas automáticas' },
        ],
      },
      {
        index: 'Paso 05', title: 'Envío de oferta personalizada', tag: 'HTML · Oferta',
        body: 'Si el prospecto muestra interés, envías el **HTML de propuesta oficial WellCore**. Un documento visual que el cliente abre y lee como una presentación completa.',
        checklist: [
          { strong: '', rest: 'Qué es WellCore y qué va a obtener el cliente' },
          { strong: '', rest: 'Tu presentación en primera persona — experiencia, estilo de trabajo, qué te diferencia' },
          { strong: '', rest: 'Descripción del acompañamiento: "seguimiento directo conmigo, chequeo mensual..."' },
          { strong: '', rest: 'Los planes disponibles (Esencial / Método / Elite) con diferencias y precios' },
          { strong: '', rest: 'El siguiente paso claro: qué hacer si quiere inscribirse' },
        ],
        rule: { label: 'Voz de la oferta', text: 'Primera persona del coach en todo el documento. WellCore aparece solo en el footer como respaldo. La presentación es tuya — WellCore es la infraestructura.' },
      },
      {
        index: 'Paso 06', title: 'Cierre y pago', tag: 'Pago',
        body: '',
        payments: [
          { label: 'Opción A · Recomendada', title: 'Pago por Wompi', recommended: true,
            desc: 'Compartes tu link de referido personal. El sistema registra el pago automáticamente y activa el proceso de inscripción de inmediato. Sin fricciones.' },
          { label: 'Opción B · Manual', title: 'Pago externo',
            desc: 'Nequi, transferencia o cuenta bancaria. El cliente paga — tú solicitas el comprobante y lo subes al portal WellCore. El equipo valida y genera el acceso en máx. 30 min.' },
        ],
        rule: { label: 'Regla sin excepción', text: 'Nunca dar acceso sin tener el comprobante registrado en el sistema. Protege al coach y al cliente por igual.' },
      },
    ],
  },
  {
    id: 'onboarding',
    num: '02',
    badge: 'Etapa 2 · Onboarding',
    title: 'Acceso, entrevista y carga del ticket.',
    objective: 'Vincular al cliente a la plataforma y recolectar toda la información necesaria para construir su plan.',
    accent: 'gold',
    steps: [
      {
        index: 'Paso 01', title: 'Acceso a la plataforma', tag: 'Sistema',
        body: '',
        checklist: [
          { strong: 'Pago por Wompi:', rest: ' el cliente recibe su enlace de inscripción de forma automática e inmediata. Se registra solo.' },
          { strong: 'Pago externo:', rest: ' subes el comprobante al portal → el equipo lo valida → envías el enlace al cliente. Plazo: máx. 30 minutos.' },
          { strong: 'Confirma siempre', rest: ' que el cliente pudo acceder correctamente antes de continuar.' },
        ],
      },
      {
        index: 'Paso 02', title: 'Entrevista de perfil', tag: 'WhatsApp · Videollamada',
        quote: { lead: 'El propósito no es llenar un formulario.', em: 'Es entender a la persona.' },
        body: 'Realizas la entrevista oficial con el cliente — por WhatsApp o videollamada — usando el cuestionario profesional WellCore. La calidad del plan depende directamente de la calidad de la información que recolectas aquí.',
        checklist: [
          { strong: 'Datos personales', rest: ' — nombre, edad, género, peso, estatura, nivel de actividad' },
          { strong: 'Entrenamiento', rest: ' — lugar, nivel, disponibilidad horaria, lesiones, restricciones, split deseado' },
          { strong: 'Nutrición', rest: ' — hábitos actuales, horarios, preferencias, restricciones alimentarias' },
          { strong: 'Hábitos y estilo de vida', rest: ' — sueño, estrés, rutina matutina y nocturna' },
          { strong: 'Suplementación', rest: ' — qué toma actualmente, objetivo del stack' },
          { strong: 'Objetivo real', rest: ' — motivación de fondo, no solo el físico' },
        ],
        rule: { label: 'No apresurarse', text: 'Esta conversación es el fundamento del plan. Si la saltas o la acortas, el plan lo refleja — y el cliente lo nota en la primera semana.' },
      },
      {
        index: 'Paso 03', title: 'Carga del ticket en el portal', tag: 'Portal',
        body: 'Con toda la información de la entrevista, llenas el ticket de plan en tu portal WellCore y lo envías al equipo para su elaboración. Una vez enviado, le comunicás al cliente:',
        rule: { label: 'Mensaje al cliente tras enviar el ticket', text: '"Ya subí toda tu información a la plataforma. En un máximo de 48 horas recibirás tu plan personalizado completo con todas las indicaciones. Cualquier duda antes de eso, aquí estoy contigo."' },
      },
    ],
  },
  {
    id: 'activacion',
    num: '03',
    badge: 'Etapa 3 · Activación',
    title: 'Entregar, orientar y activar.',
    objective: 'Presentar el plan, orientar al cliente en el portal y garantizar una primera semana de arranque exitosa.',
    accent: 'blue',
    steps: [
      {
        index: 'Paso 01', title: 'Presentación del plan personalizado', tag: 'Entrega',
        quote: { lead: 'El plan nunca', em: 'se entrega en frío.' },
        body: 'Cuando WellCore sube el plan, le notificas al cliente y haces una presentación directa — mensaje de voz, videollamada corta o mensaje escrito. El cliente que no entiende su plan no lo sigue. El coach que lo presenta genera compromiso desde el día uno.',
        checklist: [
          { strong: 'Qué contiene', rest: ' su plan — entrenamiento, nutrición, hábitos, suplementación' },
          { strong: 'Por qué se diseñó así', rest: ' para él/ella específicamente' },
          { strong: 'Qué puede esperar', rest: ' en las primeras 2 a 4 semanas' },
          { strong: 'Cuál es el punto de enfoque', rest: ' principal al comenzar' },
        ],
      },
      {
        index: 'Paso 02', title: 'Orientación del portal', tag: 'Plataforma',
        editorial: 'El cliente que entiende y usa el portal se mantiene activo. El que no lo entiende, desaparece. Esta orientación es tan importante como el plan mismo.',
        body: 'Haces un recorrido rápido por la plataforma para que el cliente sepa exactamente dónde está todo y cómo usarla desde el primer día.',
        checklist: [
          { strong: 'Plan de entrenamiento y nutrición', rest: ' — dónde encontrarlos' },
          { strong: 'Check-in semanal', rest: ' — cómo registrar peso, fotos y sensaciones' },
          { strong: 'Chat directo contigo', rest: ' — para hablarte cuando lo necesite' },
          { strong: 'Seguimiento mes a mes', rest: ' — qué esperar' },
          { strong: 'Ajustes al plan', rest: ' — cómo se comunican y se interpretan' },
        ],
      },
      {
        index: 'Paso 03', title: 'Primera semana de activación', tag: 'Seguimiento',
        quote: { lead: 'Esta semana define el tono', em: 'de toda la relación.' },
        body: 'Durante los primeros 7 días haces seguimiento activo y directo. Un cliente bien acompañado en la primera semana es un cliente que renueva.',
        checklist: [
          { strong: 'Confirmar acceso al portal', rest: ' y que vio su plan' },
          { strong: 'Preguntar por el primer entrenamiento', rest: ' — cómo se sintió' },
          { strong: 'Resolver dudas iniciales', rest: ' sobre ejercicios, comidas o suplementos' },
          { strong: 'Celebrar el primer logro', rest: ' — por pequeño que sea' },
          { strong: 'Reforzar el vínculo', rest: ' y el compromiso a largo plazo' },
        ],
      },
    ],
  },
  {
    id: 'activo',
    num: '04',
    badge: 'Etapa 4 · Servicio Activo',
    title: 'Sostener, ajustar y fidelizar.',
    objective: 'Mantener el progreso, ajustar continuamente y convertir al cliente en alguien que permanece y refiere.',
    accent: 'green',
    cycles: [
      { tag: 'Ciclo semanal', title: 'Check-in + Respuesta',
        text: 'El cliente registra su check-in semanal en la plataforma — peso, foto, cómo se sintió, qué dificultades tuvo. Tú revisas y respondes con indicaciones específicas, ajustes o motivación.',
        rule: 'Un check-in sin respuesta del coach es un cliente que empieza a desconectarse.' },
      { tag: 'Ciclo mensual', title: 'Revisión y ajuste',
        text: 'Evaluás si el plan sigue siendo el adecuado o si necesita cambios en entrenamiento, nutrición o hábitos. Comunicas los cambios con claridad y explicas el porqué.',
        rule: 'Este es el momento en que el cliente siente que el servicio es vivo y evoluciona con él.' },
      { tag: 'Comunicación', title: 'Chat directo',
        text: 'El cliente puede escribirte en cualquier momento por el chat de la plataforma. Respondés con agilidad, especialmente ante dudas urgentes sobre ejercicios o alimentación.',
        rule: 'Tono siempre cercano, profesional y motivador.' },
      { tag: 'Renovación', title: 'Anticipar y proponer',
        text: 'Antes de que venza el mes, inicias tú la conversación de renovación. Muestras los resultados obtenidos y propones continuar — o hacer un upgrade de plan.',
        rule: 'Un cliente satisfecho que renueva y refiere es el mejor activo que puedes tener.' },
    ],
    extraRule: { label: 'Referidos', text: 'Tu link de referido personal en WellCore vincula automáticamente a cada nuevo cliente que llegue a través tuyo. Reconoce y agradece cada referido — genera comunidad y lealtad alrededor de tu perfil.' },
  },
];

const activeStage = ref('preventa');
const stageRefs = ref({});
const navRef = ref(null);

const accentMap = {
  red: { text: 'text-wc-accent', bg: 'bg-wc-accent/10', ring: 'ring-wc-accent/30', border: 'border-wc-accent/40', dot: 'bg-wc-accent' },
  gold: { text: 'text-amber-400', bg: 'bg-amber-400/10', ring: 'ring-amber-400/30', border: 'border-amber-400/40', dot: 'bg-amber-400' },
  blue: { text: 'text-sky-400', bg: 'bg-sky-400/10', ring: 'ring-sky-400/30', border: 'border-sky-400/40', dot: 'bg-sky-400' },
  green: { text: 'text-emerald-400', bg: 'bg-emerald-400/10', ring: 'ring-emerald-400/30', border: 'border-emerald-400/40', dot: 'bg-emerald-400' },
};

function accentOf(stage) { return accentMap[stage.accent]; }

function jumpTo(id) {
  const el = stageRefs.value[id];
  if (!el) return;
  const top = el.getBoundingClientRect().top + window.scrollY - 110;
  window.scrollTo({ top, behavior: 'smooth' });
}

let observer = null;
onMounted(() => {
  nextTick(() => {
    observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) activeStage.value = entry.target.dataset.stageId;
      });
    }, { threshold: 0.18, rootMargin: '-80px 0px -55% 0px' });

    Object.values(stageRefs.value).forEach(el => el && observer.observe(el));
  });
});

onBeforeUnmount(() => {
  if (observer) observer.disconnect();
});
</script>

<template>
  <CoachLayout>
    <div class="onboarding-flow space-y-10 pb-16">

      <!-- ── HERO ── -->
      <header class="hero-block">
        <p class="hero-eyebrow">Manual Operativo · Coaches WellCore</p>
        <h1 class="hero-title">
          Nuestro sistema<br>
          <em>4 etapas.</em>
        </h1>
        <p class="hero-subtitle">
          Este onboarding te explica todo paso a paso — desde el primer contacto
          con un prospecto hasta el cliente que lleva meses contigo y te recomienda.
        </p>
      </header>

      <!-- ── STAGE NAV ── -->
      <nav ref="navRef" class="stage-nav-grid">
        <button
          v-for="s in stages"
          :key="s.id"
          @click="jumpTo(s.id)"
          :class="['stage-nav-tab', activeStage === s.id ? `is-active accent-${s.accent}` : '']"
        >
          <span class="stage-nav-num">{{ s.num }}</span>
          <span class="stage-nav-label">{{ s.id === 'activo' ? 'Servicio Activo' : (s.id.charAt(0).toUpperCase() + s.id.slice(1)) }}</span>
        </button>
      </nav>

      <!-- ── STAGES ── -->
      <section
        v-for="stage in stages"
        :key="stage.id"
        :ref="el => stageRefs[stage.id] = el"
        :data-stage-id="stage.id"
        class="stage-section"
      >
        <!-- Stage header -->
        <div class="stage-header">
          <div class="stage-number-bg">{{ stage.num }}</div>
          <div class="stage-header-text">
            <span :class="['stage-badge', `accent-${stage.accent}`]">{{ stage.badge }}</span>
            <h2 class="stage-title">{{ stage.title }}</h2>
            <p class="stage-objective"><strong>Objetivo:</strong> {{ stage.objective }}</p>
          </div>
        </div>

        <!-- Steps (Etapas 1, 2, 3) -->
        <div v-if="stage.steps" class="space-y-3">
          <article
            v-for="(step, idx) in stage.steps"
            :key="idx"
            class="step-card"
          >
            <header class="step-card-header">
              <span class="step-index">{{ step.index }}</span>
              <h3 class="step-title">{{ step.title }}</h3>
              <span :class="['step-tag', `accent-${stage.accent}`]">{{ step.tag }}</span>
            </header>

            <div class="step-card-body">
              <!-- Pull-quote -->
              <blockquote v-if="step.quote" class="pull-quote">
                <p class="pull-quote-text">
                  {{ step.quote.lead }}<br>
                  <em>{{ step.quote.em }}</em>
                </p>
              </blockquote>

              <!-- Editorial note -->
              <p v-if="step.editorial" class="editorial-note">"{{ step.editorial }}"</p>

              <!-- Body text -->
              <p v-if="step.body" class="step-body-text" v-html="step.body.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')"></p>

              <!-- Payments -->
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

              <!-- Rule block -->
              <div v-if="step.rule" class="rule-block">
                <p class="rule-block-label">{{ step.rule.label }}</p>
                <p class="rule-block-text">{{ step.rule.text }}</p>
              </div>

              <!-- Checklist -->
              <ul v-if="step.checklist" class="checklist">
                <li v-for="(item, ci) in step.checklist" :key="ci">
                  <strong v-if="item.strong">{{ item.strong }}</strong>{{ item.rest }}
                </li>
              </ul>
            </div>
          </article>
        </div>

        <!-- Cycles (Etapa 4) -->
        <div v-if="stage.cycles" class="cycle-grid">
          <article v-for="(c, ci) in stage.cycles" :key="ci" class="cycle-card">
            <span :class="['cycle-tag', `accent-${stage.accent}`]">{{ c.tag }}</span>
            <h3 class="cycle-title">{{ c.title }}</h3>
            <p class="cycle-text">{{ c.text }}</p>
            <p class="cycle-rule"><strong>·</strong> {{ c.rule }}</p>
          </article>
        </div>

        <!-- Extra rule -->
        <div v-if="stage.extraRule" class="rule-block mt-4">
          <p class="rule-block-label">{{ stage.extraRule.label }}</p>
          <p class="rule-block-text">{{ stage.extraRule.text }}</p>
        </div>
      </section>

      <!-- ── SUMMARY ── -->
      <div class="summary-divider">
        <span>Resumen del sistema</span>
      </div>

      <div class="summary-table">
        <header class="summary-header">
          <p>Flujo completo · Del primer DM al cliente fidelizado</p>
        </header>
        <div class="summary-row">
          <div class="summary-stage accent-red">Preventa</div>
          <div class="summary-pills">
            <span class="summary-pill">01 · Contenido</span>
            <span class="summary-pill">02 · Contacto</span>
            <span class="summary-pill">03 · Diagnóstico</span>
            <span class="summary-pill">04 · Solución</span>
            <span class="summary-pill">05 · Oferta HTML</span>
            <span class="summary-pill">06 · Pago</span>
          </div>
          <div class="summary-count">6</div>
        </div>
        <div class="summary-row">
          <div class="summary-stage accent-gold">Onboarding</div>
          <div class="summary-pills">
            <span class="summary-pill">01 · Acceso plataforma</span>
            <span class="summary-pill">02 · Entrevista perfil</span>
            <span class="summary-pill">03 · Ticket portal</span>
          </div>
          <div class="summary-count">3</div>
        </div>
        <div class="summary-row">
          <div class="summary-stage accent-blue">Activación</div>
          <div class="summary-pills">
            <span class="summary-pill">01 · Presentar plan</span>
            <span class="summary-pill">02 · Orientar portal</span>
            <span class="summary-pill">03 · Primera semana</span>
          </div>
          <div class="summary-count">3</div>
        </div>
        <div class="summary-row">
          <div class="summary-stage accent-green">Activo</div>
          <div class="summary-pills">
            <span class="summary-pill">Check-in semanal</span>
            <span class="summary-pill">Revisión mensual</span>
            <span class="summary-pill">Chat directo</span>
            <span class="summary-pill">Renovación</span>
            <span class="summary-pill">Referidos</span>
          </div>
          <div class="summary-count">∞</div>
        </div>
      </div>

      <!-- ── POWERED BY ── -->
      <div class="powered-bar">
        <span>Powered</span><span class="dot"></span>
        <span>by</span><span class="dot"></span>
        <span class="brand">WellCore Fitness</span><span class="dot"></span>
        <span>Metodología basada en evidencia científica</span>
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
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-secondary);
  border-radius: 14px;
  overflow: hidden;
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
  border: 1px solid var(--color-wc-border);
  background: var(--color-wc-bg-secondary);
  border-radius: 14px;
  padding: 18px;
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
  border: 1px solid var(--color-wc-border);
  border-radius: 14px;
  overflow: hidden;
  background: var(--color-wc-bg-secondary);
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
  border: 1px solid var(--color-wc-border);
  border-radius: 14px;
  background: var(--color-wc-bg-secondary);
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
}
</style>
