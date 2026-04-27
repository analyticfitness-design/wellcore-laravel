# Coach Strategy Hub — Design Spec

**Fecha:** 2026-04-26
**Versión:** 1.0
**Estado:** Listo para revisión
**Owner:** Daniel Esparza
**Subsistema:** Fase 1 (Strategy Hub lectura) + Fase 1.5 (Admin queue mínimo)

---

## 1. Contexto y motivación

WellCore necesita una nueva pestaña en el dashboard del coach (`/coach/strategy`) que entregue, semana a semana, un paquete de marketing **100% personalizado por coach**: brief estratégico, guiones de reels, plan de stories Lun-Dom, checklist de producción, banco semanal de hooks/CTAs/captions, y sets de hashtags.

El contenido se produce **offline** mediante sesiones de Claude Code que leen un sistema de MDs en `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-MARKETING-COACHES\` (espejo del sistema existente `SISTEMA-CREACION-PLANES`). Daniel revisa y aprueba cada drop antes de que el coach lo vea. El coach percibe el servicio como "Equipo Estrategia WellCore" — la mecánica de Claude Code permanece confidencial.

El objetivo es que cada coach reciba un kit de marketing premium, sienta que tiene una estrategia hecha a la medida, y aprenda a producir contenido siguiendo guiones cinematográficos de calidad — replicando el lenguaje visual de los HTMLs ya consolidados en `E:\WELLCORE FITNESS PLATAFORMA\Produccion comercial\`.

## 2. Decisiones lockeadas

| # | Decisión | Lockdown |
|---|---|---|
| D1 | Subsistema a construir primero | Fase 1: Strategy Hub (lectura coach) + sistema offline `SISTEMA-CREACION-MARKETING-COACHES` |
| D2 | Personalización entre coaches | 100% personalizado por coach |
| D3 | Cadencia de drops | Drop semanal único (semana ISO Lun→Dom) |
| D4 | Composición del drop | Estándar premium: brief + 2 reels + 7 stories + checklist + banco semanal + hashtags |
| D5 | Recolección del intake | Coach self-service con admin override |
| D6 | Profundidad del intake | Compact (~12 campos en 1 paso) con énfasis en público/metodologías/temas |
| D7 | Workflow de publicación | Admin approval gate (`pending → generating → in_review → approved → ready → ...`) |
| D8 | Voice samples | Opcionales al final del intake |
| D9 | Estructura de la pestaña | "Esta semana" + tab "Historial" |
| D10 | Rendering de stories en Fase 1 | Texto + preview visual estático WellCore-styled (botones "Copiar texto" + "Descargar PNG") |
| D11 | Atribución del drop | "Por Daniel · Equipo Estrategia WellCore" |

## 3. Arquitectura de alto nivel

```
┌──────────────────────────────────────────────────────────────────────┐
│   E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-MARKETING-COACHES\ │
│   (Sistema offline — humanos + Claude Code)                          │
│                                                                      │
│   PROMPT-CLAUDE-CODE-NUEVA-SESION.txt                                │
│   + 20 MDs (workflow, voz, schemas, reglas, banco, operación)        │
│                              │                                       │
│            Daniel + Claude Code (sesión semanal por coach)           │
│                              │                                       │
│                              ▼                                       │
│              JSON canónico `coach_drop_v1` + intake_snapshot          │
└──────────────────────────────│───────────────────────────────────────┘
                               │ INSERT vía script PHP heredoc
                               │ ejecutado en EasyPanel (mismo patrón
                               │  que SISTEMA-CREACION-PLANES)
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│   wellcore-laravel  (MySQL wellcore_fitness — DB compartida)        │
│                                                                     │
│   Tablas: coach_marketing_profiles · coach_content_drops ·          │
│           coach_content_piece_states                                │
│                                                                     │
│   API:    /api/v/coach/strategy/* · /api/v/coach/marketing-profile  │
│           /api/v/admin/marketing/*                                  │
│                                                                     │
│   UI Coach:                          UI Admin (Fase 1.5):            │
│    /coach/onboarding/brand-profile    /admin/marketing/queue         │
│    /coach/strategy                    /admin/marketing/drops/:id      │
│    /coach/profile?tab=brand           /admin/coaches/:id/marketing-  │
│                                         profile                      │
└─────────────────────────────────────────────────────────────────────┘
```

**Tres capas, un contrato JSON (`coach_drop_v1`):**

1. **Capa offline (E:\\)** — humanos operan, Claude Code asiste, output es JSON `coach_drop_v1` validado contra schema formal.
2. **Capa de persistencia + API** — Laravel Eloquent con DTOs/Resources tipadas; valida el JSON con schema antes de aceptar inserción; state machine explícita.
3. **Capa de presentación** — Vue 3 SPA, Pinia store dedicado, componentes con dirección visual "Editorial Production Document".

**Por qué la capa offline vive en E:\ y no en el repo:** las decisiones de marketing son confidenciales, los MDs cambian más rápido que el código, y queremos que cualquier sesión de Claude Code (no solo en este repo) pueda servir. Mismo patrón que `SISTEMA-CREACION-PLANES`.

## 4. Modelo de datos

### 4.1 Tabla `coach_marketing_profiles` (intake — 1 fila por coach)

```sql
CREATE TABLE coach_marketing_profiles (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  coach_id BIGINT UNSIGNED NOT NULL UNIQUE,

  -- Identidad
  brand_name VARCHAR(120) NOT NULL,
  city VARCHAR(80),
  country_code CHAR(2),

  -- Especialidad
  specialty_primary ENUM('fuerza','hipertrofia','recomposicion',
                         'perdida_grasa','mujeres_postparto',
                         'funcional','otro') NOT NULL,
  specialty_primary_other VARCHAR(80),
  specialty_secondary ENUM('fuerza','hipertrofia','recomposicion',
                           'perdida_grasa','mujeres_postparto',
                           'funcional','otro') NULL,
  specialty_secondary_other VARCHAR(80),
  differentiator TEXT NOT NULL,

  -- Audiencia (persona)
  audience_age_range ENUM('18-25','25-35','35-45','45+') NOT NULL,
  audience_gender ENUM('mujeres','hombres','mixto') NOT NULL,
  audience_pain_main VARCHAR(200) NOT NULL,
  audience_offer_main ENUM('esencial','metodo','elite',
                           'presencial','otro') NOT NULL,

  -- Metodologías y temas (multi-select como JSON array)
  preferred_methodologies JSON NOT NULL,
  preferred_methodologies_other JSON,
  content_topics JSON NOT NULL,
  content_topics_other JSON,

  -- Voz
  voice_adjectives JSON NOT NULL,        -- exactamente 3 chips
  voice_samples JSON,                    -- [{caption:string, source_url:string|null,
                                         --   note:string|null}], 0-3, opcional

  -- Ofertas activas
  active_offers JSON NOT NULL,           -- [{name:string, price:number, currency:ISO4217 ej "COP","USD",
                                         --   promo:string|null}], 1-3 elementos

  -- Top posts que funcionaron (opcional)
  top_working_posts JSON,                -- [{url,why_worked}] hasta 3

  -- Meta
  completed_at TIMESTAMP NULL,
  last_updated_by ENUM('coach','admin') NOT NULL,
  last_admin_editor_id BIGINT UNSIGNED NULL,

  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,

  INDEX idx_completed (completed_at),
  CONSTRAINT fk_cmp_coach FOREIGN KEY (coach_id)
    REFERENCES admins(id) ON DELETE CASCADE
);
```

### 4.2 Tabla `coach_content_drops` (1 fila por coach por semana ISO)

```sql
CREATE TABLE coach_content_drops (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  coach_id BIGINT UNSIGNED NOT NULL,

  -- Calendario
  iso_year SMALLINT UNSIGNED NOT NULL,
  iso_week TINYINT UNSIGNED NOT NULL,
  week_starts_on DATE NOT NULL,          -- lunes ISO

  -- Estado
  status ENUM('pending','generating','in_review',
              'approved','ready','in_progress',
              'completed','archived') NOT NULL DEFAULT 'pending',

  -- Contenido
  content JSON NOT NULL,
  intake_snapshot JSON NOT NULL,         -- foto del intake al generar
  schema_version VARCHAR(20) NOT NULL DEFAULT 'coach_drop_v1',

  -- Audit / mejora continua
  generated_by_session_id VARCHAR(80),
  original_content JSON NULL,            -- snapshot del content al INSERT inicial
  admin_edits_diff JSON NULL,            -- diff calculado al approve (final vs original)
  generated_at TIMESTAMP NULL,
  reviewed_at TIMESTAMP NULL,
  reviewed_by_id BIGINT UNSIGNED NULL,
  approved_at TIMESTAMP NULL,
  approved_by_id BIGINT UNSIGNED NULL,
  ready_at TIMESTAMP NULL,
  completed_at TIMESTAMP NULL,

  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,

  UNIQUE KEY uniq_coach_week (coach_id, iso_year, iso_week),
  INDEX idx_status_week (status, iso_year, iso_week),
  CONSTRAINT fk_ccd_coach FOREIGN KEY (coach_id)
    REFERENCES admins(id) ON DELETE CASCADE
);
```

### 4.3 Tabla `coach_content_piece_states` (tracking por pieza)

```sql
CREATE TABLE coach_content_piece_states (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  drop_id BIGINT UNSIGNED NOT NULL,
  coach_id BIGINT UNSIGNED NOT NULL,    -- denormalizado para query rápido

  piece_type ENUM('reel','story','checklist_phase') NOT NULL,
  piece_key VARCHAR(40) NOT NULL,       -- 'reel_1', 'story_lun', 'phase_pre', etc.

  state ENUM('pending','in_progress','published','skipped')
    NOT NULL DEFAULT 'pending',
  published_url VARCHAR(500),
  notes TEXT,

  state_changed_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,

  UNIQUE KEY uniq_piece (drop_id, piece_type, piece_key),
  CONSTRAINT fk_ccps_drop FOREIGN KEY (drop_id)
    REFERENCES coach_content_drops(id) ON DELETE CASCADE
);
```

### 4.4 Notas de diseño

- **Coach = `admins.id` con `role = UserRole::Coach`** — no existe tabla `coaches` separada. El FK `coach_id` referencia `admins(id)` en las 3 tablas (mismo patrón que el resto del codebase: `coach_notes.coach_id`, `coach_messages.coach_id`, `coach_invitations.coach_id`, etc., todos apuntan a `admins`).
- **`intake_snapshot`** es deliberado: si el coach actualiza su intake, drops anteriores conservan el contexto que los generó. Lo captura el script PHP heredoc al momento del INSERT inicial (lee `coach_marketing_profiles` por `coach_id` y embebe el `toArray()` actual en la columna).
- **`schema_version`** desde el día 1 — futura `coach_drop_v2` se agrega sin romper renderers anteriores.
- **`status='archived'`** se aplica a drops `completed >= 30 días` automáticamente vía comando de consola programado (`php artisan wellcore:archive-old-drops`), corre 1 vez al día.
- **`admin_edits_diff`** se calcula y persiste **únicamente al transicionar `in_review → approved`**, comparando el `content` original (snapshot tomado al INSERT inicial, conservado en columna interna) contra el `content` final que se aprueba. Ediciones intermedias del admin no generan diffs incrementales — solo importa el diff final-vs-original para señales de mejora del MD system.
- **Migraciones aditivas únicamente** — sin DROP, sin RENAME (memory `feedback_db_safety`).

## 5. Contrato JSON `coach_drop_v1`

El JSON formal vive en `schemas/coach_drop_v1.schema.json` (artefacto del repo) y se valida server-side con **opis/json-schema** antes de aceptar inserción. Estructura conceptual:

```jsonc
{
  "schema_version": "coach_drop_v1",

  "brief": {
    "title": "string",
    "objective": "string",
    "priority_offer": "esencial|metodo|elite|presencial|otro",
    "key_message": "string",
    "target_metric": "string",
    "weekly_theme": "string",
    "framing_copy": "string"               // Fraunces Italic en UI
  },

  "reels": [
    {
      "key": "reel_1|reel_2",
      "type": "educativo|conversion",
      "title": "string",
      "format_meta": {
        "duration_sec_min": 30,
        "duration_sec_max": 45,
        "platforms": ["instagram","tiktok"],
        "bpm_hint": "100"
      },
      "hook": {
        "text": "string",
        "rationale": "string"
      },
      "timecode_table": [
        {
          "time": "00:00-00:03",
          "dialogue": "string",
          "visual": "string",
          "edit_notes": "string"
        }
      ],
      "caption": "string",
      "music_note": "string",
      "production_notes": "string"          // Fraunces Italic en UI
    }
  ],

  "stories": [
    {
      "day": "LUN|MAR|MIE|JUE|VIE|SAB|DOM",
      "pillar": "activacion|nutricion|spotlight|bts|qa|motivacion|reset",
      "slides": [
        {
          "kind": "text|template|visual",
          "text": "string",
          "visual_hint": "string",
          "sticker": "poll|slider|question|none"
        }
      ],
      "dm_followup_hint": "string"
    }
  ],

  "checklist": {
    "phases": [
      {
        "key": "pre|cam|edit|pub",
        "title": "string",
        "items": [
          { "title": "string", "detail": "string", "subitems": ["string"] }
        ]
      }
    ]
  },

  "bank": {
    "alt_hooks":   ["string", "string", "string", "string", "string"],
    "alt_ctas":    ["string", "string", "string"],
    "alt_captions":["string", "string", "string"]
  },

  "hashtags": {
    "sets": [
      { "name": "string", "tags": ["#x", "#y"] }
    ]
  }
}
```

**Validaciones críticas en el JSON Schema:**
- `reels` — exactamente 2 elementos.
- `stories` — exactamente 7 elementos, días únicos LUN→DOM.
- `checklist.phases` — exactamente 4 fases con keys `pre`, `cam`, `edit`, `pub`.
- `bank.alt_hooks` — exactamente 5; `alt_ctas` — exactamente 3; `alt_captions` — exactamente 3.
- Todos los strings con `minLength: 1` y `maxLength` definido explícitamente por campo en el archivo `schemas/coach_drop_v1.schema.json` (artefacto del repo, source of truth — el MD `20a-SCHEMA-COACH-DROP-V1.md` lo describe en lenguaje humano).

## 6. Sistema offline `SISTEMA-CREACION-MARKETING-COACHES`

**Ubicación:** `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-MARKETING-COACHES\`

**Total: 20 MDs + 1 prompt maestro `.txt`** (mismo patrón que `SISTEMA-CREACION-PLANES`).

### 6.1 Estructura de carpeta

```
SISTEMA-CREACION-MARKETING-COACHES\
│
│ ── BLOQUE A: Workflow y reglas de operación ──
├── 00-INDEX.md                          Orquestador: bloques, orden de lectura por caso
├── 01-PASO-A-PASO.md                    Workflow 7 fases
├── 02-CREDENCIALES.md                   Refs a memory (no creds reales)
│
│ ── BLOQUE B: Voz, identidad y prohibiciones ──
├── 05-VOZ-WELLCORE.md                   Voz neutra WellCore (10% del contenido)
├── 06-VOZ-COACH.md                      Cómo extraer voz del coach desde intake +
│                                         voice samples (90% del contenido)
├── 07-PROHIBICIONES.md                  15 prohibiciones críticas
│
│ ── BLOQUE C: Reglas por tipo de pieza ──
├── 10-REGLAS-BRIEF.md                   Estructura del brief estratégico
├── 11-REGLAS-REEL.md                    Estructura timecode (espejo HTML 01-)
├── 12-REGLAS-STORIES-DIARIAS.md         Estructura Lun→Dom (espejo HTML 04-)
├── 13-CHECKLIST-PRODUCCION-REEL.md      4 fases (espejo HTML 05-)
├── 14-BANCO-SEMANAL-ALTERNATIVOS.md     5 hooks + 3 CTAs + 3 captions de respaldo
├── 15-HASHTAGS-Y-SETS.md                4 sets curados según tema/nicho
│
│ ── BLOQUE D: Schemas JSON canónicos ──
├── 20-DATA-MODEL-MARKETING.md           Visión general 3 tablas + relaciones
├── 20a-SCHEMA-COACH-DROP-V1.md          JSON canónico completo (humano)
├── 20b-SCHEMA-INTAKE.md                 JSON del intake (mapping a profiles)
├── 21-VALIDACION-PRE-INSERT.md          Reglas de validación previa
│
│ ── BLOQUE E: Operación y mejora continua ──
├── 30-COMO-MONTAR-EN-DB.md              Script PHP heredoc + INSERT vía tinker
├── 31-CHECKLIST-VERIFICACION-DASHBOARD  MCP Chrome DevTools post-INSERT
├── 32-CALENDARIO-EDITORIAL-90DIAS.md    Pilares anuales + tema mensual + sugerencias
├── 33-MEJORA-CONTINUA.md                Cómo leer admin_edits_diff y refinar MDs
│
└── PROMPT-CLAUDE-CODE-NUEVA-SESION.txt  EL ARTEFACTO MÁS CRÍTICO (12 secciones)
```

### 6.2 Convenciones (idénticas a SISTEMA-CREACION-PLANES)

- Naming kebab-case con números (`00-INDEX.md`, `20a-SCHEMA-COACH-DROP-V1.md`).
- Sin frontmatter YAML — Markdown puro con `#` headers.
- Numeración no secuencial; agrupa por dominio.
- Contenido = instrucciones para Claude + ejemplos JSON inline + plantillas copy-paste + referencias.
- Credenciales NUNCA en MDs — referencias a memory de Claude Code.
- Output: el plan-of-work termina como JSON insertado en `coach_content_drops.content` con `status='in_review'`. NO HTML, NO archivos sueltos.

### 6.3 Las 12 secciones del prompt maestro

```
0.  Proyecto y stack (apunta a wellcore-laravel + tablas relevantes)
1.  Sistema de MDs — ubicación, los 20 archivos con marca ⭐ a los críticos
2.  Orden de lectura obligatorio (PASO 1-10)
3.  Contexto técnico (DB compartida vanilla+laravel, no destructivo, voz coach NO IA)
4.  Intake del coach — cómo leer coach_marketing_profiles vía tinker
5.  Workflow 7 fases:
      F0: Verificar intake completo del coach
      F1: Lectura MDs (Bloques A-D según caso)
      F2: Diseñar drop (brief + 2 reels + 7 stories + checklist + banco + hashtags)
      F3: Armar JSON coach_drop_v1 (templates de schemas D)
      F4: INSERT vía script PHP heredoc (status='in_review')
      F5: Invalidar caches (`coach_drop_v3:{id}:{year}:{week}`)
      F6: Notificar admin (push interno + Daniel WhatsApp)
      F7: Esperar approval — Claude NO auto-aprueba
6.  Credenciales y accesos (refs a memory)
7.  JSON canónico coach_drop_v1 (resumen rápido + ejemplo inline)
8.  Si algo falla — 10 síntomas comunes
9.  Checklist final antes de marcar como in_review (12 items)
10. Casos de uso (coach nuevo · recurrente · regeneración por feedback de Daniel ·
    cambio de oferta · bajo engagement)
11. NO HACER NUNCA (15 prohibiciones, ej: nunca mencionar IA/Claude, nunca generar
    sin intake completo, nunca usar voz neutra cuando hay voice samples, nunca
    recomendar herramienta/app sin datos del coach)
12. ¿Qué hacer ahora? (primer mensaje al admin)
```

## 7. UX — superficies del coach

### 7.1 `/coach/onboarding/brand-profile`

Formulario de 6 secciones visualmente diferenciadas (1 paso scroll-largo, no wizard multistep). Auto-save de draft con debounce 500ms tras última interacción. Botón final: **"Activar mi Estrategia"** → redirect a `/coach/strategy` con animación "unlock".

```
01 IDENTIDAD              brand_name · city · country_code
02 ESPECIALIDAD           specialty_primary (+ secondary opcional) · differentiator
03 AUDIENCIA              audience_age_range · audience_gender ·
                          audience_pain_main · audience_offer_main
04 METODOLOGÍAS Y TEMAS   preferred_methodologies (multi) · content_topics (multi)
05 VOZ                    voice_adjectives (3 chips) · voice_samples (opcional, hasta 3)
06 OFERTAS Y TOP POSTS    active_offers (hasta 3) · top_working_posts (opcional, hasta 3)
```

**Reglas de validación cliente:** voice_adjectives = exactamente 3 chips; preferred_methodologies y content_topics = mínimo 1; active_offers = mínimo 1.

### 7.2 `/coach/strategy` — Esta semana (vista principal)

```
┌───────────────────────────────────────────────────────────────────┐
│  [JetBrains Mono · 11px · letter-spacing 0.2em · gris 30%]        │
│  WC-DROP / ANDREA-VEGA / ISO-W14 / 21 ABR 2026 / APPROVED         │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━                              │
│                                                                   │
│  [Oswald 700 · clamp(48px,7vw,96px) · tracking 0.02em]            │
│  ESTA SEMANA                                                      │
│                                                                   │
│  [Fraunces Italic 400 · 22px · gris 60%]                          │
│  Drop curado de Andrea Vega                                       │
│                                                                   │
│  [Raleway 500 · 12px · gold #C8A769 · letter-spacing 0.15em]      │
│  POR DANIEL · EQUIPO ESTRATEGIA WELLCORE                          │
│                                                                   │
│  [progress strip Mono]                                            │
│  PIEZAS PUBLICADAS  ███░░░░░░░ 3/9  ·  CIERRA DOM 27 ABR          │
│                                                                   │
│  ─────────────────────────  01 / BRIEF  ─────────────────────────│
│  [Brief content rendered]                                         │
│                                                                   │
│  ─────────────────────────  02 / REELS  ─────────────────────────│
│  [Reel #1 shooting script card]                                   │
│  [Reel #2 shooting script card]                                   │
│                                                                   │
│  ─────────────────────────  03 / STORIES  ───────────────────────│
│  [Fila Lun-Dom de 7 cards 9:16 con day-coding]                    │
│                                                                   │
│  ─────────────────────────  04 / CHECKLIST  ─────────────────────│
│  [4 fases marcables, persistentes en piece_states]                │
│                                                                   │
│  ─────────────────────────  05 / BANCO SEMANAL  ─────────────────│
│  [5 hooks alternativos · 3 CTAs · 3 captions]                     │
│                                                                   │
│  ─────────────────────────  06 / HASHTAGS  ──────────────────────│
│  [4 sets curados]                                                 │
└───────────────────────────────────────────────────────────────────┘
```

**Estado vacío premium** (sin drop esta semana):

```
┌───────────────────────────────────────────────────────────────────┐
│  [Oswald 700 grande]                                              │
│  TU DROP ESTÁ EN PREPARACIÓN                                      │
│                                                                   │
│  [Fraunces Italic gris]                                           │
│  El equipo estrategia está finalizando tu paquete de la semana 15│
│                                                                   │
│  [Botón ghost] Ver mi drop anterior →                             │
└───────────────────────────────────────────────────────────────────┘
```

### 7.3 `/coach/strategy?tab=historial`

Lista cronológica inversa de drops anteriores. Cada card: `Semana X · resumen 1 línea · % completado`. Click → modal con drop completo en read-only.

### 7.4 `/coach/profile?tab=brand`

Mismo formulario que onboarding en modo edición. Warning si hay drop activo (`generating | in_review | ready | in_progress`):

> "Tus cambios afectarán drops futuros, no este. El próximo drop semanal se generará con tu nueva información."

## 8. UX — superficies del admin (Fase 1.5)

### 8.1 `/admin/marketing/queue`

Tabla operativa con todos los coaches activos × semana actual. Stats top:

> "3 drops pending review · 2 coaches sin drop esta semana"

Filtros: status, coach, semana ISO. Tabla con columnas:

```
Coach           | Semana | Estado     | Última acción | Acción
Andrea Vega     |   14   | in_review  | hace 2h       | [Revisar]
Pablo Ruiz      |   14   | pending    | -             | [Generar]
Carla Mendoza   |   14   | ready      | aprobado 1h   | -
```

Click [Revisar] → `/admin/marketing/drops/:id`.

### 8.2 `/admin/marketing/drops/:id`

Split view:

- **Izquierda:** `intake_snapshot` del coach (read-only).
- **Derecha:** `content` JSON renderizado igual que ve el coach + editor inline para ajustar texto.

Botones: **[Aprobar y publicar]** · **[Pedir regenerar]**.

Edits manuales se capturan en `admin_edits_diff` al aprobar (diff entre `original_content` y `final_content`).

### 8.3 `/admin/coaches/:id/marketing-profile`

Mismo formulario que coach pero accesible por admin para edit. Audit: quién editó qué cuándo (`last_admin_editor_id`).

## 9. Dirección visual

### 9.1 Design interrogation — 10 respuestas

```
01 · Brand personality:    Concierge · Decisivo · Cinematográfico
02 · Theme:                Dark-first
03 · Direction:            Editorial brutalism (WellCore + cinematic editorial)
04 · Typography:
       Display:   Oswald 700 (uppercase, tracking 0.02-0.05em)
       Editorial: Fraunces Italic 400 (subheads, attribution, framing copy)
       Body:      Raleway 400/500/700
       Mono:      JetBrains Mono 400/500
05 · Color accents:
       Primary:   #DC2626
       Day-coded: LUN #DC2626 · MAR #10B981 · MIE #F59E0B · JUE #3B82F6 ·
                  VIE #A78BFA · SAB #EC4899 · DOM #14B8A6
       Premium:   #C8A769 (gold satinado — solo en attribution "por Daniel")
06 · Spatial motif:        Broken-grid editorial — asymmetric, content rompe contenedor
07 · Atmosphere:           SVG noise 4-6% opacity + red radial glow bajo hero (8%)
08 · Motion:               Orchestrated page-load (stagger 80ms) ·
                           cubic-bezier(0.16,1,0.3,1)
09 · Brand existente:      WellCore Athletic Brutalism (locked)
10 · References:           Criterion Collection production notes · The Players' Tribune ·
                           Linear docs · HTMLs 01/04/05/12 producción WellCore
```

### 9.2 Inflexión — Fraunces Italic como segundo voice

Hasta hoy WellCore vive con Oswald (display) + Raleway (body). Se incorpora **Fraunces Italic 400** en muy pequeñas dosis (5% del volumen) en 3 lugares:

1. Subhead bajo el hero del drop ("Drop curado de Andrea Vega").
2. Section framing copy debajo de los headers Oswald.
3. Notas de producción en cada reel.

Esa variación rompe la cadencia robótica de Oswald all-caps y le da el feel "publicación premium" sin tirar el brand language.

### 9.3 Sectioning numerado tipo guión cinematográfico

Cada sección de la pestaña usa numeración con `/` rojo de acento:

```
01 / BRIEF                                          de la semana
─────────────────────────                           ─────────────
[Oswald 700 · 32px · "/" en #DC2626]              [Fraunces Italic gris]
```

### 9.4 Reel card — shooting script aesthetic

La card del reel hereda directo el aesthetic del HTML `01-guion-reel-entrenamiento.html`:

- Strip metadata superior en JetBrains Mono: `EDU · 35-45s · IG+TT · 100bpm`.
- Hook block con borde-izquierdo rojo 3px, fondo `rgba(220,38,38,0.03)`, texto Oswald 18px bold.
- Tabla timecode 4 columnas (Tiempo / Diálogo / Visual / Edición) con mono en tiempos, italic Fraunces en notas de edición.
- Caption block en Raleway con borde sutil.
- Música note en pill verde con ♫.
- Notas de producción en Fraunces Italic.
- Action row al pie: [Marcar como publicado] [Copiar guión] [Ver checklist].

### 9.5 Stories Lun-Dom — fila de 7 (rompe grid 3-4)

7 cards 9:16 con day-coding por color (LUN→DOM). Breakpoint: ≥`lg` (1024px) muestra fila completa de 7 columnas; `md` (768-1023px) colapsa a 2 filas (4+3); `<md` scroll horizontal full-width con `scroll-snap-type: x mandatory`. Hover: lift -2px + corner gold indicator (solo desktop, no mobile).

Click en card → drawer lateral con: template completo de la story, "Copiar texto", "Descargar PNG", checklist DM follow-up.

**"Descargar PNG" — implementación**: client-side vía librería `html-to-image` (npm). Se renderiza un nodo HTML oculto de 1080×1920 con la tipografía/fondo/texto WellCore, se exporta a PNG via `htmlToImage.toPng(node)` y se dispara el download del blob. Sin canvas editable (eso es Fase 3), sin servidor involucrado, sin queue. Latencia: < 500ms en hardware moderno.

### 9.6 Atmosphere — capa de profundidad obligatoria

```css
/* Sobre todo el body de la pestaña */
.strategy-page {
  background: #09090B;
  position: relative;
}
.strategy-page::before {
  content: '';
  position: fixed; inset: 0; pointer-events: none;
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'><filter id='n'><feTurbulence baseFrequency='0.85' numOctaves='2'/></filter><rect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/></svg>");
  mix-blend-mode: overlay;
  z-index: 1;
}
.strategy-page::after {
  content: '';
  position: absolute; top: 0; left: 50%; transform: translateX(-50%);
  width: 80%; height: 400px; pointer-events: none;
  background: radial-gradient(ellipse at center,
              rgba(220,38,38,0.08), transparent 60%);
  z-index: 0;
}
```

### 9.7 Motion intencional

```ts
// Stagger del page-load — easing custom, no "all 0.3s ease"
const enter = (delay: number) => ({
  initial: { opacity: 0, y: 12 },
  animate: { opacity: 1, y: 0 },
  transition: { delay, duration: 0.6, ease: [0.16, 1, 0.3, 1] }
});
```

Hover en cards:
```css
transition: transform 240ms cubic-bezier(0.16,1,0.3,1),
            box-shadow 320ms ease-out;
&:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 32px rgba(220,38,38,0.08);
}
```

### 9.8 Anti-Generic Checklist — verificación

- [x] Display NO es Inter — es Oswald 700.
- [x] No purple→blue gradient — paleta WellCore + day-coded.
- [x] Layout rompe grid 3+ veces (hero asymmetric, sectioning numerado, fila de 7 stories).
- [x] Atmosphere layer presente (noise + red radial glow).
- [x] Motion intencional (easing custom, stagger 80ms declarado).
- [x] Palette ≥ 2 accents (red + 7 day colors + gold premium).
- [x] Display + Body fonts diferentes (Oswald + Raleway + Fraunces).
- [x] Border-radius mixto (strip cuadrado, cards 12px, pills 20px).
- [x] Element con interacción inesperada (corner gold indicator on hover).
- [x] Contrast — `#FAFAFA` sobre `#09090B` = 19:1 (AAA).

## 10. Quality bar — código no-negotiable

### 10.1 Backend (PHP 8.4 + Laravel 13)

**`declare(strict_types=1)` en todos los archivos PHP nuevos.**

**Enums tipados** (no strings sueltos):
- `App\Enums\Marketing\DropStatus`
- `App\Enums\Marketing\PieceType`
- `App\Enums\Marketing\PieceState`
- `App\Enums\Marketing\SpecialtyPrimary`
- `App\Enums\Marketing\AudienceAgeRange`
- `App\Enums\Marketing\AudienceGender`
- `App\Enums\Marketing\AudienceOfferMain`
- `App\Enums\Marketing\LastUpdatedBy`

