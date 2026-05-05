# FASE 0 — Pre-flight Report · Food Tracking

**Fecha:** 2026-05-04
**Ejecutado por:** Claude (Opus 4.7) antes de implementación
**Conclusión:** El spec original (`2026-05-04-food-tracking-design.md`) tiene **3 bloqueos estructurales** que requieren decisión del usuario antes de codear. Todo lo demás del spec es viable con ajustes menores.

---

## 1. Bloqueos estructurales (decisión requerida)

### B-01 · `habit_logs.habit_type` es **ENUM cerrado** — bloquea `food_day_bonus`

Schema real de la DB **local**:

```sql
`habit_type` enum('agua','sueno','entrenamiento','nutricion','suplementos','estres')
  COLLATE utf8mb4_unicode_ci NOT NULL
```

⚠ **El schema verificado es LOCAL, NO producción.** Memoria `feedback_clients_plan_enum_prod` registra que prod ya divergió antes con `clients.plan`. Antes de cualquier ALTER en prod hay que verificar el schema real allá.

El spec proponía `HabitLog::firstOrCreate(['habit_type' => 'food_day_bonus', ...])` para idempotencia del bonus diario. **No funciona** sin migración aditiva.

**Opciones (en orden de riesgo creciente):**

| Opción | Riesgo a otras funcionalidades | Pros | Contras |
|---|---|---|---|
| **C. Columna en `food_photos`** — `bonus_awarded_for_date` flag, idempotencia via UNIQUE constraint sobre `food_photos` | **Cero** — solo tabla nueva | Aislamiento total. No toca habit_logs. | Lógica de "bonus diario" no vive con resto de habits. |
| **B. Tabla nueva** — `food_day_bonuses(client_id, date, awarded_at)` con UNIQUE `(client_id,date)` | **Cero** — solo tabla nueva | No toca ENUM compartido. App vanilla nunca lee esta tabla nueva. | Una tabla más en el schema. |
| **A. ALTER aditivo** — `MODIFY habit_type ENUM(... existentes ..., 'food_day_bonus')` | **Medio** — re-escribe tabla habit_logs entera, lock momentáneo, app vanilla podría leer/escribir mientras | Patrón consistente con habits existentes. Idempotencia via DB constraint claro. | Riesgo de lock en tabla compartida con vanilla. ALTER MODIFY ENUM no es metadata-only — replica datos. Vanilla podría tener algún SELECT con CASE/WHERE sobre habit_type que asume valores específicos. |

**Mi recomendación cambia a Opción B** dado tu advertencia: tabla `food_day_bonuses` separada, **cero impacto a habit_logs ni a la app vanilla**. La idempotencia es igual de fuerte (UNIQUE constraint), y aislamos la nueva feature al 100%.

Opción C también es válida si querés cero tablas nuevas además de `food_photos`.

---

### B-02 · DB local **NO** tiene `assigned_plans`, `progress_photos`, ni `client_xp`

⚠ **Salvaguarda obligatoria:** cualquier sync de prod a local debe ser **schema only** (DDL), nunca incluir datos reales de clientes (PII, payments, auth_tokens). Usar `mysqldump --no-data` o equivalente, y filtrar tablas `payments`, `auth_tokens`, `chat_messages`, `clients`, `payment_proofs`, `bloodwork_results` para evitar fugas.


La DB local tiene 66 tablas, pero faltan 3 que son load-bearing para esta feature:

| Tabla | Existe local | Usada por |
|---|---|---|
| `assigned_plans` | ❌ | NutritionPlan.php, AssignedPlan model, plan.lock middleware, **toda la lógica de "plan activo"** |
| `progress_photos` | ❌ | El spec dice "copiar el patrón" — pero la tabla no está local |
| `client_xp` | ❌ | El spec dice "+15 XP" — pero la tabla referenciada en `Client::xp()` no existe local |
| `food_analyses` | ✅ (vacía) | Tabla preexistente — ver B-03 |

**Implicación práctica:** No se puede desarrollar ni testear la feature en local sin sincronizar el schema de producción. Las migraciones de Laravel locales están desfasadas respecto a la DB compartida vanilla+Laravel.

**Opciones:**

| Opción | Pros | Contras |
|---|---|---|
| **A. Hacer dump de schema prod → import local** (sin datos sensibles) | Local refleja realidad. Tests unitarios y feature corren. | Setup de 1-2h. Hay que cuidar PII. |
| **B. Crear migraciones Laravel "espejo"** que repliquen schema prod | Repo se vuelve fuente de verdad. Reproducible. | Es trabajo significativo (3+ tablas grandes con índices). Hay que hacerlo aditivo, no destructivo. |
| **C. Skipear local, desarrollar contra prod readonly + smoke test** | Velocidad inmediata. | Cero TDD, cero confianza. Contradice CLAUDE.md. |

