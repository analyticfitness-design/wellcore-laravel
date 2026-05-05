# Latido del Grupo — Feed de actividad agregada en Dashboard y Comunidad

> **Brainstorm date:** 2026-05-04
> **Author:** Daniel Esparza + Claude Opus 4.7
> **Status:** Awaiting user approval before transition to writing-plans
> **Companion mockups:** `.superpowers/brainstorm/13858-1777947888/content/02-propuesta-completa.html`

## Goal

Agregar visibilidad de **actividad del grupo** (compañeros del mismo coach) en dos lugares clave:

1. **Dashboard cliente** — widget `DashboardGroupPulse` con stats vivos del día y los 3 eventos más relevantes.
2. **Comunidad** — nueva tab `Latido` en `CommunityFeed.vue` con feed agregado de eventos automáticos (workouts completados, PRs rotos, milestones de racha, achievements desbloqueados), con agregación inteligente y filtros temporales.

El usuario reporta sentir el dashboard "personal y aislado" — no hay social proof inmediato. El cambio convierte cada pantalla relevante en una señal de comunidad activa.

## Estado actual relevante (review honesto)

**Lo que ya funciona bien:**

- `Dashboard.vue` tiene 14 componentes maduros: Hero, Stats, Checkin, Missions, Coach, Heatmap 90d, Weight, WeeklySummary, WeeklyGrid, Pull-to-refresh, Activity, Timeline, Fab, ProgressCollapsible.
- `CommunityFeed.vue` es robusto: posts (text/photo/achievement/pr), reacciones (like/fire/muscle/clap), comentarios, tabs all/following, PULSOS (stories 24-48h con stat overlay), real-time vía Reverb.
- `SocialController::communityIndex` ya scope por coach (`coachScopeQuery`) — el "grupo" en WellCore son los clientes del mismo coach, no global. **Esto es privacidad implícita gratis** y se respeta en el latido.
- Privacidad opt-out granular YA en `clients` table como flags fillable: `autoshare_workout`, `autoshare_pr`, `autoshare_medal`, `autoshare_weight`, `autoshare_streak`. Endpoint `PATCH /api/v/me/preferences` existe (`MeController::updatePreferences`).
- Cache patterns ya establecidos: `community:stats` 300s, `community:active-list` 180s en Redis.

**Problemas detectados que esta feature corrige:**

- `DashboardActivity.vue` solo muestra **tu propia actividad** personal repetida ("entrenaste hace 2d", "check-in semana pasada"). No hay social proof. Todo lo que muestra ya lo viste hacer. Bajo valor informativo.
- No existe ningún componente que comunique "estás en un grupo activo" — el cliente no siente el efecto comunidad antes de entrar a `/comunidad`.
- Los achievements y PRs que el sistema ya genera (`personal_records`, `client_achievements`) no se difunden — quedan en la pantalla individual del cliente sin aprovechar el efecto motivacional grupal.
- Branch `feat/community-redesign` está podrido (938 archivos, regresiones de Sprints 1-2). Lo abandonamos. El trabajo va en una rama nueva.

**Lo que NO está roto y NO tocamos:**

- Sistema PULSOS (stories) funciona — sigue intacto.
- Sistema de posts manuales — sigue intacto.
- Reverb broadcasting de likes/comentarios — sigue intacto.
- Reacciones, comentarios, follows — sin cambios.

## Architecture

```
┌────────────────────────────────────────────────────────────────────┐
│                        Dashboard.vue                               │
│  ┌─────────┐ ┌──────┐ ┌─────────────────────┐ ┌─────────┐ ┌─────┐  │
│  │  Hero   │ │Stats │ │ DashboardGroupPulse │ │ Checkin │ │ ... │  │
│  └─────────┘ └──────┘ └─────────────────────┘ └─────────┘ └─────┘  │
│                                  ▲                                 │
└──────────────────────────────────┼─────────────────────────────────┘
                                   │ GET /api/v/client/group-pulse?scope=summary
┌──────────────────────────────────┼─────────────────────────────────┐
│                    CommunityFeed.vue                               │
│  ┌─────────────┐ ┌────────┐ ┌───────┐ ┌──────────┐                 │
│  │ Tab Latido  │ │ Posts  │ │Pulsos │ │ Siguiendo│                 │
│  └──────┬──────┘ └────────┘ └───────┘ └──────────┘                 │
│         │                                                          │
│         ▼                                                          │
│  ┌──────────────────────────────────────────┐                      │
│  │  GroupPulseFeed.vue (nuevo componente)   │                      │
│  │  - filters: today/week/all + type        │                      │
│  │  - infinite scroll                        │                      │
│  │  - eventos individuales + agregados      │                      │
│  └──────────┬───────────────────────────────┘                      │
└─────────────┼──────────────────────────────────────────────────────┘
              │ GET /api/v/client/group-pulse?scope=feed&filter=...
              ▼
   ┌────────────────────────────────────────┐
   │   GroupPulseController (nuevo)         │
   │   - index(scope=summary|feed)          │
   │   - cached 30s in Redis                │
   │   - respeta autoshare_* flags          │
   │   - scoped al coach del cliente        │
   └──────┬─────────────────────────────────┘
          │ reads
          ▼
  ┌─────────────────────────────────────────┐
  │   GroupPulseAggregator (Service)        │
  │   - workouts_today, prs_week, ach_today │
  │   - top_events (PRs, racha milestones,  │
  │     achievements raros)                 │
  │   - eventos auto-agrupados (>5/h)       │
  └──┬──────┬────────────┬──────────────────┘
     │      │            │
     ▼      ▼            ▼
  workout_  personal_   client_         community_   clients (autoshare_*
  sessions  records     achievements    posts        flags filtran outputs)
```

