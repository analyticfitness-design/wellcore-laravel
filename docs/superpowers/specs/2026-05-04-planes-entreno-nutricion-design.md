# Diseño · Planes ENTRENO + NUTRICIÓN (vertical única)

**Fecha:** 2026-05-04
**Autor:** Daniel Esparza + Claude
**Estado:** Diseñado, pendiente de plan de implementación
**Sub-proyecto sucesor:** `writing-plans` después de aprobación

---

## 1. Resumen ejecutivo

Agregar 2 planes nuevos a la oferta WellCore: **ENTRENO** (solo entrenamiento, $200.000 COP/mes original — $170.000 con promo Mayo -15%) y **NUTRICIÓN** (solo plan nutricional, $180.000 COP/mes original — $153.000 con promo). Ambos llevan coach humano con ajuste mensual (mismo nivel de servicio que Esencial), pero cubren UNA sola vertical.

Posicionamiento: **puerta de entrada** para clientes que ya tienen una vertical resuelta o quieren probar el sistema con menor compromiso. Diseñados para incentivar upsell natural a Esencial ($254k → solo $84k más por la suite completa).

El alcance cubre 4 superficies:
1. **Página pública `/planes`** — sección nueva "Planes simples" con 2 cards
2. **Flujo de checkout `/pagar`** — los nuevos slugs entran como opciones válidas
3. **Dashboard del cliente `/client/plan`** — tabs bloqueadas con upsell educativo según plan
4. **Sistema de creación de planes** (`E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\`) — MDs actualizados para que sesiones futuras de Claude Code sepan crear planes de vertical única

---

## 2. Decisiones del brainstorm

| # | Pregunta | Elección |
|---|----------|----------|
| 1 | Posicionamiento en `/planes` | Sección APARTE (no integrados en grilla de 3) |
| 2 | Naming UI | **ENTRENO** / **NUTRICIÓN** (mayúsculas brutalistas) |
| 3 | Acompañamiento | Coach humano + ajuste mensual (igual que Esencial pero 1 vertical) |
| 4 | Períodos | Mensual / Trimestral (-10%) / Anual (-20%) — mismas mecánicas que los 3 actuales |
| 5 | Ubicación | DESPUÉS de TierCards, ANTES del Comparador |
| 6 | Comparador (tabla) | NO aparecen — el comparador queda solo con los 3 grandes |
| 7 | Estilo visual de cards | Cards "hermanas" en par, más livianas, grid 2-cols, sin scroll-snap |
| 8 | Slug DB (`clients.plan`) | `entreno_solo` y `nutricion_solo` (simétrico, evita colisión con `assigned_plans.plan_type='nutricion'`) |
| 9 | Migration | Asumir `clients.plan` es VARCHAR; si es ENUM, hotfix con migration aditiva |
| 10 | Bug precios InscriptionForm.vue | NO arreglar — fuera de alcance, queda para otra PR |
| 11 | UX tabs bloqueadas | Educativo: overlay con CTA "Suma con Esencial desde $X/mes más" |

---

## 3. Naming canónico

### En DB

| Columna | Valor canónico | Significado |
|---------|----------------|-------------|
| `clients.plan` | `entreno_solo` | Tier comercial: cliente compró plan vertical entrenamiento |
| `clients.plan` | `nutricion_solo` | Tier comercial: cliente compró plan vertical nutrición |
| `assigned_plans.plan_type` | `entrenamiento` | Contenido del plan (sin cambios — sigue siendo el mismo string que esencial/metodo/elite usan) |
| `assigned_plans.plan_type` | `nutricion` | Contenido del plan (sin cambios) |

**Regla crítica:** un cliente con `clients.plan = 'entreno_solo'` recibe **1 fila** en `assigned_plans` (con `plan_type='entrenamiento'`). NO se le crea fila de nutrición. Lo mismo para `nutricion_solo` → 1 fila con `plan_type='nutricion'`.

### En config y lang

```php
// config/plans.php — keys
'entreno_solo' => [...]
'nutricion_solo' => [...]

// lang/es/planes.php — claves
'entreno_name'    => 'ENTRENO'
'nutricion_name'  => 'NUTRICIÓN'
```

### En enum PHP

```php
// app/Enums/PlanType.php
case EntrenoSolo = 'entreno_solo';
case NutricionSolo = 'nutricion_solo';
```

---

## 4. Pricing

### Precios mensuales

| Plan | Original | Con promo Mayo (-15%) | USD original | USD promo |
|------|----------:|----------------------:|-------------:|----------:|
| ENTRENO | $200.000 COP | **$170.000 COP/mes** | $49 | $42 |
| NUTRICIÓN | $180.000 COP | **$153.000 COP/mes** | $44 | $37 |

### Períodos (descuentos aplicados en `PlanesController`, igual que los 3 grandes)

| Plan | Mensual | Trimestral (-10%) | Anual (-20%) |
|------|--------:|------------------:|--------------:|
| ENTRENO | $170.000 | $153.000/mes ($459k total) | $136.000/mes ($1.632k total) |
| NUTRICIÓN | $153.000 | $137.700/mes ($413k total) | $122.400/mes ($1.469k total) |

---

## 5. Copy y características

### ENTRENO

**Quote:** *"Para quien tiene la nutrición resuelta y solo necesita un plan de entrenamiento que se ajuste cada mes con tu coach."*

**3 pilares:**
1. *Entrenamiento personalizado · ejercicios con video o demostración, registro de pesos, récords automáticos*
2. *Tu coach humano ajusta el plan cada mes · revisa tu check-in y adapta volumen, ejercicios y progresiones*
3. *Acceso completo a la plataforma · Voice Logger, variaciones, comunidad, misiones diarias y XP*

### NUTRICIÓN

**Quote:** *"Para quien ya entrena bien y necesita una estrategia nutricional con macros, plan de comidas y ajuste mensual."*

**3 pilares:**
1. *Nutrición 100% personalizada · macros, plan de comidas con 3 opciones por plato, agua diaria*
2. *Tu coach humano ajusta el plan cada mes · revisa tu check-in y adapta calorías, macros y horarios*
3. *Acceso completo a la plataforma · 3 opciones por plato, agua diaria, comunidad y XP*

> **Nota:** la pillar 3 de NUTRICIÓN explícitamente NO menciona "suplementación con horarios" porque ese protocolo está reservado a Esencial+ (es uno de los upsell levers). Si en el futuro se decide incluir un protocolo simplificado, actualizar copy + matriz §6.4.1 simultáneamente.

---

## 6. Cambios técnicos — radiografía completa

### 6.1 Backend / config

| # | Archivo | Línea(s) | Cambio |
|---|---------|----------:|--------|
| 1 | `config/plans.php` | — | Agregar entradas `entreno_solo` y `nutricion_solo` (price_cop, price_cop_original, price_usd, price_usd_original, name, desc, includes, features_count). El campo `tier => 'simple'` es **informativo** (clasificación en admin/analytics); no se usa en lógica de runtime — la lógica de acceso vive en `PlanViewer.vue` (matriz §6.4.1) y `PlanLockService` |
| 2 | `app/Enums/PlanType.php` | 7-23 | Agregar `EntrenoSolo` y `NutricionSolo` cases + actualizar `label()` con "Entreno" y "Nutrición" |
| 3 | `app/Services/PricingService.php` | 9 | `BILLABLE_PLANS` extender: `['esencial', 'metodo', 'elite', 'entreno_solo', 'nutricion_solo', 'rise']` |
| 4 | `app/Http/Controllers/Public/PlanesController.php` | 21-73 | Diferenciar `$plansComplete` vs `$plansSimple`; build prices/totals/savings para los 5 planes; pasar ambos arrays a la vista |
| 5 | `app/Services/PlanLockService.php` | 55 | `isMonthlyPlan()` aceptar `entreno_solo` y `nutricion_solo` (también son mensuales con expires_at +30d) |
| 6 | `app/Console/Commands/AutoRenewalCommand.php` | 60 | `$monthlyPlans` extender con los 2 nuevos |

### 6.2 Frontend público

| # | Archivo | Cambio |
|---|---------|--------|
| 7 | `resources/views/public/planes.blade.php` | Insertar `<section class="tiers-simple">` justo después de `</section>` de TierCards (línea ~187) y antes del divider del Comparador. Incluye: divider eyebrow `PLANES SIMPLES · UNA VERTICAL`, h2 + sub, grid 2-cols con 2 cards (ENTRENO / NUTRICIÓN). Cada card lee `pricesCop['entreno_solo']` etc. Las cards reaccionan al `period` global del Alpine root. CTA → `/pagar?plan={slug}&period={period}` |
| 8 | `resources/css/v2-public.css` (mismo archivo que `.t-card`, `.tier-track`) | Agregar bloque `/* === TIERS SIMPLES (entreno/nutricion) === */` con: `.tiers-simple` (section wrapper), `.tiers-simple-divider`, `.tiers-simple-grid` (grid 2-cols desktop, 1-col mobile, gap consistente con tier-track), `.t-card-simple` (variante card más compacta — sin badge, sin metric ring, padding reducido), `.t-card-simple-name`, `.t-card-simple-eyebrow` (label "ENTRENO" o "NUTRICIÓN" como categoría) |
| 9 | `lang/es/planes.php` | Claves: `entreno_name`, `entreno_desc`, `entreno_quote`, `entreno_pillars[]`, `entreno_cta`, `nutricion_name`, `nutricion_desc`, `nutricion_quote`, `nutricion_pillars[]`, `nutricion_cta`, `simple_section_eyebrow`, `simple_section_h2`, `simple_section_sub` |
| 10 | `lang/en/planes.php` | Mismo set de claves traducidas |
| 11 | JSON-LD en `planes.blade.php` (línea ~17-19) | Agregar 2 entradas más al `OfferCatalog.itemListElement` |
| 12 | Sticky CTA bottom mobile (`planName()` en Alpine, línea ~64-67) | Extender el map con `entreno_solo` y `nutricion_solo` para que muestre nombre correcto |

### 6.3 Checkout y formularios

| # | Archivo | Línea(s) | Cambio |
|---|---------|----------:|--------|
| 13 | `app/Livewire/Checkout.php` | 77 | `getPlans()` foreach: agregar `'entreno_solo'`, `'nutricion_solo'` |
| 14 | `app/Livewire/Checkout.php` | 128 | `prefillFromAuthenticatedClient()`: extender array de planes válidos para renovación |
| 15 | `app/Livewire/InscriptionForm.php` | 72 | Validación `'plan'`: `required\|in:esencial,metodo,elite,entreno_solo,nutricion_solo` |
| 16 | `resources/js/vue/pages/Public/InscriptionForm.vue` | 81-103 | Agregar 2 entradas al array `plans` con sus features. **Nota:** los precios hardcodeados existentes están desactualizados (out-of-scope arreglar — ver §10). Para los 2 nuevos, usar precios actuales correctos para no agregar más bug |
| 17 | `resources/js/vue/pages/Public/InscriptionForm.vue` | ~106-120 (`stepOrder`) | Lógica condicional: si plan === `entreno_solo`, omitir Step 5 (Nutrición). Si plan === `nutricion_solo`, omitir Steps 2-4 (Experiencia, Preferencias, Lesiones) |
| 18 | `app/Http/Controllers/Api/PublicFormController.php` | 44 | Validación `'plan' => 'required\|in:esencial,metodo,elite,entreno_solo,nutricion_solo'` |
| 19 | `app/Http/Controllers/Api/AdminController.php` | 1991, 3032, 3096 | Validaciones admin: agregar `entreno_solo`, `nutricion_solo` al `in:` |
| 20 | `app/Livewire/Admin/InvitationManager.php` | 71 | Validación `newPlan` extender |
| 21 | `app/Livewire/Admin/SendPlanInvitation.php` | 68 | Validación `selectedPlan` extender |

### 6.4 Dashboard cliente

| # | Archivo | Línea(s) | Cambio |
|---|---------|----------:|--------|
| 22 | `resources/js/vue/pages/Client/PlanViewer.vue` | 365-372 | El array `tabs` queda igual (mismas 6 tabs) |
| 23 | `PlanViewer.vue` | 374-376 | `canAccessNutricion` extender: incluir `nutricion_solo`, EXCLUIR `entreno_solo` |
| 24 | `PlanViewer.vue` | nuevo | Crear `canAccessEntrenamiento` computed: incluir todos los planes EXCEPTO `nutricion_solo` (que solo accede a nutrición) |
| 25 | `PlanViewer.vue` | nuevo | Crear `canAccessHabitos`, `canAccessSuplementacion` computed: NO disponibles para `entreno_solo` ni `nutricion_solo` |
| 26 | `PlanViewer.vue` | 382-386 | Reescribir `isTabLocked(key)` con la matriz completa (ver §6.4.1) |
| 27 | `PlanViewer.vue` | 414-417 | `planTypeLabel` map: agregar `'entreno_solo': 'Entreno'`, `'nutricion_solo': 'Nutrición'` |
| 28 | `PlanViewer.vue` | nuevo | Componente `<TabLockUpsell>` inline o sub-component: card que aparece dentro del contenido de cada tab bloqueada en lugar del contenido normal. Copy: *"Tu plan {ENTRENO/NUTRICIÓN} no incluye {esta sección}. Súmala con Plan Esencial desde $84.000/mes más."* + botón "Ver Esencial" → link a `/planes#tier-cards` |
| 29 | `PlanViewer.vue` | función `setTab` línea 388-395 | Permitir que tabs bloqueadas SE PUEDAN seleccionar (para que muestren el upsell), pero el contenido renderizado dentro de la tab condiciona en `isTabLocked` y muestra el TabLockUpsell |

#### 6.4.1 Matriz de acceso a tabs por plan

| `clients.plan` | entrenamiento | habitos | nutricion | suplementacion | ciclo | bloodwork |
|---------------|:---:|:---:|:---:|:---:|:---:|:---:|
| `esencial` | ✅ | ✅ | ✅ | ✅ | 🔒 | 🔒 |
| `metodo` | ✅ | ✅ | ✅ | ✅ | 🔒 | 🔒 |
| `elite` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `entreno_solo` ⭐ | ✅ | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 |
| `nutricion_solo` ⭐ | 🔒 | 🔒 | ✅ | 🔒 | 🔒 | 🔒 |
| `rise` | ✅ | ✅ | ✅ | ✅ | 🔒 | 🔒 |
| `presencial` | ✅ | ✅ | ✅ | ✅ | 🔒 | 🔒 |
| `trial` | ✅ | 🔒 | 🔒 | 🔒 | 🔒 | 🔒 |

### 6.5 Otros

| # | Archivo | Cambio |
|---|---------|--------|
| 30 | `app/Services/PlanTicketExportService.php` | Línea 355: si los archivos `plan-esencial.md` etc. existen físicamente y se usan al generar tickets de coach, agregar `plan-entreno-solo.md` y `plan-nutricion-solo.md` con guías mínimas para coaches. Si solo es metadata sin archivos asociados, agregar las claves correspondientes. **Verificar antes** que no se rompa la generación de tickets si los archivos no existen |
| 31 | Migration aditiva (condicional) | Si `SHOW CREATE TABLE clients` muestra que `plan` es ENUM, crear migration `ALTER TABLE clients MODIFY COLUMN plan ENUM('esencial','metodo','elite','rise','presencial','trial','entreno_solo','nutricion_solo')`. Si es VARCHAR, no se necesita migration |
| 32 | Memory file `~/.claude/projects/.../memory/reference_plan_creation_system.md` | Actualizar para reflejar nuevos tipos |

---

## 7. Actualización del SISTEMA-CREACION-PLANES (E:\)

Esto es **crítico** para que sesiones futuras de Claude Code creen planes de vertical única sin improvisar.

### 7.1 Archivos a EDITAR

**`00-INDEX.md`**
- Tabla *"TIPOS DE `plan_type` EN `assigned_plans`"* — agregar nota desambiguando `clients.plan` (tier comercial) vs `assigned_plans.plan_type` (tipo de contenido). Aclarar que ambos tipos pueden tener el string `nutricion`/`entrenamiento` pero significan cosas distintas.
- *"ORDEN DE LECTURA POR TIPO DE PLAN"* — agregar 2 secciones:
  - **Plan ENTRENO solo (vertical única)** — leer 28 + 16a + 22 + 17 + 11 + 18 + 09
  - **Plan NUTRICIÓN solo (vertical única)** — leer 28 + 16b + 22 + 17 + 12 + 18 + 09
- Agregar referencia al nuevo MD 28 en BLOQUE A.

**`04-REGLAS-POR-TIPO-DE-PLAN.md`**
- Tabla *"MAPPING OFICIAL `plan_type` → LABEL"* — agregar:
  - `entreno_solo` → **Entreno**
  - `nutricion_solo` → **Nutrición**
- Tabla *"DURACIONES OFICIALES"*:
  - `entreno_solo` — 4-8 semanas (default 4) — fases tipo Esencial
  - `nutricion_solo` — 4-8 semanas (default 4) — sin fases de entrenamiento
- Tabla resumen *"TIPO / DURACIÓN / HTML / REGISTRO / NOTIFICACIONES"* — agregar 2 filas
- Nuevas secciones:
  - **"7. PLAN ENTRENO SOLO"** — intake mínimo, qué crear, qué NO crear, diferencias con combinado, cómo redactar el coach message para incentivar upsell
  - **"8. PLAN NUTRICIÓN SOLO"** — idem
- Tabla *"REGLA MAESTRA — IDENTIFICAR EL TIPO"* — agregar frases gatillo:
  - *"plan de entrenamiento solo"*, *"solo entreno"*, *"sin nutrición"*, *"plan vertical entrenamiento"* → `entreno_solo`
  - *"plan de nutrición solo"*, *"solo dieta"*, *"solo macros"*, *"plan vertical nutrición"* → `nutricion_solo`

**`23-NAMING-CANONICO-Y-ALIAS.md`**
- Tabla del campo `plan_type` (root del JSON) — actualizar enum:
  - Antes: `esencial`, `metodo`, `elite`, `rise`, `presencial`, `trial`
  - Después: `esencial`, `metodo`, `elite`, `entreno_solo`, `nutricion_solo`, `rise`, `presencial`, `trial`
- Aclarar la nota sobre dos namespaces (`clients.plan` vs `assigned_plans.plan_type`).

**`01-PASO-A-PASO.md`**
- FASE 0 *"Tipo de plan solicitado"* — opciones aceptadas: agregar **Entreno solo / Nutrición solo**
- FASE 1 tabla *"Según tipo de plan"* — 2 filas nuevas con MDs específicos
- FASE 4.5 — agregar nota: *"Si es plan de vertical única (`entreno_solo` o `nutricion_solo`), solo se crea **1 fila** en `assigned_plans`, no 2. Marcar planes previos del mismo `plan_type` como inactive."*

### 7.2 Archivo a CREAR

**`28-PLANES-VERTICAL-UNICA.md`** — documento dedicado.

Contenido obligatorio:
1. **Qué son** — definición + posicionamiento comercial
2. **Cuándo aplican** — perfiles de cliente que se benefician
3. **Mapping crítico** (tabla):
   ```
   clients.plan = 'entreno_solo'    → 1 fila assigned_plans con plan_type='entrenamiento'
   clients.plan = 'nutricion_solo'  → 1 fila assigned_plans con plan_type='nutricion'
   ```
4. **Intake reducido** — qué SÍ y qué NO preguntar:
   - `entreno_solo`: omite alergias, presupuesto suplementos, horario laboral, dieta_actual
   - `nutricion_solo`: omite días disponibles, lugar, equipamiento, lesiones (a menos que afecten dietas — ej. cirugía bariátrica, gastritis crónica)
5. **Tabs que ve el cliente en `/client/plan`** — referencia a la matriz §6.4.1
6. **Upsell** — copy sugerido para los lock states
7. **Notificaciones** — frecuencia y triggers ajustados:
   - `entreno_solo`: solo notificaciones de entrenamiento, no enviar tips de comida ni recordatorios de macros
   - `nutricion_solo`: solo notificaciones nutricionales, no enviar recordatorios de cardio o entrenamiento
8. **Diferencias clave con combinado** — tabla side-by-side
9. **NO HACER** — checklist de errores comunes:
   - ❌ Crear 2 filas en `assigned_plans` (solo 1)
   - ❌ Incluir suplementación si no la pagó
   - ❌ Mezclar tier comercial con contenido en queries
   - ❌ Generar coach message que mencione la vertical no contratada
10. **Templates JSON copy-paste** — uno mínimo viable para cada vertical única

### 7.3 Updates colaterales

- **Memory** (`~/.claude/projects/.../memory/reference_plan_creation_system.md`) — actualizar con los nuevos tipos disponibles.
- **Memory** — crear nuevo entry `project_planes_vertical_unica.md` documentando que estos planes existen, fecha de lanzamiento, y que el SISTEMA-CREACION-PLANES ya está actualizado para soportarlos.

---

## 8. Riesgos y mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| `clients.plan` es ENUM en prod y rompe al insertar | Media | Alto (login fail post-payment) | Inspeccionar con `SHOW CREATE TABLE clients` antes de cualquier deploy. Si es ENUM, migration aditiva (no destructiva) primero. |
| Canibalización de Esencial (clientes que toman ENTRENO en vez de Esencial completo) | Media | Medio | Copy diseñado para upsell. Ofrecer cambio de plan en cualquier momento desde dashboard. Monitorear conversión Esencial post-launch. |
| Cliente con `entreno_solo` pide nutrición al coach (presión social) | Alta | Bajo | Coach training: explicar al cliente que su plan no la incluye, ofrecer upgrade. Agregar nota en panel coach sobre el plan que tiene asignado. |
| Confusión de slugs: dev escribe `entreno` o `solo_entreno` por inercia | Media | Medio | Constants en código + tests de validación + esta spec como referencia. |
| Inscripciones via API antiguas con plan=`elite` no validan al ser ahora más estrictas | Baja | Bajo | El array `in:esencial,metodo,elite,entreno_solo,nutricion_solo` es **superset**. Inscripciones existentes siguen funcionando. |
| Hábitos NO disponibles en planes solo entreno/nutrición rompe alguna lógica de gamificación | Media | Medio | Verificar que `HabitLog` no asume que TODO cliente tiene plan de hábitos. Si lo asume, condicional `if (canAccessHabitos)` antes de crear logs. |

---

## 9. Out of scope (declarado explícitamente)

Para mantener foco y reducir riesgo de regresiones:

1. **NO arreglar los precios hardcodeados en `InscriptionForm.vue`** (líneas 83-103) — los precios actuales de los 3 planes ($149,900 / $249,900 / $399,900) están desactualizados pero el bug existe ya y es independiente de este trabajo. Se trackea para PR posterior.
2. **NO crear página de comparación entreno vs nutrición** — los pillars de cada card más el copy de la sección son suficientes.
3. **NO agregar testimonios para los 2 nuevos planes** — aún no existen clientes en estos planes (no se han lanzado). Se agregan testimonios cuando haya ≥3 casos reales.
4. **NO modificar el comparador (tabla)** — los 2 nuevos planes NO aparecen ahí (decisión 6 del brainstorm).
5. **NO cambiar el CTA Final** — sigue apuntando a Método (es el plan estrella).
6. **NO crear tests automatizados nuevos** en este alcance — se agregan en plan separado de testing una vez el flow está estable.
7. **NO migrar Checkout Livewire a Vue** aunque ya esté planificado para el futuro (ver `project_vue_migration.md`). Mantener Livewire por ahora.

---

## 10. Acceptance criteria

El trabajo está listo cuando:

### Página `/planes`
- [ ] Las 2 cards nuevas aparecen entre TierCards y Comparador, en grid 2-cols (desktop) y 1-col stacked (mobile)
- [ ] Los precios reaccionan correctamente al toggle global (mensual/trim/anual) con descuentos -10/-20%
- [ ] CTAs llevan a `/pagar?plan=entreno_solo&period=...` y `/pagar?plan=nutricion_solo&period=...`
- [ ] Lighthouse a11y/BP/SEO siguen en ≥95 (no regresión)
- [ ] JSON-LD `OfferCatalog` incluye los 5 planes
- [ ] Sticky CTA bottom mobile muestra nombre correcto cuando se selecciona ENTRENO o NUTRICIÓN

### Checkout `/pagar`
- [ ] Aceptar `?plan=entreno_solo` y `?plan=nutricion_solo` sin error
- [ ] Step 2 valida correctamente
- [ ] Wompi widget se prepara con el monto correcto
- [ ] Payment record se crea con `plan='entreno_solo'` o `'nutricion_solo'`

### Inscripción `/inscripcion`
- [ ] InscriptionForm.vue muestra las 5 opciones de plan en Step 0
- [ ] Si elige `entreno_solo`, salta Step 5 (Nutrición)
- [ ] Si elige `nutricion_solo`, salta Steps 2-4
- [ ] POST /api/public/inscription valida y persiste correctamente

### Dashboard `/client/plan`
- [ ] Cliente con `entreno_solo`: solo "Entrenamiento" desbloqueada, todas las demás 🔒
- [ ] Cliente con `nutricion_solo`: solo "Nutrición" desbloqueada, todas las demás 🔒
- [ ] Click en tab bloqueada muestra `<TabLockUpsell>` con CTA correcto
- [ ] Header dinámico muestra "Plan Entreno · Fase X · N semanas" o "Plan Nutrición · ..."
- [ ] PlanLockService aplica reglas de expires_at correctamente para los 2 nuevos planes (mensuales, lock a los 30 días)

### Auto-renovación
- [ ] AutoRenewalCommand procesa correctamente los planes `entreno_solo` y `nutricion_solo`

### Sistema-Creacion-Planes (E:\)
- [ ] MD 28 nuevo creado con contenido completo
- [ ] MDs 00, 01, 04, 23 actualizados
- [ ] Memory file actualizado
- [ ] Una sesión nueva de Claude Code que reciba *"crea plan de entrenamiento solo para cliente X"* genera correctamente: 1 fila en assigned_plans, intake reducido, sin contaminar verticales no contratadas

### Validación funcional
- [ ] Crear cliente test con `entreno_solo` desde admin → asignar plan entrenamiento → verificar UI cliente
- [ ] Crear cliente test con `nutricion_solo` desde admin → asignar plan nutrición → verificar UI cliente
- [ ] Renovación de cliente `entreno_solo` desde `/renovar` funciona
- [ ] **HabitLog check**: verificar que cliente con `entreno_solo` o `nutricion_solo` NO recibe error al loguearse en dashboard si no tiene plan de hábitos asignado. Si la UI o el backend asume que existe `assigned_plan` con `plan_type='habitos'`, agregar guard `if ($client->canAccessHabitos())` antes de cualquier query a `HabitLog`

---

## 11. Open questions / future work

1. **¿Hay reglas de antigüedad para upgrades?** — Si un cliente lleva 3 meses en `entreno_solo` y quiere subir a Esencial, ¿se le aplica algún descuento de fidelidad? Decidir en sprint 2.
2. **¿RISE-like badge para los planes simples?** — En el futuro, si los planes simples generan suficiente volumen, podrían tener su propio "tier visual" (ej. badge gris "ENFOCADO"). Por ahora ghost.
3. **Email transaccional post-compra** — verificar que el template de bienvenida no asume que el cliente tiene todas las verticales. Probablemente requiera 2 templates extra (`welcome-entreno-solo`, `welcome-nutricion-solo`). Tracker en sprint 2.
4. **AIPlanGenerator del admin** — si se usa, hay que enseñarlo a generar planes de vertical única (prompt update). Por ahora se sigue el flujo de creación manual via Claude Code.

---

## 12. Approval

Este spec se considera aprobado cuando Daniel responde con **"aprobado"** o equivalente. Después se invoca `superpowers:writing-plans` para crear el plan de implementación detallado.