**Mi recomendación:** Opción A para esta feature ahora. Opción B como deuda técnica pendiente del proyecto.

---

### B-03 · Ya existe tabla `food_analyses` — decisión arquitectónica

Schema preexistente (creado para análisis IA de comida):

```sql
food_analyses (
  id, client_id, image_path varchar(500), food_name, calories,
  protein, carbs, fat decimal, ai_response json,
  source enum('manual','ai'), created_at, updated_at
)
```

Está **vacía** (0 filas). El spec propone crear `food_photos` desde cero. **Pero conceptualmente:**

- `food_analyses` = "qué comió el cliente" (datos nutricionales, IA)
- `food_photos` (propuesta) = "foto de qué comió" (foto + revisión coach + gamificación)

**Pregunta arquitectónica:** ¿Una tabla o dos?

| Opción | Pros | Contras |
|---|---|---|
| **A. Crear `food_photos` separada** + FK opcional a `food_analyses` cuando IA exista | Separación de concerns clara. Coach review NO es responsabilidad de food_analyses. Migración futura a IA es no-rompedora. | 2 tablas relacionadas. |
| **B. Extender `food_analyses`** con columnas `meal_index`, `coach_seen`, `coach_reaction`, `xp_awarded`, `photo_date` | 1 sola tabla. Reutiliza `image_path` y `ai_response`. | God table — mezcla "qué se comió" con "coach revisó" y "fotos del día". Crece descontroladamente. |

**Mi recomendación:** Opción A. La tabla `food_analyses` puede quedar como referencia futura para IA — `food_photos.food_analysis_id NULL FK` cuando se conecte. Hoy no la tocamos.

---

## 2. Hallazgos confirmados (sin bloqueo, ajustes al spec)

### H-01 · Storage path correcto: `storage/app/public/food-photos/{client_id}/{uuid}.jpg`

ProgressPhoto **no** guarda en `public/uploads/...` como decía el spec. El patrón real (ver `app/Livewire/Client/ProgressPhotos.php:97-117`):

```php
$relativePath = sprintf('progress/%d/%s_%s.%s', $clientId, ...);
$photo->storeAs(dirname($relativePath), basename($relativePath), 'public');
ProgressPhoto::create([..., 'filename' => $relativePath]);
```

- Disk: `'public'` (Laravel filesystem disk → `storage/app/public/`)
- Symlink: `public/storage` → `storage/app/public` (✓ confirmado)
- URL servida: `/storage/{filename}`
- Subdirectorio por `client_id` (mejor que flat — facilita backup selectivo y debug)

**Cambio al spec:** sección 13 storage debe decir:
- Físico: `storage/app/public/food-photos/{client_id}/{uuid}.jpg`
- DB `filename`: `food-photos/{client_id}/{uuid}.jpg` (relativo al disk)
- URL: `/storage/{filename}` o `Storage::disk('public')->url($filename)`

`.gitignore` confirma `/public/storage` ignorado. `storage/app/public/` persiste deploys (es el volume).

---

### H-02 · `OwnedByClientScope` correcto pero `withoutGlobalScopes()` es redundante para coach

Código real del scope (`app/Scopes/OwnedByClientScope.php:21-28`):

```php
public function apply(Builder $builder, Model $model): void
{
    $user = auth('wellcore')->user();
    if ($user instanceof Client) {
        $builder->where($model->qualifyColumn('client_id'), $user->id);
    }
}
```

Confirmado: el scope **se auto-desactiva** cuando el usuario no es Client (admin, coach, queue/cli, anónimo). Por tanto en queries del coach Livewire:

```php
// Spec original (redundante):
FoodPhoto::withoutGlobalScopes()->whereIn('client_id', $clientIds)->...

// Correcto y suficiente:
FoodPhoto::whereIn('client_id', $clientIds)->...
```

Mantener `withoutGlobalScopes()` no rompe, pero da impresión de que el scope hace algo que no hace. **Quitar.**

---

### H-03 · Patrón de XP — hay 2 patrones en producción, elegir uno

Búsqueda en código (25+ archivos):

**Patrón 1** — modelo ClientXp (mayoritario):
```php
$clientXp = ClientXp::firstOrCreate(['client_id' => $cid], ['xp_total' => 0, 'level' => 1, 'streak_days' => 0]);
$clientXp->xp_total += $xpEarned;
$clientXp->level = max(1, (int) floor($clientXp->xp_total / 200) + 1);
$clientXp->save();
```
Usado en `WorkoutPlayer.php:861`, `TrainingController.php:1776`.

