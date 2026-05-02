<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useRouter, onBeforeRouteLeave } from 'vue-router';
import AdminLayout from '../../layouts/AdminLayout.vue';
import AdminGreeting from '../../components/admin/dashboard/AdminGreeting.vue';
import AdminSettingsSidebar from '../../components/admin/settings/AdminSettingsSidebar.vue';
import AdminSettingsField from '../../components/admin/settings/AdminSettingsField.vue';
import AdminSettingsSecretField from '../../components/admin/settings/AdminSettingsSecretField.vue';
import AdminSettingsSaveBar from '../../components/admin/settings/AdminSettingsSaveBar.vue';
import AdminSettingsImageUpload from '../../components/admin/settings/AdminSettingsImageUpload.vue';
import { useAdminSettingsStore } from '../../stores/adminSettings.js';

const store = useAdminSettingsStore();
const router = useRouter();

// ── Confirm antes de salir con cambios sin guardar ───────────────────────────

onBeforeRouteLeave(() => {
  if (store.dirty) {
    return window.confirm('Hay cambios sin guardar. ¿Salir sin guardar?');
  }
  return true;
});

function onBeforeUnloadHandler(e) {
  if (store.dirty) {
    e.preventDefault();
    e.returnValue = '';
  }
}
onMounted(() => {
  window.addEventListener('beforeunload', onBeforeUnloadHandler);
  store.fetch();
});
onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', onBeforeUnloadHandler);
});

// ── Secciones ───────────────────────────────────────────────────────────────

const SECTIONS = [
  { id: 'general',       label: 'General',        superadminOnly: false, icon: iconGeneral },
  { id: 'branding',      label: 'Branding',        superadminOnly: false, icon: iconBranding },
  { id: 'pagos',         label: 'Pagos',           superadminOnly: true,  icon: iconPagos },
  { id: 'email',         label: 'Email',           superadminOnly: true,  icon: iconEmail },
  { id: 'notificaciones',label: 'Notificaciones',  superadminOnly: false, icon: iconNotif },
  { id: 'coaches',       label: 'Coaches',         superadminOnly: false, icon: iconCoaches },
  { id: 'rise',          label: 'RISE',            superadminOnly: false, icon: iconRise },
  { id: 'seguridad',     label: 'Seguridad',       superadminOnly: true,  icon: iconSeguridad },
  { id: 'integraciones', label: 'Integraciones',   superadminOnly: true,  icon: iconIntegraciones },
  { id: 'mantenimiento', label: 'Mantenimiento',   superadminOnly: true,  icon: iconMantenimiento },
];

function selectSection(id) {
  store.setActiveSection(id);
}

// ── Cross-field validations ──────────────────────────────────────────────────

const crossFieldErrors = ref({});

function validateCrossField(section) {
  crossFieldErrors.value = {};
  const f = store.formData;

  if (section === 'seguridad') {
    const req2fa  = f.seguridad?.require_2fa_admin;
    const smtpHost = f.email?.smtp_host;
    if (req2fa && (!smtpHost || smtpHost === '')) {
      crossFieldErrors.value['seguridad.require_2fa_admin'] =
        'Necesitas configurar Email primero — el 2FA usa OTP por correo.';
    }
  }

  if (section === 'pagos') {
    const origCurrency = store.originalData.pagos?.currency;
    const newCurrency  = f.pagos?.currency;
    if (origCurrency && newCurrency && origCurrency !== newCurrency) {
      crossFieldErrors.value['pagos.currency'] =
        `Cambiar de ${origCurrency} a ${newCurrency} puede requerir migrar precios de los planes activos.`;
    }
  }

  if (section === 'coaches') {
    const origMax = store.originalData.coaches?.max_clients_per_coach;
    const newMax  = f.coaches?.max_clients_per_coach;
    if (origMax && newMax && Number(newMax) < Number(origMax)) {
      crossFieldErrors.value['coaches.max_clients_per_coach'] =
        store.warnings?.coaches_max_clients || `El nuevo limite (${newMax}) es menor al actual (${origMax}). Puede afectar coaches existentes.`;
    }
  }
}

// ── Save / discard ──────────────────────────────────────────────────────────

async function handleSave() {
  // Validaciones cross-field por sección activa
  validateCrossField(store.activeSection);
  if (Object.keys(crossFieldErrors.value).length > 0) return;
  await store.saveAll();
}

function handleDiscard() {
  store.discardAll();
  crossFieldErrors.value = {};
}

// ── Toast ────────────────────────────────────────────────────────────────────

// ── SMTP test ────────────────────────────────────────────────────────────────
const smtpTestEmail = ref('');
const smtpTesting = ref(false);
const smtpTestResult = ref(null);