**DTOs `final readonly`** (no arrays sueltos viajando entre capas):
- `App\DataTransferObjects\Marketing\CoachDropV1`
- `App\DataTransferObjects\Marketing\BriefSection`
- `App\DataTransferObjects\Marketing\ReelScript` (con `array<int, ScriptTimecodeRow>`)
- `App\DataTransferObjects\Marketing\StoryDay`
- `App\DataTransferObjects\Marketing\ProductionChecklist`
- `App\DataTransferObjects\Marketing\WeeklyBank`
- `App\DataTransferObjects\Marketing\HashtagSets`
- `App\DataTransferObjects\Marketing\MarketingProfile`

Cada DTO tiene `fromJson(array): self` y `toArray(): array`.

**JSON Schema validator server-side:**
```
composer require opis/json-schema
```

`App\Services\Marketing\DropSchemaValidator` valida cualquier `content` contra `schemas/coach_drop_v1.schema.json` antes de aceptar. Si falla, exception con path al campo inválido.

**Form Requests para todo input HTTP:**
- `App\Http\Requests\Coach\StoreMarketingProfileRequest`
- `App\Http\Requests\Coach\UpdateMarketingProfileRequest`
- `App\Http\Requests\Coach\MarkPiecePublishedRequest`
- `App\Http\Requests\Admin\Marketing\InsertGeneratedDropRequest` (validación schema completa)
- `App\Http\Requests\Admin\Marketing\ApproveDropRequest`
- `App\Http\Requests\Admin\Marketing\UpdateDropContentRequest`