**Patrón 2** — columna directa en clients (legacy):
```php
DB::table('clients')->where('id', $clientId)->value('xp_total');
```
Usado en `LeaderboardService.php:68`. Inconsistente con patrón 1.

**Decisión:** FoodPhotoService debe usar **Patrón 1** (ClientXp model). Es el dominante y el que respeta `level` autocomputado.

Adicional: `AchievementService.php:89` usa `->increment('xp_total', $xp)` lo cual es race-safe SIN lock manual (UPDATE atómico). Considerar este patrón en lugar de `+= $xp; save()` que sí necesita lock.

```php
// Mejor (atómico, sin race condition):
ClientXp::where('client_id', $cid)->increment('xp_total', 15);
// Luego recalcular level si cruzó umbral
```

---

### H-04 · `streak_days` ya existe como columna en `client_xp`

Pero ese streak es **de entrenamientos**, no de comidas. No reutilizar — calcular streak de food separado. Para evitar costo en cada GET:

```php
Cache::remember("food_streak:{$clientId}", 300, fn() =>
    $this->computeFoodStreak($clientId)  // SQL agregado por día
);
```

5 minutos TTL. Invalidar tras upload exitoso.

---

### H-05 · `assigned_plans` — schema confirmado (cuando exista local)

Modelo `app/Models/AssignedPlan.php:13-22` confirma columnas exactas:
- `plan_type` (string libre, no enum) — valores observados en código: `'nutricion'`, `'entrenamiento'`, `'rise_*'`, `'*_trial'`
- `active` (boolean, cast confirmado)
- `assigned_by` (FK a admins)
- `content` (cast `array`)
- `valid_from`, `expires_at` (date)

Spec original correcto en este punto (cuando la tabla exista en local).

---

### H-06 · `PushNotificationService` patrón canónico

Patrón static factory confirmado (`app/Services/PushNotificationService.php:36-50`):

```php
public static function notifyXxx(int $clientId, ...$args): bool
{
    return (new static)->send($clientId, [
        'title' => '...',
        'body'  => '...',
        'icon'  => '/images/logo-dark.png',
        'badge' => '/icons/icon-192x192.png',
        'tag'   => 'unique-tag-replaces-prior',
        'data'  => ['url' => '/path', 'type' => 'event_type'],
        'actions' => [...],  // opcional
    ]);
}
```

**Pero hay un gotcha:** el método `send()` SOLO acepta `clientId`. Para notificar al **coach** (que es Admin, no Client), `PushNotificationService::send()` busca `PushSubscription::where('client_id', $clientId)`. **No funciona para admins/coaches** — hace falta extender el servicio o usar otro canal.

**Decisión requerida:** ¿Cómo se notifica a un coach? Opciones:
- A. Solo notificación in-app via `WellcoreNotification` (sin push). Más simple.
- B. Extender `PushSubscription` para soportar `user_type='admin'`. Más invasivo.
- C. Email al coach (puede ser ruidoso).

`WellcoreNotification` ya soporta `user_type` (`client` o `admin`) per code en `PushNotificationService.php:397`. **Recomendación: A** — crear in-app notification para coach con `user_type='admin'`, sin push browser. Menos ruido y no requiere extender el servicio.

---

### H-07 · `AutoCreatedAt` solo setea `created_at`

Trait en `app/Models/Concerns/AutoCreatedAt.php:7-13` solo maneja created_at en el creating event. **No setea updated_at.**

Para FoodPhoto que SÍ usa `updated_at` (al revisar coach), NO usar `AutoCreatedAt`. Usar Laravel timestamps default:

```php
class FoodPhoto extends Model {
    public $timestamps = true;  // ambos created_at y updated_at automáticos
    // NO usar AutoCreatedAt
}
```

---

### H-08 · `meal_swaps`, `rise_habits_logs` existen — verificar antes

Tablas relacionadas que el spec no consideró:
- `meal_swaps` — quizás ya hay tracking de qué comida cambió el cliente
- `rise_habits_logs` — ¿existe ahí lógica de bonus de hábitos para programa RISE?

**Acción de pre-flight pendiente** (no realizada para no extender el reporte): leer ambos schemas para confirmar que NO duplicamos.

---

## 3. Resumen ejecutivo de cambios al spec original