async function testSmtp() {
  if (smtpTesting.value) return;
  smtpTesting.value = true;
  smtpTestResult.value = null;
  try {
    const f = store.formData.email ?? {};
    const result = await store.testSmtp({
      host:     f.smtp_host,
      port:     f.smtp_port,
      user:     f.smtp_user,
      password: f.smtp_password,
      to:       smtpTestEmail.value || f.sender_email,
    });
    smtpTestResult.value = result;
  } catch (err) {
    smtpTestResult.value = { ok: false, message: 'Error al conectar con la API.' };
  } finally {
    smtpTesting.value = false;
  }
}

// ── Wompi verify ────────────────────────────────────────────────────────────
const wompiVerifying = ref(false);
const wompiVerifyResult = ref(null);

async function verifyWompi() {
  if (wompiVerifying.value) return;
  wompiVerifying.value = true;
  wompiVerifyResult.value = null;
  try {
    const f = store.formData.pagos ?? {};
    const result = await store.verifyPaymentGateway({
      gateway:    'wompi',
      public_key: f.wompi_public_key,
    });
    wompiVerifyResult.value = result;
  } catch {
    wompiVerifyResult.value = { ok: false, message: 'Error al verificar.' };
  } finally {
    wompiVerifying.value = false;
  }
}

// ── Acceso helper ────────────────────────────────────────────────────────────
function f(section) { return store.formData[section] ?? {}; }
function isReadonly(section) { return store.sectionReadonly(section); }
function fieldErr(key) { return crossFieldErrors.value[key] ?? ''; }

// ── SVG icons (inline raw) ───────────────────────────────────────────────────
const iconGeneral       = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>`;
const iconBranding      = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>`;
const iconPagos         = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>`;
const iconEmail         = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>`;
const iconNotif         = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>`;
const iconCoaches       = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`;
const iconRise          = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>`;
const iconSeguridad     = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>`;
const iconIntegraciones = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>`;
const iconMantenimiento = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>`;

const CURRENCIES = [
  { value: 'COP', label: 'COP — Peso colombiano' },
  { value: 'USD', label: 'USD — Dolar estadounidense' },
  { value: 'MXN', label: 'MXN — Peso mexicano' },
  { value: 'EUR', label: 'EUR — Euro' },
];

const TIMEZONES = [
  { value: 'America/Bogota', label: 'America/Bogota (COT −5)' },
  { value: 'America/Mexico_City', label: 'America/Mexico_City (CST −6)' },
  { value: 'America/Lima', label: 'America/Lima (PET −5)' },
  { value: 'America/Buenos_Aires', label: 'America/Buenos_Aires (ART −3)' },
  { value: 'America/Santiago', label: 'America/Santiago (CLT −3/−4)' },
  { value: 'UTC', label: 'UTC' },
];

const LOCALES = [
  { value: 'es', label: 'Español' },
  { value: 'en', label: 'English' },
  { value: 'pt', label: 'Português' },
];
</script>