**Resources tipadas para output:**
- `App\Http\Resources\Coach\CoachDropResource` — NUNCA expone `admin_edits_diff`, `generated_by_session_id`, `original_content`, `intake_snapshot`. La attribution line viene de `config('marketing.attribution.line')` (default: "Por Daniel · Equipo Estrategia WellCore"), no hardcoded en el resource.
- `App\Http\Resources\Coach\CoachDropSummaryResource` (para historial).
- `App\Http\Resources\Coach\MarketingProfileResource`.
- `App\Http\Resources\Admin\Marketing\AdminDropResource` (incluye campos sensibles).
- `App\Http\Resources\Admin\Marketing\QueueRowResource`.

**Authorization Policies — IDOR-proof:**
- `App\Policies\Coach\CoachContentDropPolicy` con `view`, `markPiecePublished`. El `$user` recibido es `Admin` (auth model). Verifica `$user instanceof Admin && $user->role === UserRole::Coach && $drop->coach_id === $user->id`.
- `App\Policies\Coach\CoachMarketingProfilePolicy` con `view`, `update`.
- `App\Policies\Admin\Marketing\AdminDropPolicy` con `view`, `update`, `approve`, `requestRegenerate` — verifica `$user->role` está en `[UserRole::Admin, UserRole::Superadmin]`.
- Todas las rutas pasan por `authorize()` — `la-05-security` audita.