### Components

**Backend:**

- `app/Http/Controllers/Api/GroupPulseController.php` (nuevo) — endpoint único con dos scopes: `summary` (para dashboard widget, payload pequeño) y `feed` (para tab Latido, paginado).
- `app/Services/GroupPulseAggregator.php` (nuevo) — calcula stats y eventos. Cacheado en Redis bajo `wc:group-pulse:v1:{coach_id}:{scope}` con TTL 30s para summary y 60s para feed.
- `app/Console/Commands/PrecomputeGroupPulse.php` (nuevo) — scheduled cada 5 min para precomputar agregaciones por coach (warm cache).
- Sin migraciones nuevas (cumple regla CLAUDE.md de no destructivas y aprovecha tablas existentes).

**Frontend:**

- `resources/js/vue/components/dashboard/DashboardGroupPulse.vue` (nuevo) — widget compacto.
- `resources/js/vue/components/community/GroupPulseFeed.vue` (nuevo) — feed completo con filtros e infinite scroll.
- `resources/js/vue/composables/useGroupPulse.ts` (nuevo) — fetch + cache cliente + reactive refresh.
- `resources/js/vue/pages/Client/Dashboard.vue` (modificar) — insertar `DashboardGroupPulse` entre `DashboardStats` y `DashboardCheckin`.
- `resources/js/vue/pages/Client/CommunityFeed.vue` (modificar) — agregar tab `Latido` antes de `Posts` en la barra de tabs.
- `resources/js/vue/pages/Client/ClientSettings.vue` (modificar) — agregar sección `Privacidad de actividad` con 5 toggles que enganchan a los flags `autoshare_*` existentes.

**Mejoras UX adicionales (parte de este spec, no separadas):**

- `DashboardHeatmap.vue` — agregar línea inferior `Tu promedio: X/sem · Grupo: Y/sem · ±Z%` (datos del summary).
- `DashboardMissions.vue` — agregar pill `🔥 X gente como tú está haciendo esta misión` cuando `summary.missions_peers[mission_id] > 0`.

## Data Flow

### Endpoint summary (dashboard widget)

```
GET /api/v/client/group-pulse?scope=summary
→ {
    "active_now": 37,                    // proxied from Cache 'community:active-list' lifetime
    "bpm": 84,                            // posts/min últimos 60 min × 60 (tope 180)
    "stats": {
      "workouts_today": 142,
      "prs_week": 23,
      "achievements_today": 9,
      "checkins_week": 67
    },
    "top_events": [                      // top 3, ordenados por relevancia (rareness × recency)
      {
        "type": "pr",
        "client_name": "Carlos R.",
        "client_initials": "CR",
        "headline": "rompió PR de Sentadilla 120kg",
        "delta": "+10kg sobre PR anterior",
        "minutes_ago": 8
      },
      {
        "type": "streak_milestone",
        "client_name": "María L.",
        "client_initials": "ML",
        "headline": "cumplió 30 días de racha",
        "rank_pct": 8,                   // top 8% del grupo
        "minutes_ago": 22
      },
      {
        "type": "aggregate",
        "headline": "8 personas terminaron entrenamiento en la última hora",
        "people_count": 8,
        "preview_initials": ["M","D","P","+5"],
        "extra": "2,341 kg movidos en total"
      }
    ],
    "user_vs_group": {                   // para heatmap y missions
      "weekly_workouts": { "user": 4.2, "group_avg": 3.1, "rank_pct": 18 },
      "missions_peers": { "12": 5, "13": 12 }   // mission_id → peer count
    }
}
```

### Endpoint feed (tab Latido)