<template>
  <AdminLayout>
    <div class="settings-page">

      <!-- ── Header ──────────────────────────────────────────────────────── -->
      <AdminGreeting
        greeting="CONFIGURACION DEL SISTEMA"
        :critical-alerts="0"
        :pending-tickets="0"
        :review-tickets="0"
      />

      <!-- Loading skeleton -->
      <template v-if="store.loading">
        <div class="settings-skeleton">
          <div class="sk-bar"></div>
          <div class="settings-skeleton-body">
            <div class="sk-sidebar"></div>
            <div class="sk-content">
              <div v-for="n in 5" :key="n" class="sk-field"></div>
            </div>
          </div>
        </div>
      </template>

      <!-- Error state -->
      <template v-else-if="store.error && !Object.keys(store.formData).length">
        <div class="settings-error" role="alert">
          <p class="settings-error-msg">{{ store.error }}</p>
          <button type="button" class="settings-retry-btn" @click="store.fetch">REINTENTAR →</button>
        </div>
      </template>

      <!-- ── Layout principal ─────────────────────────────────────────────── -->
      <template v-else>
        <div class="settings-layout">

          <!-- Desktop sidebar -->
          <AdminSettingsSidebar
            class="settings-sidebar-desktop"
            :sections="SECTIONS"
            :active-section="store.activeSection"
            :is-super-admin="store.isSuperAdmin"
            @select="selectSection"
          />

          <!-- Content area -->
          <div class="settings-content">

            <!-- ── MOBILE: selector de secciones (scroll horizontal) ─────── -->
            <div class="settings-mobile-tabs" aria-label="Secciones">
              <button
                v-for="sec in SECTIONS"
                :key="sec.id"
                type="button"
                class="settings-mobile-tab"
                :class="{
                  'settings-mobile-tab--active': store.activeSection === sec.id,
                  'settings-mobile-tab--locked': sec.superadminOnly && !store.isSuperAdmin,
                }"
                @click="selectSection(sec.id)"
              >
                <span v-html="sec.icon" aria-hidden="true"></span>
                {{ sec.label }}
              </button>
            </div>

            <!-- ── Secciones (v-show controla cual es visible) ──────────── -->
            <div class="settings-sections-wrap">

              <!-- ╔═ GENERAL ════════════════════════════════════════════╗ -->
              <section v-show="store.activeSection === 'general'" class="settings-section" aria-labelledby="sec-general">
                <h2 id="sec-general" class="settings-section-title">GENERAL</h2>
                <p class="settings-section-sub">Identidad y configuracion base de la plataforma.</p>
                <div class="settings-fields-grid">
                  <AdminSettingsField label="Nombre de la plataforma" type="text" :model-value="f('general').platform_name" :disabled="isReadonly('general')" required @update:model-value="store.setField('general','platform_name',$event)" />
                  <AdminSettingsField label="Email de soporte" type="email" :model-value="f('general').support_email" :disabled="isReadonly('general')" @update:model-value="store.setField('general','support_email',$event)" />
                  <AdminSettingsField label="Zona horaria" type="select" :model-value="f('general').timezone" :options="TIMEZONES" :disabled="isReadonly('general')" @update:model-value="store.setField('general','timezone',$event)" />
                  <AdminSettingsField label="Idioma por defecto" type="select" :model-value="f('general').default_locale" :options="LOCALES" :disabled="isReadonly('general')" @update:model-value="store.setField('general','default_locale',$event)" />
                </div>
              </section>

              <!-- ╔═ BRANDING ════════════════════════════════════════════╗ -->
              <section v-show="store.activeSection === 'branding'" class="settings-section" aria-labelledby="sec-branding">
                <h2 id="sec-branding" class="settings-section-title">BRANDING</h2>
                <p class="settings-section-sub">Identidad visual. La paleta de colores v2 es de solo lectura.</p>
                <div class="settings-fields-grid">
                  <AdminSettingsImageUpload
                    label="Logo principal (512 x 512 px)"
                    :model-value="f('branding').logo_url"
                    :max-size-px="512"
                    :disabled="isReadonly('branding')"
                    hint="Se usara en emails, documentos y header movil."
                    @update:model-value="store.setField('branding','logo_url',$event)"
                  />
                  <div class="settings-readonly-block">
                    <span class="settings-readonly-label">COLOR DE ACENTO</span>
                    <div class="settings-color-preview">
                      <span class="settings-color-swatch" style="background:#DC2626"></span>
                      <span class="settings-color-value font-mono">#DC2626</span>
                      <span class="settings-readonly-badge">SISTEMA v2</span>
                    </div>
                  </div>
                  <div class="settings-readonly-block">
                    <span class="settings-readonly-label">FONDO PRINCIPAL</span>
                    <div class="settings-color-preview">
                      <span class="settings-color-swatch" style="background:#0a0a0a;border:1px solid rgba(255,255,255,0.15)"></span>
                      <span class="settings-color-value font-mono">#0a0a0a</span>
                      <span class="settings-readonly-badge">SISTEMA v2</span>
                    </div>
                  </div>
                </div>
              </section>

              <!-- ╔═ PAGOS ════════════════════════════════════════════════╗ -->
              <section v-show="store.activeSection === 'pagos'" class="settings-section" aria-labelledby="sec-pagos">
                <h2 id="sec-pagos" class="settings-section-title">PAGOS</h2>
                <p class="settings-section-sub">Pasarelas de pago y configuracion de moneda.</p>
                <div class="settings-superadmin-notice" v-if="!store.isSuperAdmin">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  Solo Superadmin puede modificar esta configuracion.
                </div>
                <div class="settings-fields-grid">
                  <AdminSettingsField label="Moneda" type="select" :model-value="f('pagos').currency" :options="CURRENCIES" :disabled="isReadonly('pagos')" :error="fieldErr('pagos.currency')" @update:model-value="store.setField('pagos','currency',$event)" />
                  <AdminSettingsField label="URL de Webhook" type="url" :model-value="f('pagos').webhook_url" :disabled="isReadonly('pagos')" hint="Recibe notificaciones de Wompi." @update:model-value="store.setField('pagos','webhook_url',$event)" />
                  <AdminSettingsSecretField label="Wompi — Clave publica" :model-value="f('pagos').wompi_public_key" :disabled="isReadonly('pagos')" hint="pub_test_... o pub_prod_..." @update:model-value="store.setField('pagos','wompi_public_key',$event)" />
                  <AdminSettingsSecretField label="Wompi — Clave privada" :model-value="f('pagos').wompi_private_key" :disabled="isReadonly('pagos')" @update:model-value="store.setField('pagos','wompi_private_key',$event)" />
                  <AdminSettingsSecretField label="MercadoPago — Clave publica" :model-value="f('pagos').mercadopago_public_key" :disabled="isReadonly('pagos')" @update:model-value="store.setField('pagos','mercadopago_public_key',$event)" />
                  <AdminSettingsSecretField label="MercadoPago — Clave secreta" :model-value="f('pagos').mercadopago_secret_key" :disabled="isReadonly('pagos')" @update:model-value="store.setField('pagos','mercadopago_secret_key',$event)" />
                </div>
                <div v-if="store.isSuperAdmin" class="settings-action-row">
                  <button type="button" class="settings-test-btn" :disabled="wompiVerifying" @click="verifyWompi">
                    <span v-if="wompiVerifying" class="mini-spinner" aria-hidden="true"></span>
                    {{ wompiVerifying ? 'VERIFICANDO' : 'VERIFICAR KEYS WOMPI →' }}
                  </button>
                  <span v-if="wompiVerifyResult" class="settings-test-result" :class="wompiVerifyResult.ok ? 'settings-test-result--ok' : 'settings-test-result--err'">
                    {{ wompiVerifyResult.message }}
                  </span>
                </div>
              </section>

              <!-- ╔═ EMAIL ════════════════════════════════════════════════╗ -->
              <section v-show="store.activeSection === 'email'" class="settings-section" aria-labelledby="sec-email">
                <h2 id="sec-email" class="settings-section-title">EMAIL</h2>
                <p class="settings-section-sub">Configuracion SMTP para correos transaccionales.</p>
                <div class="settings-superadmin-notice" v-if="!store.isSuperAdmin">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  Solo Superadmin puede modificar esta configuracion.
                </div>
                <div class="settings-fields-grid">
                  <AdminSettingsField label="Host SMTP" type="text" :model-value="f('email').smtp_host" :disabled="isReadonly('email')" placeholder="smtp.mailjet.com" @update:model-value="store.setField('email','smtp_host',$event)" />
                  <AdminSettingsField label="Puerto SMTP" type="number" :model-value="f('email').smtp_port" :disabled="isReadonly('email')" placeholder="587" @update:model-value="store.setField('email','smtp_port',$event)" />
                  <AdminSettingsField label="Usuario SMTP" type="email" :model-value="f('email').smtp_user" :disabled="isReadonly('email')" @update:model-value="store.setField('email','smtp_user',$event)" />
                  <AdminSettingsSecretField label="Contrasena SMTP" :model-value="f('email').smtp_password" :disabled="isReadonly('email')" @update:model-value="store.setField('email','smtp_password',$event)" />
                  <AdminSettingsField label="Nombre del remitente" type="text" :model-value="f('email').sender_name" :disabled="isReadonly('email')" @update:model-value="store.setField('email','sender_name',$event)" />
                  <AdminSettingsField label="Email del remitente" type="email" :model-value="f('email').sender_email" :disabled="isReadonly('email')" @update:model-value="store.setField('email','sender_email',$event)" />
                </div>
                <div v-if="store.isSuperAdmin" class="settings-action-row">
                  <div class="settings-smtp-test-row">
                    <input type="email" v-model="smtpTestEmail" class="settings-smtp-input" placeholder="tu@correo.com" :disabled="smtpTesting" aria-label="Email de prueba SMTP" />
                    <button type="button" class="settings-test-btn" :disabled="smtpTesting || !smtpTestEmail" @click="testSmtp">
                      <span v-if="smtpTesting" class="mini-spinner" aria-hidden="true"></span>
                      {{ smtpTesting ? 'ENVIANDO' : 'PROBAR SMTP →' }}
                    </button>
                  </div>
                  <span v-if="smtpTestResult" class="settings-test-result" :class="smtpTestResult.ok ? 'settings-test-result--ok' : 'settings-test-result--err'">
                    {{ smtpTestResult.message }}
                  </span>
                </div>
              </section>

              <!-- ╔═ NOTIFICACIONES ══════════════════════════════════════╗ -->
              <section v-show="store.activeSection === 'notificaciones'" class="settings-section" aria-labelledby="sec-notif">
                <h2 id="sec-notif" class="settings-section-title">NOTIFICACIONES</h2>
                <p class="settings-section-sub">Canales de notificacion activos para coaches y clientes.</p>
                <div class="settings-fields-grid">
                  <AdminSettingsField label="Push notifications" type="toggle" :model-value="f('notificaciones').push_enabled" :disabled="isReadonly('notificaciones')" hint="Notificaciones push via servicio web." @update:model-value="store.setField('notificaciones','push_enabled',$event)" />
                  <AdminSettingsField label="Notificaciones por email" type="toggle" :model-value="f('notificaciones').email_notifications" :disabled="isReadonly('notificaciones')" hint="Enviar emails transaccionales." @update:model-value="store.setField('notificaciones','email_notifications',$event)" />
                  <AdminSettingsSecretField label="WhatsApp Business — API Key" :model-value="f('notificaciones').whatsapp_api_key" :disabled="isReadonly('notificaciones')" hint="Token de WhatsApp Cloud API." @update:model-value="store.setField('notificaciones','whatsapp_api_key',$event)" />
                  <AdminSettingsField label="WhatsApp — Phone ID" type="text" :model-value="f('notificaciones').whatsapp_phone_id" :disabled="isReadonly('notificaciones')" hint="ID del numero en Meta Business." @update:model-value="store.setField('notificaciones','whatsapp_phone_id',$event)" />
                </div>
              </section>

              <!-- ╔═ COACHES ══════════════════════════════════════════════╗ -->
              <section v-show="store.activeSection === 'coaches'" class="settings-section" aria-labelledby="sec-coaches">
                <h2 id="sec-coaches" class="settings-section-title">COACHES</h2>
                <p class="settings-section-sub">Limites operacionales y estructura de comisiones.</p>
                <div class="settings-fields-grid">
                  <AdminSettingsField label="Max. clientes por coach" type="number" :model-value="f('coaches').max_clients_per_coach" :disabled="isReadonly('coaches')" :error="fieldErr('coaches.max_clients_per_coach')" hint="Por encima de este limite se bloquea la asignacion." @update:model-value="store.setField('coaches','max_clients_per_coach',$event)" />
                  <AdminSettingsField label="SLA de respuesta (horas)" type="number" :model-value="f('coaches').sla_response_hours" :disabled="isReadonly('coaches')" hint="Tiempo maximo para responder un check-in." @update:model-value="store.setField('coaches','sla_response_hours',$event)" />
                  <AdminSettingsField label="Comision del coach (%)" type="number" :model-value="f('coaches').commission_percent" :disabled="isReadonly('coaches')" hint="Porcentaje del pago del cliente que va al coach." @update:model-value="store.setField('coaches','commission_percent',$event)" />
                  <AdminSettingsField label="Auto-asignar coach" type="toggle" :model-value="f('coaches').auto_assign" :disabled="isReadonly('coaches')" hint="Asignar coach automaticamente a nuevas inscripciones." @update:model-value="store.setField('coaches','auto_assign',$event)" />
                </div>
              </section>

              <!-- ╔═ RISE ═════════════════════════════════════════════════╗ -->
              <section v-show="store.activeSection === 'rise'" class="settings-section" aria-labelledby="sec-rise">
                <h2 id="sec-rise" class="settings-section-title">RISE</h2>
                <p class="settings-section-sub">Parametros del programa de transformacion RISE.</p>
                <div class="settings-fields-grid">
                  <AdminSettingsField label="Duracion del programa (semanas)" type="number" :model-value="f('rise').program_duration_weeks" :disabled="isReadonly('rise')" @update:model-value="store.setField('rise','program_duration_weeks',$event)" />
                  <AdminSettingsField label="Numero de retos" type="number" :model-value="f('rise').challenge_count" :disabled="isReadonly('rise')" @update:model-value="store.setField('rise','challenge_count',$event)" />
                  <AdminSettingsField label="Puntos por reto completado" type="number" :model-value="f('rise').reward_points_per_challenge" :disabled="isReadonly('rise')" @update:model-value="store.setField('rise','reward_points_per_challenge',$event)" />
                  <AdminSettingsField label="Dias de prueba" type="number" :model-value="f('rise').trial_days" :disabled="isReadonly('rise')" hint="Dias antes del primer pago RISE." @update:model-value="store.setField('rise','trial_days',$event)" />
                </div>
              </section>

              <!-- ╔═ SEGURIDAD ════════════════════════════════════════════╗ -->
              <section v-show="store.activeSection === 'seguridad'" class="settings-section" aria-labelledby="sec-seg">
                <h2 id="sec-seg" class="settings-section-title">SEGURIDAD</h2>
                <p class="settings-section-sub">Politica de acceso y autenticacion.</p>
                <div class="settings-superadmin-notice" v-if="!store.isSuperAdmin">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  Solo Superadmin puede modificar esta configuracion.
                </div>
                <div class="settings-fields-grid">
                  <AdminSettingsField label="2FA obligatorio para admins" type="toggle" :model-value="f('seguridad').require_2fa_admin" :disabled="isReadonly('seguridad')" :error="fieldErr('seguridad.require_2fa_admin')" hint="Requiere OTP por email al iniciar sesion." @update:model-value="store.setField('seguridad','require_2fa_admin',$event)" />
                  <AdminSettingsField label="Longitud minima de contrasena" type="number" :model-value="f('seguridad').password_min_length" :disabled="isReadonly('seguridad')" @update:model-value="store.setField('seguridad','password_min_length',$event)" />
                  <AdminSettingsField label="Requerir mayuscula en contrasena" type="toggle" :model-value="f('seguridad').password_require_upper" :disabled="isReadonly('seguridad')" @update:model-value="store.setField('seguridad','password_require_upper',$event)" />
                  <AdminSettingsField label="Timeout de sesion (minutos)" type="number" :model-value="f('seguridad').session_timeout_minutes" :disabled="isReadonly('seguridad')" hint="Inactividad maxima antes de cerrar sesion automaticamente." @update:model-value="store.setField('seguridad','session_timeout_minutes',$event)" />
                </div>
              </section>

              <!-- ╔═ INTEGRACIONES ════════════════════════════════════════╗ -->
              <section v-show="store.activeSection === 'integraciones'" class="settings-section" aria-labelledby="sec-int">
                <h2 id="sec-int" class="settings-section-title">INTEGRACIONES</h2>
                <p class="settings-section-sub">Analytics, tracking y APIs externas.</p>
                <div class="settings-superadmin-notice" v-if="!store.isSuperAdmin">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  Solo Superadmin puede modificar esta configuracion.
                </div>
                <div class="settings-fields-grid">
                  <AdminSettingsField label="Meta Pixel ID" type="text" :model-value="f('integraciones').meta_pixel_id" :disabled="isReadonly('integraciones')" hint="Convierte visitas en datos de audiencia en Meta Ads." @update:model-value="store.setField('integraciones','meta_pixel_id',$event)" />
                  <AdminSettingsField label="Google Analytics ID" type="text" :model-value="f('integraciones').ga_tracking_id" :disabled="isReadonly('integraciones')" placeholder="G-XXXXXXXXXX" @update:model-value="store.setField('integraciones','ga_tracking_id',$event)" />
                  <AdminSettingsSecretField label="Mixpanel Token" :model-value="f('integraciones').mixpanel_token" :disabled="isReadonly('integraciones')" @update:model-value="store.setField('integraciones','mixpanel_token',$event)" />
                  <AdminSettingsField label="Hotjar ID" type="text" :model-value="f('integraciones').hotjar_id" :disabled="isReadonly('integraciones')" @update:model-value="store.setField('integraciones','hotjar_id',$event)" />
                  <AdminSettingsSecretField label="Anthropic API Key" :model-value="f('integraciones').anthropic_api_key" :disabled="isReadonly('integraciones')" hint="Usada por el generador IA de planes." @update:model-value="store.setField('integraciones','anthropic_api_key',$event)" />
                </div>
              </section>

              <!-- ╔═ MANTENIMIENTO ════════════════════════════════════════╗ -->
              <section v-show="store.activeSection === 'mantenimiento'" class="settings-section" aria-labelledby="sec-mant">
                <h2 id="sec-mant" class="settings-section-title">MANTENIMIENTO</h2>
                <p class="settings-section-sub">Control de disponibilidad de la plataforma.</p>
                <div class="settings-superadmin-notice" v-if="!store.isSuperAdmin">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  Solo Superadmin puede modificar esta configuracion.
                </div>
                <div v-if="f('mantenimiento').maintenance_mode" class="settings-warning-banner" role="alert">
                  La plataforma esta en modo mantenimiento. Los clientes no pueden acceder.
                </div>
                <div class="settings-fields-grid">
                  <AdminSettingsField label="Modo mantenimiento" type="toggle" :model-value="f('mantenimiento').maintenance_mode" :disabled="isReadonly('mantenimiento')" hint="Deshabilita el acceso de clientes y coaches temporalmente." @update:model-value="store.setField('mantenimiento','maintenance_mode',$event)" />
                  <AdminSettingsField label="Mensaje de mantenimiento" type="textarea" :model-value="f('mantenimiento').maintenance_message" :disabled="isReadonly('mantenimiento')" hint="Texto visible en la pantalla de mantenimiento." @update:model-value="store.setField('mantenimiento','maintenance_message',$event)" />
                  <AdminSettingsField label="Ventana de deploy — inicio" type="text" :model-value="f('mantenimiento').deploy_window_start" :disabled="isReadonly('mantenimiento')" placeholder="02:00" hint="Hora en UTC para iniciar deploys (HH:MM)." @update:model-value="store.setField('mantenimiento','deploy_window_start',$event)" />
                  <AdminSettingsField label="Ventana de deploy — fin" type="text" :model-value="f('mantenimiento').deploy_window_end" :disabled="isReadonly('mantenimiento')" placeholder="05:00" @update:model-value="store.setField('mantenimiento','deploy_window_end',$event)" />
                </div>
              </section>

            </div><!-- /settings-sections-wrap -->
          </div><!-- /settings-content -->
        </div><!-- /settings-layout -->
      </template><!-- /v-else loaded -->

      <!-- ── Toast ──────────────────────────────────────────────────────── -->
      <Teleport to="body">
        <Transition name="toast">
          <div
            v-if="store.toast"
            :key="store.toast.id"
            class="settings-toast"
            :class="store.toast.type === 'error' ? 'settings-toast--error' : 'settings-toast--ok'"
            role="status"
            aria-live="polite"
          >
            {{ store.toast.message }}
          </div>
        </Transition>
      </Teleport>

      <!-- ── Save bar ────────────────────────────────────────────────────── -->
      <AdminSettingsSaveBar
        :dirty="store.dirty"
        :dirty-count="store.dirtyCount"
        :saving="store.saving"
        @save="handleSave"
        @discard="handleDiscard"
      />

    </div><!-- /settings-page -->
  </AdminLayout>
