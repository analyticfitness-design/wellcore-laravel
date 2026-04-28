<script setup>
// Backend gap intencional:
// El log de comunicaciones (WhatsApp Business + email transaccional) no
// existe todavía como endpoint. Esta vista renderiza el estado pendiente
// con voz editorial — no falsea data ni muestra "—" sin contexto.
// Cuando la-02-backend agregue GET /api/v/admin/clients/:id/communication-log,
// se conecta el array al ítem item.kind / item.timestamp / item.payload.
defineProps({
    client: { type: Object, default: null },
});
</script>

<template>
  <div class="comm-panel">
    <article class="card">
      <header class="card-head">
        <span class="card-eyebrow">LOG DE COMUNICACION</span>
        <span class="badge-pending">PROXIMAMENTE</span>
      </header>

      <div class="placeholder-body">
        <p class="placeholder-quote">
          "El log de WhatsApp Business y email transaccional al cliente vivirá aquí.
          La interfaz queda lista y a la espera del endpoint backend."
        </p>
        <ul class="checklist">
          <li><span class="check">·</span> WhatsApp templates enviados (delivered / read / responded)</li>
          <li><span class="check">·</span> Emails de Mailjet (open / click / bounce)</li>
          <li><span class="check">·</span> Notificaciones in-app (vista / clic)</li>
          <li><span class="check">·</span> Push notifications (delivered / opened)</li>
        </ul>
        <p class="placeholder-note">
          Mientras tanto, las notificaciones se ejecutan vía PushNotificationService
          y WellcoreNotification — pero sin agregación visible para el admin.
        </p>
      </div>
    </article>
  </div>
</template>

<style scoped>
.comm-panel { display: flex; flex-direction: column; gap: 12px; }

.card {
    border-radius: 14px;
    border: 1px dashed var(--color-wc-border);
    background: rgba(17, 17, 17, 0.5);
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.card-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.card-eyebrow {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--color-wc-text-tertiary);
}
.badge-pending {
    font-family: var(--font-mono, monospace);
    font-size: 8px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: 4px;
    background: var(--color-wc-amber-soft, rgba(245, 158, 11, 0.1));
    color: var(--color-wc-amber-text, #FCD34D);
}

.placeholder-body { display: flex; flex-direction: column; gap: 12px; }
.placeholder-quote {
    font-family: var(--font-editorial, serif);
    font-style: italic;
    font-size: 13px;
    color: var(--color-wc-gold, #C8A769);
    line-height: 1.55;
    margin: 0;
    text-wrap: balance;
}
.checklist {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.checklist li {
    font-family: var(--font-sans);
    font-size: 12px;
    color: var(--color-wc-text-secondary);
    display: flex;
    align-items: baseline;
    gap: 8px;
}
.check {
    color: var(--color-wc-text-tertiary);
    font-family: var(--font-mono, monospace);
    flex-shrink: 0;
}
.placeholder-note {
    font-family: var(--font-mono, monospace);
    font-size: 9px;
    letter-spacing: 0.14em;
    color: var(--color-wc-text-tertiary);
    margin: 4px 0 0;
    line-height: 1.6;
}
</style>