```
GET /api/v/client/group-pulse?scope=feed&time=today&type=all&page=1&per_page=10
→ {
    "events": [
      { /* mismo shape que top_events, más reactions/comentarios opcionales */ }
    ],
    "pagination": { "current_page": 1, "last_page": 7, "total": 64 }
}

filtros:
  - time: today | week | all
  - type: all | pr | achievement | streak | workout
```

### Agregación lógica

`GroupPulseAggregator::buildFeed(int $coachId, string $time, string $type)`:

1. Resuelve ventana temporal (`today` = `Carbon::today()`, `week` = `subDays(7)`, `all` = `subDays(30)`).
2. Lee eventos de las 4 fuentes en paralelo:
   - `workout_sessions` donde `completed = 1` AND `client.autoshare_workout = 1`.
   - `personal_records` donde `is_current = 1` AND `client.autoshare_pr = 1`.
   - `client_achievements` donde `client.autoshare_medal = 1`.
   - `community_posts` donde `post_type IN ('achievement','pr')` (legacy posts manuales).
3. Filtra por coach (join `clients.coach_id = $coachId`).
4. Aplica heurística de rareness:
   - PRs siempre individuales (alta señal).
   - Achievements raros (< 5% del grupo lo tiene): individual.
   - Achievements comunes: agregar.
   - Workouts: si > 5 en última hora, agregar; si <=5, individual.
   - Streak milestones (7, 30, 100, 365): siempre individual.
5. Ordena por `minutes_ago ASC` con peso `pr × 3 + streak_milestone × 2.5 + rare_achievement × 2 + common × 1`.
6. Pagina y retorna.

## Privacy & Filtering

- **Scope-by-coach** — un cliente solo ve actividad de clientes con su mismo `coach_id`. Esto es la zona de seguridad implícita.
- **Per-event-type opt-out** — los flags `autoshare_*` en `clients` filtran ANTES de agregar:
  - `autoshare_workout = 0` → mis entrenos no aparecen en el feed (ni míos ni para los demás).
  - `autoshare_pr = 0` → mis PRs no aparecen.
  - `autoshare_medal = 0` → mis logros no aparecen.
  - `autoshare_streak = 0` → mis milestones de racha no aparecen.
  - `autoshare_weight = 0` → ya existe pero no aplica al latido (sin eventos de peso).
- **Display** — siempre `nombre + inicial apellido` (ej "Carlos R."), nunca email ni full last name.
- **Default** — todos los flags están a `1` (visible). Cambiar default rompería retro-compat con vanilla PHP.
- **Settings UI** — sección nueva en `ClientSettings.vue` con 5 toggles, hookea endpoint `PATCH /api/v/me/preferences` (ya existe).

## Cache Strategy

| Key | TTL | Computed by |
|-----|-----|-------------|
| `wc:group-pulse:v1:{coach_id}:summary` | 30s | request-time miss → aggregator + scheduled command warm |
| `wc:group-pulse:v1:{coach_id}:feed:{time}:{type}:{page}` | 60s | request-time miss only |
| `community:stats` | 300s (existente, sin cambio) | mantener |
| `community:active-list` | 180s (existente, sin cambio) | mantener |

Scheduled command `wellcore:precompute-group-pulse` corre cada 5 min y refresca summary key para todos los coaches activos. Costo: ~1 query agregada por coach × N coaches = bajo.

Bust-cache no manual: en MVP confiamos en TTL. Eventos NO invalidan cache (sería caro). Aceptamos latencia de hasta 30s en summary widget — UX-acceptable porque el heartbeat es estético, no transaccional.

## Real-time

**Out of scope para MVP**: NO broadcast por evento individual (saturaría Reverb). El widget se refresca:

- Pull al cargar dashboard (ya hay `fetchDashboard`, agregamos call paralela).
- Pull cada 60s mientras dashboard esté activo (timer existente).
- Pull on pull-to-refresh (ya implementado).

Future: si engagement justifica latencia, considerar broadcasting `GroupPulseUpdated` event en tasa máxima 1/min vía Reverb.

## Error Handling

- Si Redis no responde → endpoint cae a query directa con timeout 2s, loggea warning a `Log::channel('cache')`.
- Si query agregada > 2s → log a Sentry (canal performance), retornar payload parcial (`stats` sin `top_events`).
- Si cliente no tiene coach asignado → endpoint retorna `204 No Content` y widget se oculta gracefully (`v-if="data && data.stats"`).
- Frontend timeout: 5s en `useGroupPulse`. En timeout, widget muestra estado vacío `Sin datos del grupo aún` en lugar de error rojo (no bloquea dashboard).
- Errores 5xx no rompen `Dashboard.vue` — el widget se monta dentro de `v-if` con catch propio.

## Testing Strategy