</template>

<style scoped>
.settings-page {
  display: flex;
  flex-direction: column;
  gap: 0;
  min-height: calc(100vh - var(--admin-topbar-h, 64px));
}

/* ── Layout ─────────────────────────────────────────────────────────────── */
.settings-layout {
  display: flex;
  flex: 1;
  position: relative;
}

.settings-sidebar-desktop {
  display: none;
}

.settings-content {
  flex: 1;
  min-width: 0;
  padding: 20px 16px 100px; /* bottom padding para save bar */
}

@media (min-width: 1024px) {
  .settings-layout {
    gap: 0;
  }
  .settings-sidebar-desktop {
    display: block;
    position: sticky;
    top: var(--admin-topbar-h, 64px);
    height: calc(100vh - var(--admin-topbar-h, 64px));
    overflow-y: auto;
    align-self: flex-start;
  }
  .settings-content {
    padding: 28px 32px 100px;
  }
}

/* ── Mobile tabs (selector scroll) ─────────────────────────────────────── */
.settings-mobile-tabs {
  display: flex;
  gap: 6px;
  overflow-x: auto;
  padding: 0 0 12px;
  scrollbar-width: none;
  -ms-overflow-style: none;
  border-bottom: 1px solid var(--c-border);
  margin-bottom: 20px;
}
.settings-mobile-tabs::-webkit-scrollbar { display: none; }

