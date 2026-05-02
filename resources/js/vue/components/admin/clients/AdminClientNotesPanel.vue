<script setup>
// Backend gap intencional:
// El sistema de notas internas append-only (con autor + timestamp) no
// existe todavía como tabla / endpoint. UI lista, pendiente la-02-backend.
// Endpoint esperado: POST /api/v/admin/clients/:id/notes (body: { text }).
//                   GET  /api/v/admin/clients/:id/notes (timeline).
defineProps({
    client: { type: Object, default: null },
});
</script>

<template>
  <div class="notes-panel">
    <article class="card">
      <header class="card-head">
        <span class="card-eyebrow">NOTAS INTERNAS</span>
        <span class="badge-pending">PROXIMAMENTE</span>
      </header>

      <form class="note-form" @submit.prevent>
        <label class="note-label" for="note-input">NUEVA NOTA</label>
        <textarea
          id="note-input"
          class="note-textarea"
          rows="3"
          placeholder="Las notas serán append-only — quedará registrado el autor y la hora exacta."
          disabled
        />
        <div class="form-foot">
          <span class="form-hint">Reservado para staff. Visible solo en el panel admin.</span>
          <button type="button" class="btn-disabled" disabled>
            AGREGAR NOTA
          </button>
        </div>
      </form>

      <div class="placeholder-body">
        <p class="placeholder-quote">
          "El historial editorial de cada cliente. Append-only — porque las decisiones
          que se borran no son decisiones, son ruido."
        </p>
      </div>
    </article>
  </div>
</template>

<style scoped>
.notes-panel { display: flex; flex-direction: column; gap: 12px; }

.card {
    border-radius: var(--r-md, 16px);
    border: 1px dashed var(--c-border);
    background: rgba(17, 17, 17, 0.5);
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.card-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.card-eyebrow {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.badge-pending {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: var(--r-pill, 999px);
    background: rgba(245,158,11,0.1);
    color: #FCD34D;
}

.note-form { display: flex; flex-direction: column; gap: 8px; }
.note-label {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.note-textarea {
    width: 100%;
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255, 255, 255, 0.02);
    padding: 10px 12px;
    color: var(--c-text-2);
    font-family: var(--font-sans);
    font-size: 13px;
    resize: vertical;
    min-height: 72px;
    transition: border-color 0.15s var(--ease-out, ease);
}
.note-textarea:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
.note-textarea::placeholder {
    color: var(--c-text-3);
    font-style: italic;
}

.form-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.form-hint {
    font-family: var(--font-display);
    font-size: 8px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    color: var(--c-text-3);
}
.btn-disabled {
    padding: 0 14px;
    min-height: var(--tap-comfort, 48px);
    border-radius: var(--r-sm, 12px);
    border: 1px solid var(--c-border);
    background: rgba(255, 255, 255, 0.02);
    color: var(--c-text-3);
    font-family: var(--font-display);
    font-size: 9px;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    cursor: not-allowed;
    opacity: 0.55;
}

.placeholder-body { padding-top: 4px; border-top: 1px solid rgba(255, 255, 255, 0.04); }
.placeholder-quote {
    font-family: var(--font-editorial, var(--font-sans));
    font-style: italic;
    font-size: 12px;
    color: #C8A769;
    line-height: 1.55;
    margin: 8px 0 0;
    text-wrap: balance;
}
</style>