**State machine explícita:**
`App\Services\Marketing\DropStateMachine` con tabla de transiciones:

```
pending      → generating
generating   → in_review | pending
in_review    → approved | pending          (pedir regenerar vuelve a queue)
approved     → ready
ready        → in_progress | archived
in_progress  → completed | archived
completed    → archived
```

Transiciones inválidas lanzan `InvalidDropTransition`. Cada transición registra timestamp + actor.

**Performance baseline:**
- Eager loading obligatorio: `with('pieceStates', 'coach.profile')`.
- Cache: `Cache::remember("coach_drop_v3:{$coachId}:{$year}:{$week}", 300, ...)` — TTL 5min, invalidación por eventos `DropApproved`, `PieceMarkedPublished`, `DropArchived`.
- Indices: `(coach_id, iso_year, iso_week)` UNIQUE + `(status, iso_year, iso_week)` para queue admin.

### 10.2 Frontend (Vue 3 + TypeScript estricto)

**`tsconfig.json`** con `strict: true`, `noUncheckedIndexedAccess: true`, `exactOptionalPropertyTypes: true`. Sin `any`. Sin `as` casts gratuitos.

**Tipos generados desde JSON Schema:**
```bash
npm run gen:schema-types
# usa json-schema-to-typescript sobre schemas/coach_drop_v1.schema.json
# output: resources/js/vue/types/marketing.generated.ts (commited)
```