.settings-mobile-tab {
  display: flex;
  align-items: center;
  gap: 6px;
  height: 28px;
  padding: 0 12px;
  border-radius: var(--r-pill, 999px);
  border: 1px solid var(--c-border);
  background: transparent;
  color: var(--c-text-2);
  font-family: var(--font-display);
  font-size: 9px;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  white-space: nowrap;
  cursor: pointer;
  transition: color 0.12s, background 0.12s, border-color 0.12s;
  flex-shrink: 0;
}
.settings-mobile-tab :deep(svg) { stroke: currentColor; width: 11px; height: 11px; }
.settings-mobile-tab--active {
  border-color: var(--c-accent);
  background: var(--c-accent-dim);
  color: var(--c-text);
}
.settings-mobile-tab--locked { opacity: 0.55; }

@media (min-width: 1024px) {
  .settings-mobile-tabs { display: none; }
}

/* ── Sections wrap ──────────────────────────────────────────────────────── */
.settings-sections-wrap { display: block; }

.settings-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
  max-width: 720px;
}

.settings-section-title {
  font-family: var(--font-display);
  font-size: 28px;
  letter-spacing: 0.04em;
  color: var(--c-text);
  margin: 0;
  line-height: 1.1;
}
.settings-section-sub {
  font-family: var(--font-sans);
  font-size: 13px;
  color: var(--c-text-2);
  margin: -12px 0 0;
}