**Unit (Pest):**
- `GroupPulseAggregator::computeStats` con fixtures workout_sessions, prs, achievements.
- `GroupPulseAggregator::buildFeed` con cada filtro `time/type`.
- `GroupPulseAggregator::aggregateThreshold` (>5 eventos similares en 1h → agregado).
- Filtrado por flags `autoshare_*`: cliente con `autoshare_pr = 0` no aparece.
- Scope por `coach_id`: clientes de coach B no contaminan feed de coach A.

**Feature (Pest):**
- `GroupPulseEndpointTest::summary_returns_expected_shape`.
- `GroupPulseEndpointTest::feed_paginates_correctly`.
- `GroupPulseEndpointTest::respects_autoshare_flags`.
- `GroupPulseEndpointTest::scoped_to_clients_coach`.
- `GroupPulseEndpointTest::cache_hit_returns_in_under_50ms` (con cache warmed).
- `GroupPulseEndpointTest::client_without_coach_returns_204`.

**E2E (Pest browser, opcional MVP):**
- Dashboard carga y widget aparece.
- Tab Latido en comunidad muestra eventos.
- Toggle privacidad en Settings se persiste.

## What's NOT in MVP (YAGNI)

Deliberadamente fuera de alcance, candidatos a sprint posterior:

- Push notifications de eventos del grupo (toggle ya existe en Settings, pero pipeline `PushNotificationService` no se gatilla por estos eventos).
- Algoritmo de "miembros similares a ti" (necesita cluster scoring que no tenemos).
- Coach view del latido por programa (el coach ya ve admin dashboard).
- Anonymous mode toggle por evento individual (sobre-engineering hasta validar uso).
- Gamificación con badges sociales ("Top motivador del mes").
- Filtro `solo PRs` o `solo Logros` con UI dedicada — el spec describe shape backend `type=pr|achievement` pero la UI MVP solo muestra `time` filter; los chips `type` quedan deshabilitados con tooltip "Próximamente".
- Comparativa "tú vs grupo" en `DashboardWeight.vue` (existe el dato `user_vs_group.weekly_workouts` pero el peso no, requiere otra agregación).
- Respeto de "blocked users" — SP-4 no implementó bloqueo, queda fuera.
- Real-time broadcast por evento individual.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| Query agregada lenta en coach con 200+ clientes | Scheduled precompute + Redis cache 30s + LIMIT en lecturas raw |
| Privacy leak (un cliente ve actividad de otro coach) | Test específico `scoped_to_clients_coach` + revisión policy en code review |
| Widget aumenta TTI del dashboard | Lazy fetch separado de `fetchDashboard`, widget renderiza skeleton primero |
| Eventos auto-publicados se sienten invasivos | Default opt-OUT global toggle visible en Settings desde día 1 |
| Branch podrido `feat/community-redesign` causa conflictos | Trabajar en rama nueva `feat/group-pulse` desde main, ignorar la podrida |

## Definition of Done

- [ ] `GroupPulseController` con `index(scope=summary|feed)` retorna shape acordado.
- [ ] `GroupPulseAggregator` agrega de las 4 fuentes con respeto a `autoshare_*` flags y scope coach.
- [ ] Cache Redis con TTL acordado y scheduled command `wellcore:precompute-group-pulse`.
- [ ] `DashboardGroupPulse.vue` renderiza summary + heartbeat animado + 3 top events. Respeta `prefers-reduced-motion`.
- [ ] `GroupPulseFeed.vue` renderiza feed paginado con infinite scroll + filtros temporales (`time`).
- [ ] Tab `Latido` activa/desactiva en `CommunityFeed.vue` con misma estética que tabs existentes.
- [ ] Sección `Privacidad de actividad` en `ClientSettings.vue` con 5 toggles + persistencia vía endpoint existente.
- [ ] `DashboardHeatmap.vue` muestra promedio grupo + ranking pct.
- [ ] `DashboardMissions.vue` muestra peer count por misión.
- [ ] Tests Pest verdes: 6 unit + 6 feature.
- [ ] No regresión en Lighthouse: dashboard performance ≥ 70.
- [ ] Smoke test prod manual con Chrome DevTools en `/dashboard` y `/comunidad`.
- [ ] Build local + commit `public/build/` + push + gitpull-load (memoria flujo deploy).

## Branch & Commit Plan

- Crear rama `feat/group-pulse` desde `main` (NO desde `feat/community-redesign`).
- Una commit por capa: migrations skip, models skip (no nuevos), service, controller, command, frontend widget, frontend feed, settings, tests.
- PR a `main` con descripción de Definition of Done marcada.

## Open Questions for User

Ninguna — decisiones autónomas tomadas y justificadas en cada sección. Si algo no cuadra, indica qué cambiar y reviso el spec antes de transición a writing-plans.