**Pinia store dedicado:** `resources/js/vue/stores/coachStrategy.ts`

```ts
export const useCoachStrategyStore = defineStore('coachStrategy', () => {
  const currentDrop = ref<CoachDrop | null>(null);
  const history = ref<CoachDropSummary[]>([]);
  const profile = ref<MarketingProfile | null>(null);
  const isLoadingDrop = ref(false);

  async function fetchCurrentDrop(): Promise<void>;
  async function markPiecePublished(pieceKey: string, url?: string): Promise<void>;
  async function fetchHistory(): Promise<void>;
  async function fetchProfile(): Promise<void>;
  async function saveProfileDraft(patch: Partial<MarketingProfile>): Promise<void>;

  const isProfileComplete = computed(() => profile.value?.completed_at !== null);

  return { /* ... */ };
});
```

Optimistic UI en `markPiecePublished` (commit local instant + rollback si API falla).

**Componentes nuevos** (todos `<script setup lang="ts">`, ningún SFC > 250 líneas):

```
resources/js/vue/pages/Coach/
  Strategy.vue                            ← container con tabs Esta semana / Historial
  Onboarding/BrandProfile.vue             ← formulario con auto-save

resources/js/vue/layouts/CoachLayout.vue  ← MODIFICAR: agregar item "Estrategia" en
                                            sección "Principal" del sidebar (después
                                            de "Inicio", antes de "Clientes"), con
                                            badge sutil "Nuevo" durante primer mes

resources/js/vue/components/coach/strategy/
  StrategyHero.vue
  StrategyEmptyState.vue
  SectionDivider.vue
  BriefSection.vue
  ReelScriptCard.vue
  ReelTimecodeTable.vue
  StoriesWeekRow.vue
  StoryDayCard.vue
  ProductionChecklistCard.vue
  WeeklyBankCard.vue
  HashtagSetCard.vue
  PieceMarkPublishedButton.vue
  StrategyHistoryList.vue

resources/js/vue/components/coach/onboarding/
  BrandProfileForm.vue
  ProfileSection01Identity.vue
  ProfileSection02Specialty.vue
  ProfileSection03Audience.vue
  ProfileSection04MethodsTopics.vue
  ProfileSection05Voice.vue
  ProfileSection06OffersAndPosts.vue

resources/js/vue/pages/Admin/Marketing/
  Queue.vue
  DropReview.vue
  CoachProfileEdit.vue
```