.settings-fields-grid {
  display: flex;
  flex-direction: column;
  gap: 18px;
  padding: 20px;
  background: var(--c-surface);
  border: 1px solid var(--c-border);
  border-radius: var(--r-md, 16px);
}

@media (min-width: 640px) {
  .settings-fields-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px 24px;
  }
  /* Full-width fields */
  .settings-fields-grid :deep(.sf-wrap:first-child),
  .settings-fields-grid :deep(.siu-wrap) {
    grid-column: 1 / -1;
  }
  .settings-fields-grid :deep(.sf-wrap.full) {
    grid-column: 1 / -1;
  }
}

/* ── Superadmin notice ──────────────────────────────────────────────────── */
.settings-superadmin-notice {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  border-radius: var(--r-sm, 12px);
  border: 1px solid var(--c-border);
  background: rgba(255,255,255,0.02);
  font-family: var(--font-display);
  font-size: 9px;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  color: var(--c-text-3);
}

/* ── Warning banner ─────────────────────────────────────────────────────── */
.settings-warning-banner {
  padding: 12px 16px;
  border-radius: var(--r-sm, 12px);
  border: 1px solid rgba(245,158,11,0.25);
  background: rgba(245,158,11,0.1);
  font-family: var(--font-sans);
  font-size: 13px;
  color: #FCD34D;
}