| # | Sección del spec original | Cambio requerido |
|---|---|---|
| §2 DB | UNIQUE `(client_id, meal_index, photo_date)` | OK; añadir índice `(client_id, photo_date)` para streak query |
| §2 DB | path `public/uploads/food-photos/{uuid}.jpg` | **Cambiar a** `storage/app/public/food-photos/{client_id}/{uuid}.jpg`, servir vía symlink |
| §6 Service | `HabitLog::firstOrCreate(['habit_type' => 'food_day_bonus', ...])` | **Bloqueado por ENUM**; aplicar B-01 antes |
| §6 Service | `$clientXp->xp_total += 15; $clientXp->save()` | **Cambiar a** `ClientXp::increment('xp_total', 15)` (atómico, sin lock) |
| §6 Service | `Cache::lock("food_day_bonus", 10)` | TTL muy bajo; subir a 30s o usar `block()` |
| §8 Coach | `FoodPhoto::withoutGlobalScopes()` | **Remover** — scope ya no afecta coaches |
| §9 Push | `notifyCoachFoodPhotoUploaded` | **No funciona via push** — usar WellcoreNotification in-app con user_type='admin' |
| §10 Streak | "calculada on-the-fly desde food_photos" | OK pero **agregar Cache 5 min** + invalidación post-upload |
| §13 Diseño | Sin cambios | OK |

---

## 4. Salvaguardas globales (no romper lo existente)

Aplicables a TODAS las decisiones, sin excepción:

| Salvaguarda | Cómo se cumple |
|---|---|
| **Cero impacto a app vanilla PHP** | Solo agregar tablas/columnas nuevas. NO modificar tablas que vanilla lee/escribe (clients, payments, auth_tokens, habit_logs si se elige opción A). |
| **Cero migración destructiva** | `php artisan migrate` solo CREATE TABLE / ADD COLUMN. Cero `DROP`, `RENAME`, `MODIFY` sobre tablas compartidas. |
| **Verificar schema prod ANTES de ALTER** | Antes de cualquier ALTER en prod, ejecutar `SHOW CREATE TABLE` en prod (no asumir que local refleja prod). Memoria `feedback_clients_plan_enum_prod` lo deja claro. |
| **Cero edición de archivos no relacionados** | Solo se tocan: NutritionPlan.php (refactor a parser, comportamiento idéntico), PushNotificationService.php (agregar métodos, no modificar existentes), routes/api.php + routes/web.php (agregar grupos), layouts/coach.blade.php (agregar link). |
| **Tests verdes ANTES de cualquier deploy** | `php artisan test` sin fallos. Si tests pre-existentes fallan, no se sigue. |
| **Sync local schema-only** | Si se sincroniza desde prod, `--no-data` o whitelist de tablas (NO traer payments/auth_tokens/PII). |

---

## 5. Decisiones que necesito de Daniel

Antes de generar el prompt mejorado y lanzar implementación, requiero decisión explícita en:

1. **B-01 (ENUM habit_logs):** ¿Vamos con **Opción B** (tabla nueva `food_day_bonuses`) que **no toca habit_logs**? Es la opción más segura dada tu advertencia. (Recomendación cambiada: sí a B, no a A.)
2. **B-02 (schema local):** ¿Sincronizo schema-only de prod a local (sin datos sensibles) antes de codear, o preferís otra estrategia?
3. **B-03 (food_photos vs food_analyses):** ¿Creamos `food_photos` nueva (sin tocar food_analyses)? Recomiendo sí — food_analyses queda para IA futura sin fricción.
4. **H-06 (push a coach):** ¿Notificación al coach es in-app only (`WellcoreNotification` con user_type='admin')? Recomiendo sí — no extender PushSubscription a admins por ahora.

Con esas 4 respuestas, reescribo el prompt y lanzo implementación con TDD por fases.

---

## 5. Prompt definitivo (esperando respuestas)

Una vez confirmadas las 4 decisiones, el prompt mejorado:

- Reemplaza FASE 0 verbosa por referencia a este reporte
- Comienza con FASE 1: ALTER `habit_logs` + sincronizar schema local + crear migration `food_photos`
- FASE 2: tests Pest red (NutritionPlanParserTest, FoodPhotoUploadTest, FoodPhotoReviewTest)
- FASE 3: backend (model + parser + service + controller + request + resource)
- FASE 4: coach Livewire
- FASE 5: Vue SPA + composable + router + nav
- FASE 6: build local + commit `public/build` + verificación Chrome DevTools

Cada fase con commit autocontenido y verificación obligatoria antes de avanzar.

---

**Status:** Pre-flight completado. Bloqueado en decisiones de Daniel sobre B-01 a B-03 y H-06.