**Auto-save de drafts** con debounce 500ms en `BrandProfileForm.vue` → `PATCH /api/v/coach/marketing-profile/draft`.

**Vue Router middleware:** `requireCompleteBrandProfile` bloquea entrada a `/coach/strategy` si `coach_marketing_profiles.completed_at IS NULL`.

**Mecánica:**
- Pinia store `coachStrategy` se hidrata en `app.js` mount (`fetchProfile()` antes del primer render del router-view) si el usuario actual es coach.
- El route guard `beforeEach` para rutas `meta: { requiresBrandProfile: true }` lee `useCoachStrategyStore().isProfileComplete`. Si es `false`, redirige a `/coach/onboarding/brand-profile`.
- Después del submit del onboarding, el store se actualiza optimísticamente y la próxima navegación a `/coach/strategy` pasa.

### 10.3 Tests obligatorios (Pest)

```
tests/Feature/Coach/Marketing/
  BrandProfileOnboardingTest.php
  CoachStrategyAccessTest.php          ← IDOR coverage
  DropStateMachineTest.php
  DropSchemaValidationTest.php
  PieceStateTest.php

tests/Feature/Admin/Marketing/
  QueueListingTest.php
  DropReviewAndApprovalTest.php
  AdminEditsDiffCaptureTest.php          ← verifica diff calculado al approve
  RequestRegenerateTest.php              ← verifica vuelta a status=pending
  ArchiveOldDropsCommandTest.php         ← scheduled command
  CoachProfileAdminEditTest.php

tests/Unit/Marketing/
  CoachDropV1DtoTest.php
  DropSchemaValidatorTest.php            ← cubre INSERT path desde script PHP heredoc
  DropStateMachineTransitionsTest.php
  AttributionLineConfigTest.php          ← lee config y nunca hardcoded
```

Factories: `MarketingProfileFactory`, `CoachContentDropFactory` (con states `pending()`, `inReview()`, `ready()`, etc.), `CoachContentPieceStateFactory`.

### 10.4 Versionado y reversibilidad

- Migraciones aditivas únicamente (sin DROP, sin RENAME).
- `schema_version` en `content` desde el día 1.
- Comando programado: `app/Console/Commands/ArchiveOldDropsCommand.php` con signature `wellcore:archive-old-drops`. Registrado en `app/Console/Kernel.php` con `->daily()->at('03:00')`. Archiva drops `status='completed' AND completed_at <= NOW() - INTERVAL 30 DAY` actualizando a `status='archived'`.
- Feature flag `FEATURE_COACH_STRATEGY_ENABLED` en `config/features.php` — permite desactivar la pestaña entera sin rollback de DB.
- Configuración de attribution en `config/marketing.php`:
  ```php
  return [
      'attribution' => [
          'line' => env('MARKETING_ATTRIBUTION_LINE', 'Por Daniel · Equipo Estrategia WellCore'),
      ],
  ];
  ```
  Cambiable por `.env` sin redeploy si Daniel decide ajustar la firma (ej. en su ausencia).

## 11. API endpoints

### 11.1 Coach

```
GET    /api/v/coach/marketing-profile
PUT    /api/v/coach/marketing-profile
PATCH  /api/v/coach/marketing-profile/draft

GET    /api/v/coach/strategy/current        → CoachDropResource o 404
GET    /api/v/coach/strategy/history        → paginated CoachDropSummaryResource
       query: ?page=1&per_page=20  (default per_page=20, max 50)
GET    /api/v/coach/strategy/drops/{id}     → CoachDropResource (read-only, archived OK)

POST   /api/v/coach/strategy/drops/{drop}/pieces/{pieceKey}/publish
       body: { url?: string, notes?: string }
POST   /api/v/coach/strategy/drops/{drop}/pieces/{pieceKey}/skip
POST   /api/v/coach/strategy/drops/{drop}/pieces/{pieceKey}/in-progress
```

### 11.2 Admin

```
GET    /api/v/admin/marketing/queue
       query: ?status=&coach_id=&iso_year=&iso_week=

GET    /api/v/admin/marketing/drops/{id}                  → AdminDropResource
PUT    /api/v/admin/marketing/drops/{id}/content          → actualiza content + diff
POST   /api/v/admin/marketing/drops/{id}/approve          → status='ready', calcula
                                                            admin_edits_diff y persiste
POST   /api/v/admin/marketing/drops/{id}/request-regenerate → status='pending'; preserva
                                                            content actual como referencia
                                                            (admin reescribirá al regenerar);
                                                            opcional body { reason:string }

GET    /api/v/admin/coaches/{id}/marketing-profile
PUT    /api/v/admin/coaches/{id}/marketing-profile
```

Todos requieren middleware `auth:wellcore` + role check (`isCoach()` o `isAdmin()`). Respuestas tipadas, sin Express-style `res.json(any)`.

**Inserción del drop generado offline** se hace mediante script PHP heredoc ejecutado vía tinker en EasyPanel (mismo patrón que `SISTEMA-CREACION-PLANES`). El script:

1. Recibe el JSON `coach_drop_v1` completo + `coach_id` + `iso_year` + `iso_week` como argumentos.
2. Verifica que `admins.id = coach_id` exista, esté activo, y tenga `role = UserRole::Coach`. Si falla, aborta.
3. Lee el intake actual del coach desde `coach_marketing_profiles`. Si `completed_at IS NULL`, aborta con mensaje "Coach no ha completado su Brand Profile — drop no puede generarse". Embebe el intake completo en `intake_snapshot`.
4. Invoca `DropSchemaValidator` sobre el JSON `content` y aborta si falla con detalle del path inválido.
5. Hace UPSERT en `coach_content_drops` (clave `coach_id, iso_year, iso_week`): si ya existe drop para esa semana, actualiza; si no, inserta. `status='in_review'`, `original_content = content` (snapshot baseline para `admin_edits_diff` futuro), timestamps de `generated_at`.
6. Invalida el cache `coach_drop_v3:{coach_id}:{year}:{week}`.
7. Notifica via Database Notification a admins activos (Daniel) — visible en `/admin/marketing/queue`.

NO existe endpoint HTTP público para inserción — es trabajo de admin con acceso a tinker, no superficie expuesta. Esto previene cualquier vector externo para crear drops fraudulentos.

## 12. Matriz de delegación a agentes Laravel

| Pieza | Agente |
|---|---|
| Modelos Eloquent + relaciones + casts a DTOs/enums | `la-02-backend` |
| Migraciones aditivas (3 tablas + indices) | `la-06-database` |
| DTOs + JSON Schema validator + state machine | `la-02-backend` |
| Form Requests + Policies + middleware "profile required" | `la-05-security` |
| API Resources + endpoints REST tipados | `la-15-api` |
| Cache strategy + N+1 audit + indices | `la-10-performance` |
| Componentes Vue 3 + Pinia store + tipos TS | `la-03-vue3` |
| Tokens CSS Tailwind + atmosphere layer + tipografía Fraunces | `la-04-tailwind-ds` |
| Tests Feature + factories + CI gate | `la-14-testing` |
| Despliegue (cuando aplique) | `la-07-devops` |

El agente Plan despachará en paralelo donde haya independencia.

## 13. Memory hooks aplicables

- `feedback_db_safety` — solo migraciones aditivas, nada destructivo.
- `feedback_push_not_deploy` — push via bash, NO deploy automático.
- `feedback_no_npm_build` — compilar Vue local + commit `public/build/` antes de push, luego `gitpull-load` en EasyPanel.
- `feedback_npm_build_oom` — NO correr `npm run build` en EasyPanel.
- `feedback_ia_confidencial` — IA/Claude Code es info interna; coaches NUNCA deben saberlo. Reemplazar por "Equipo Estrategia WellCore" en toda UI/copy/docs visibles.
- `reference_plan_creation_system` — patrón a clonar para `SISTEMA-CREACION-MARKETING-COACHES`.
- `feedback_no_ask_permission` — ejecutar tareas autónomamente entre pasos.

## 14. Out-of-scope (Fase 2+)

- **Story Editor (canvas interactivo)** — editor visual texto-only con identidad WellCore, capas, drag, opacidad, emojis, export JPG/PNG editable. Es Subsistema C en la decomposición original; tiene su propio spec.
- **Generación automatizada por Claude API** — la Fase 1 usa Claude Code en sesión humana asistida por Daniel. Una futura fase podría correr Claude API server-side para auto-generar drops sin intervención humana (subsistema D original).
- **Plan mensual estratégico** — un layer "monthly" arriba del semanal con pilares del mes, métricas mensuales, calendario editorial. Puede sumarse como `monthly_strategic_plans` sin tocar las 3 tablas de Fase 1.
- **Análisis de competencia automatizado** — scraping/análisis de cuentas referencia del nicho del coach.
- **Notificaciones push WhatsApp** al coach cuando drop esté listo. Fase 1 solo notifica a Daniel cuando hay pending review.
- **Personalización runtime de placeholders** — si en Fase 2 mezclamos contenido global + personal, la fusión vive en la API resource, no en DB.

## 15. Glosario

| Término | Significado |
|---|---|
| **Drop** | Paquete semanal de contenido entregado a un coach. 1 fila en `coach_content_drops`. |
| **Pieza** | Unidad atómica dentro del drop: 1 reel, 1 story de un día, 1 fase del checklist. Tracked en `coach_content_piece_states`. |
| **Brand Profile / Intake** | Perfil de marca personal del coach. 1 fila en `coach_marketing_profiles`. |
| **Voice samples** | Captions reales del coach que considera "su voz al 100%". Calibran el output de Claude. |
| **`coach_drop_v1`** | Versión actual del JSON canónico. Validado server-side contra `schemas/coach_drop_v1.schema.json`. |
| **State machine** | Transiciones permitidas entre estados de un drop. Implementada en `App\Services\Marketing\DropStateMachine`. |
| **Sistema offline** | Carpeta `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-MARKETING-COACHES\` con MDs + prompt maestro. Usada por sesiones Claude Code. |
| **Equipo Estrategia WellCore** | Atribución pública de los drops. Daniel firma. Claude Code permanece confidencial. Configurable en `config/marketing.php`. |
| **Coach (modelo)** | Registro en tabla `admins` con `role = UserRole::Coach`. NO hay tabla `coaches`. FK `coach_id` siempre apunta a `admins(id)`. |
| **Auth model** | `App\Models\Admin` (extiende `Authenticatable`). Custom guard `WellCoreGuard` lee `auth_tokens`. |

## 16. Criterios de "feature completa"

La Fase 1 (+ Fase 1.5 admin mínimo) está lista cuando:

1. Las 3 tablas existen en producción (migraciones aplicadas).
2. `schemas/coach_drop_v1.schema.json` valida en server-side antes de cualquier inserción.
3. Un coach puede completar `/coach/onboarding/brand-profile` y se persiste con `completed_at`.
4. Sin perfil completo, `/coach/strategy` redirige a onboarding.
5. Daniel puede generar un drop offline (vía sistema de MDs en E:\), insertarlo a DB, y aprobarlo desde `/admin/marketing/drops/:id`.
6. Una vez aprobado, el coach lo ve en `/coach/strategy` con la dirección visual definida (Editorial Production Document).
7. El coach puede marcar piezas como publicadas; el progreso persiste.
8. El historial de drops anteriores está accesible desde `/coach/strategy?tab=historial`.
9. Todos los Feature tests de §10.3 pasan en verde.
10. La Anti-Generic Checklist de §9.8 verificada por revisión visual.
11. El sistema de MDs en `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-MARKETING-COACHES\` está creado, con los 20 MDs + prompt maestro `.txt` poblados con contenido funcional.
12. La pestaña respeta el feature flag `FEATURE_COACH_STRATEGY_ENABLED`.