/* ── Readonly color preview ─────────────────────────────────────────────── */
.settings-readonly-block {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.settings-readonly-label {
  font-family: var(--font-display);
  font-size: 9px;
  letter-spacing: 1.6px;
  text-transform: uppercase;
  color: var(--c-text-3);
}
.settings-color-preview {
  display: flex;
  align-items: center;
  gap: 10px;
  height: 36px;
  padding: 0 12px;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--c-border);
  border-radius: var(--r-sm, 12px);
}
.settings-color-swatch {
  width: 18px;
  height: 18px;
  border-radius: 4px;
  flex-shrink: 0;
}
.settings-color-value {
  flex: 1;
  font-family: var(--font-display);
  font-size: 12px;
  color: var(--c-text-2);
}
.settings-readonly-badge {
  font-family: var(--font-display);
  font-size: 8px;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  color: var(--c-text-3);
  border: 1px solid var(--c-border);
  border-radius: var(--r-pill, 999px);
  padding: 2px 6px;
}

/* ── Action rows (test SMTP, verify Wompi) ──────────────────────────────── */
.settings-action-row {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.settings-smtp-test-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}
.settings-smtp-input {
  flex: 1;
  min-width: 180px;
  height: 34px;
  padding: 0 10px;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--c-border);
  border-radius: var(--r-sm, 12px);
  color: var(--c-text);
  font-family: var(--font-sans);
  font-size: 13px;
  outline: none;
}
.settings-smtp-input:focus { border-color: var(--c-accent); }
.settings-test-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  height: 34px;
  padding: 0 16px;
  border-radius: var(--r-sm, 12px);
  border: 1px solid rgba(255,255,255,0.12);
  background: transparent;
  color: var(--c-text-2);
  font-family: var(--font-display);
  font-size: 9px;
  letter-spacing: 1.6px;
  text-transform: uppercase;
  cursor: pointer;
  transition: color 0.12s, border-color 0.12s;
  white-space: nowrap;
}
.settings-test-btn:hover:not(:disabled) { color: var(--c-text); border-color: var(--c-accent); }
.settings-test-btn:disabled { opacity: 0.45; cursor: not-allowed; }
.mini-spinner {
  width: 10px; height: 10px;
  border: 1.5px solid rgba(255,255,255,0.25);
  border-top-color: currentColor;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.settings-test-result {
  font-family: var(--font-sans);
  font-size: 12px;
}
.settings-test-result--ok { color: #34D399; }
.settings-test-result--err { color: #F87171; }

/* ── Toast ──────────────────────────────────────────────────────────────── */
.settings-toast {
  position: fixed;
  top: 80px;
  right: 20px;
  z-index: 200;
  padding: 12px 18px;
  border-radius: 10px;
  font-family: var(--font-sans);
  font-size: 13px;
  max-width: 340px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.4);
}
.settings-toast--ok {
  background: rgba(16,185,129,0.12);
  border: 1px solid rgba(16,185,129,0.25);
  color: #34D399;
}
.settings-toast--error {
  background: var(--c-accent-dim);
  border: 1px solid rgba(220,38,38,0.25);
  color: #F87171;
}
.toast-enter-active, .toast-leave-active { transition: transform 0.22s var(--ease-out), opacity 0.18s; }
.toast-enter-from, .toast-leave-to { transform: translateX(110%); opacity: 0; }

/* ── Error state ────────────────────────────────────────────────────────── */
.settings-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 48px 24px;
  text-align: center;
}
.settings-error-msg {
  font-family: var(--font-sans);
  font-size: 14px;
  color: #F87171;
}
.settings-retry-btn {
  font-family: var(--font-display);
  font-size: 9px;
  letter-spacing: 1.6px;
  text-transform: uppercase;
  color: var(--c-text-2);
  background: none;
  border: 1px solid var(--c-border);
  border-radius: var(--r-sm, 12px);
  padding: 8px 16px;
  cursor: pointer;
  transition: color 0.12s, border-color 0.12s;
}
.settings-retry-btn:hover { color: var(--c-text); border-color: var(--c-accent); }

/* ── Skeleton ───────────────────────────────────────────────────────────── */
.settings-skeleton { display: flex; flex-direction: column; gap: 16px; padding: 20px 16px; }
.settings-skeleton-body { display: flex; gap: 24px; }
.sk-bar, .sk-sidebar, .sk-content, .sk-field {
  background: var(--c-surface-2);
  border: 1px solid var(--c-border);
  border-radius: 12px;
  animation: sk-pulse 1.5s ease-in-out infinite;
}
@keyframes sk-pulse { 0%, 100% { opacity: 0.6; } 50% { opacity: 0.9; } }
.sk-bar { height: 48px; }
.sk-sidebar { width: 240px; min-height: 320px; flex-shrink: 0; }
.sk-content { flex: 1; display: flex; flex-direction: column; gap: 12px; padding: 16px; }
.sk-field { height: 36px; }

/* ── Reduced motion ─────────────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
  .accordion-caret,
  .save-bar,
  .settings-toast { transition: none !important; animation: none !important; }
}
</style>
