# Food Tracking — Seguimiento de Fotos de Comida

**Fecha:** 2026-05-04  
**Estado:** Aprobado — listo para implementación  
**Audiencia:** Clientes WellCore con plan de nutrición activo · Coaches asignados  

---

## 1. Visión General

Sistema completo para que los clientes documenten sus comidas diarias con fotos, y los coaches puedan revisarlas con reacciones y notas. Incluye gamificación (XP + rachas) para incentivar adherencia.

**Flujo central:**
1. Cliente abre tab "Mi Alimentación" → ve sus comidas del plan de nutrición del día
2. Por cada comida sube una foto (1 foto/comida/día)
3. Gana XP (+15 por foto, +30 bonus si completa todas las comidas del día)
4. Coach recibe notificación push (con throttle anti-flood)
5. Coach revisa en su portal → reacciona ✅/⚠️ → nota opcional
6. Cliente recibe notificación cuando el coach reacciona

---

## 2. Base de Datos

### Tabla: `food_photos` (migración aditiva — nunca destructiva)

```sql
CREATE TABLE food_photos (
  id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  client_id        BIGINT UNSIGNED NOT NULL,
  meal_name        VARCHAR(255) NOT NULL,        -- "Desayuno", "Almuerzo", etc. — fuente de verdad para display
  meal_index       TINYINT UNSIGNED NOT NULL DEFAULT 0, -- posición en comidas[] — solo para UNIQUE constraint
  photo_date       DATE NOT NULL,                -- siempre en America/Bogota
  filename         VARCHAR(255) NOT NULL,         -- UUID generado, nunca input del usuario
  file_size        INT UNSIGNED NULL,
  coach_seen       TINYINT(1) NOT NULL DEFAULT 0,
  coach_seen_at    TIMESTAMP NULL,
  coach_reaction   ENUM('bien', 'mejorar') NULL,
  coach_note       TEXT NULL,
  xp_awarded       TINYINT(1) NOT NULL DEFAULT 0, -- nunca se revoca, incluso si se elimina la foto
  ai_analysis      JSON NULL,                     -- reservado para análisis IA futuro (sin lógica todavía)
  created_at       TIMESTAMP NULL,
  updated_at       TIMESTAMP NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_client_meal_date (client_id, meal_index, photo_date), -- 1 foto/comida/día a nivel DB
  KEY idx_client_date (client_id, photo_date),
  KEY idx_coach_pending (coach_seen, created_at)
);
```

**Decisiones de diseño:**
- `meal_name` es fuente de verdad para display histórico — si el plan cambia, el historial no se rompe
- `meal_index` existe únicamente para el UNIQUE constraint de DB
- `ai_analysis JSON NULL` presente desde el inicio — extensión futura sin migration adicional
- `xp_awarded` nunca se revoca al eliminar una foto — XP ganado es permanente
- Storage flat: `public/uploads/food-photos/{uuid}.jpg` — mismo patrón y Docker volume que `ProgressPhoto`

---

## 3. Archivos a Crear

### Backend

| Archivo | Propósito |
|---|---|
| `database/migrations/2026_05_04_000001_create_food_photos_table.php` | Migración aditiva |
| `app/Models/FoodPhoto.php` | Modelo Eloquent con OwnedByClientScope |
| `app/Services/NutritionPlanParser.php` | Extrae comidas de JSON de cualquier formato de plan |
| `app/Services/FoodPhotoService.php` | Lógica de upload, upsert, XP, notificaciones |
| `app/Http/Controllers/Api/Client/FoodPhotoController.php` | Endpoints REST del cliente |
| `app/Http/Requests/StoreFoodPhotoRequest.php` | Validación del upload |
| `app/Http/Resources/FoodPhotoResource.php` | Serialización de respuestas |
| `app/Livewire/Coach/FoodPhotoReview.php` | Componente Livewire del coach |
| `resources/views/livewire/coach/food-photo-review.blade.php` | Vista del coach |

### Frontend

| Archivo | Propósito |
|---|---|
| `resources/js/vue/pages/Rise/FoodTracking.vue` | Página principal del cliente |
| `resources/js/vue/composables/useFoodTracking.js` | Estado y API calls |

### Modificaciones a archivos existentes

| Archivo | Cambio |
|---|---|
| `routes/api.php` | Agregar rutas `food-photos` bajo el grupo cliente |
| `routes/web.php` | Agregar ruta `/coach/food-photos` → FoodPhotoReview |
| `app/Services/PushNotificationService.php` | Agregar 2 métodos nuevos |
| `resources/views/layouts/coach.blade.php` | Badge de fotos pendientes en nav |
| Router Vue / nav cliente | Agregar tab "Mi Alimentación" |

---

## 4. Modelo `FoodPhoto`

```php
// app/Models/FoodPhoto.php
#[Fillable([
    'client_id', 'meal_name', 'meal_index', 'photo_date',
    'filename', 'file_size', 'coach_seen', 'coach_seen_at',
    'coach_reaction', 'coach_note', 'xp_awarded', 'ai_analysis',
])]
class FoodPhoto extends Model
{
    use AutoCreatedAt; // trait existente en el proyecto

    protected $table = 'food_photos';

    protected static function booted(): void
    {
        static::addGlobalScope(new OwnedByClientScope()); // RLS automático para clientes
    }

    protected function casts(): array
    {
        return [
            'photo_date'    => 'date',
            'coach_seen'    => 'boolean',
            'xp_awarded'    => 'boolean',
            'coach_seen_at' => 'datetime',
            'ai_analysis'   => 'array',
        ];
    }

    // URL pública de la foto — mismo patrón que ProgressPhoto
    public function getPhotoUrlAttribute(): string
    {
        return '/uploads/food-photos/' . $this->filename;
    }
}
```

---

## 5. `NutritionPlanParser` (Service Compartido)

**Problema que resuelve:** La lógica de extracción de comidas del JSON del plan existe únicamente en `NutritionPlan.php` (Livewire). Si el controller de API duplica esa lógica, habrá inconsistencias cuando los formatos de plan cambien.

**Solución:** Extraer `parseMeals()` + `normalizeMeal()` de `NutritionPlan.php` a un service reutilizable.

```php
// app/Services/NutritionPlanParser.php
class NutritionPlanParser
{
    /**
     * Extrae el array de comidas de cualquier formato de plan de nutrición JSON.
     * Soporta: comidas[], dias[n].comidas, plan_semanal[n].comidas,
     *          plan_dia_entrenamiento.comidas, meals[]
     * 
     * Retorna array normalizado con claves: nombre, calorias, alimentos, notas, macros
     */
    public static function extractMeals(array $planContent): array { ... }

    private static function normalizeMeal(array $meal): array { ... }
}
```

`NutritionPlan.php` (Livewire) refactorizado para usar este service — cero duplicación.

---

## 6. `FoodPhotoService`

Centraliza toda la lógica de negocio del upload. El controller solo valida y delega.

```php
class FoodPhotoService
{
    /**
     * Almacena o reemplaza la foto de una comida.
     * Proceso atómico: archivo nuevo → DB transaction → borrar archivo viejo.
     * Maneja race condition via QueryException 1062 (idempotente).
     */
    public function store(
        Client $client,
        UploadedFile $file,
        string $mealName,
        int $mealIndex,
        string $date  // America/Bogota
    ): FoodPhoto { ... }

    /**
     * Elimina foto solo si coach_seen = false.
     * Lanza AuthorizationException si ya fue vista.
     */
    public function delete(FoodPhoto $photo): void { ... }

    private function processImage(UploadedFile $file): string
    {
        // Intervention Image v4:
        // 1. orientate()   — corrige EXIF rotation (fotos de celular)
        // 2. scale(1200)   — resize max 1200px preservando aspecto, solo si mayor
        // 3. toJpeg(85)    — re-encode como JPEG limpio (elimina metadata maliciosa)
        // 4. save()        — guarda en public/uploads/food-photos/{uuid}.jpg
    }

    private function awardXp(Client $client, FoodPhoto $photo, string $date): void
    {
        // +15 XP solo si es primera subida (xp_awarded = false)
        // Check day-complete con Cache::lock() Redis — anti race condition
        // +30 XP bonus si todas las comidas del plan están fotografiadas hoy
        // Bonus rastreado via HabitLog (habit_type: 'food_day_bonus', log_date: $date)
        // HabitLog::firstOrCreate() garantiza idempotencia a nivel DB
    }

    private function notifyCoach(Client $client, string $mealName): void
    {
        // Throttle: 1 notificación por par (client, coach) cada 15 minutos
        // Implementado con Cache::remember("food_notif:{$coachId}:{$clientId}", 900)
        // Busca coach de nutrición: AssignedPlan::where(plan_type='nutricion')->value('assigned_by')
        // Fallback a cualquier assigned_by si el de nutrición es null
        // Skip silencioso si coach_id es null
    }
}
```

---

## 7. API Endpoints (Cliente)

Todos bajo `/api/v/client/` con middleware `auth:wellcore` + `plan.lock:strict`.

```
GET    /food-photos               → index: fotos de hoy + lista de comidas del plan
GET    /food-photos/history       → historial 7 días con % completado por día
POST   /food-photos               → upload (multipart/form-data)
DELETE /food-photos/{photo}       → eliminar (solo si coach_seen = false)
```

### Request de Upload (`StoreFoodPhotoRequest`)
```php
'photo'      => 'required|file|mimes:jpg,jpeg,png,webp|max:10240',
'meal_name'  => 'required|string|max:255',
'meal_index' => 'required|integer|min:0|max:20',
'photo_date' => [
    'sometimes', 'date',
    // Max: mañana en Bogotá (tolerancia para diferentes TZ)
    // Min: ayer en Bogotá (no backdating para farmear XP)
],
```

Throttle: `20 uploads/minuto` por cliente.

### Response de `GET /food-photos` (`FoodPhotoResource`)
```json
{
  "has_nutrition_plan": true,
  "meals": [
    {
      "index": 0,
      "nombre": "Desayuno",
      "calorias": 380,
      "alimentos": [...],
      "photo": {
        "id": 42,
        "photo_url": "/uploads/food-photos/uuid.jpg",
        "coach_seen": false,
        "coach_reaction": null,
        "coach_note": null,
        "xp_awarded": true,
        "uploaded_at": "2026-05-04T08:32:00-05:00"
      }
    }
  ],
  "xp_today": 30,
  "bonus_earned_today": false,
  "streak_days": 3,
  "week_history": [
    {"date": "2026-04-28", "total_meals": 5, "uploaded": 5, "pct": 100},
    ...
  ]
}
```

---

## 8. Coach — `FoodPhotoReview` (Livewire)

Patrón idéntico a `CheckinReview.php`.

### Estado
```php
public bool $showReviewed = false;
public ?int $selectedClientId = null;
public int $page = 1;
public array $reactionMap = [];  // [photoId => 'bien'|'mejorar']
public array $noteMap = [];      // [photoId => string]
```

### Acciones
```php
// Autorización en cada acción: verificar photo.client_id ∈ $myClientIds
public function react(int $photoId, string $reaction): void
{
    // Actualiza coach_reaction, coach_seen = true, coach_seen_at = now()
    // Notifica cliente: PushNotificationService::notifyClientFoodPhotoReacted()
}

public function saveNote(int $photoId): void  // Actualiza coach_note
public function markSeen(int $photoId): void  // Solo coach_seen = true, sin reacción
public function markAllSeen(): void           // Bulk update, sin N+1
```

### Query del Render (sin N+1)
```php
$clientIds = AssignedPlan::where('assigned_by', $coachId)->pluck('client_id')->unique();

$photos = FoodPhoto::withoutGlobalScopes()  // coach no es Client, scope no aplica
    ->whereIn('client_id', $clientIds)
    ->where('coach_seen', $this->showReviewed)
    ->when($this->selectedClientId, fn($q) => $q->where('client_id', $this->selectedClientId))
    ->orderByDesc('created_at')
    ->paginate(15);

// Eager load de clientes en una sola query
$clients = Client::whereIn('id', $photos->pluck('client_id')->unique())->keyBy('id');
```

### Badge count en nav
Cache 60s por coach: `FoodPhoto::withoutGlobalScopes()->whereIn('client_id', $myClientIds)->where('coach_seen', false)->count()`.

---

## 9. Push Notifications — Métodos Nuevos

```php
// En PushNotificationService

public static function notifyCoachFoodPhotoUploaded(
    int $coachId, string $clientName, string $mealName
): bool {
    // Título: "Foto de comida nueva 📸"
    // Body: "{$clientName} subió foto de {$mealName}"
    // URL deep link: /coach/food-photos
    // Tag: "food-photo-{$coachId}" (reemplaza notif anterior del mismo coach)
}

public static function notifyClientFoodPhotoReacted(
    int $clientId, string $coachName, string $reaction, string $mealName
): bool {
    // 'bien':    "Tu coach revisó tu {$mealName} ✅"  
    // 'mejorar': "Tu coach tiene un comentario sobre tu {$mealName} ⚠️"
    // URL: /client/food-tracking
}
```

---

## 10. Gamificación

| Evento | XP | Regla |
|---|---|---|
| Subir foto de comida | +15 XP | Solo primera vez por comida/día. No XP en reemplazos. |
| Completar todas las comidas del día | +30 XP | Verificado con Redis lock anti-race-condition. Rastreado en HabitLog `food_day_bonus`. |
| XP al eliminar foto | 0 (no se quita) | XP ganado es permanente. |

**Racha de alimentación:** días consecutivos con ≥50% de comidas fotografiadas. Calculada on-the-fly desde `food_photos` en el response del API (no columna en DB — evita migrations frecuentes).

---

## 11. Seguridad

| Capa | Mecanismo |
|---|---|
| Cliente → sus fotos | `OwnedByClientScope` — RLS aplicativo automático |
| Coach → sus clientes | `whereIn(client_id, $myClientIds)` + verificación en cada acción |
| Upload malicioso | `mimes` valida MIME real + Intervention Image re-encode como JPEG puro |
| Path traversal | filename = `Str::uuid() . '.jpg'` — nunca input del usuario |
| EXIF rotation | `$image->orientate()` antes del resize — fotos siempre correctas |
| Flood de uploads | Throttle 20/min por cliente |
| Backdating XP | Validación `photo_date` máx ayer, mín mañana (Bogotá) |
| Delete de foto vista | Bloqueado si `coach_seen = true`, validado server-side + frontend |
| Race condition upsert | UNIQUE DB constraint + catch QueryException 1062 → idempotente |
| Race condition XP | `Cache::lock("food_day_bonus:{$clientId}:{$date}", 10)` |

---

## 12. Dilemas Resueltos

| # | Dilema | Solución |
|---|---|---|
| D-01 | meal_index obsoleto tras cambio de plan | meal_name como fuente de verdad para display |
| D-02 | Carb cycling: comidas distintas por día | NutritionPlanParser compartido con Livewire |
| D-03 | Flood de notifs al coach | Cache throttle 15min por par (client, coach) |
| D-04 | EXIF rotation en fotos de celular | orientate() antes de scale() en Intervention v4 |
| D-05 | Upsert atómico sin estado inconsistente | Nuevo archivo → DB commit → borrar viejo |
| D-06 | Race condition en upsert doble | UNIQUE constraint + catch 1062 idempotente |
| D-07 | Race condition XP day-complete | Redis Cache::lock() 10s |
| D-08 | Timezone "hoy" = Bogotá no UTC | Carbon::now('America/Bogota') + validación de fecha |
| D-09 | Cliente sin plan de nutrición | has_nutrition_plan: false → empty state UX |
| D-10 | Quién es "el coach" para notificar | Buscar plan_type='nutricion' assigned_by primero |
| D-11 | Eliminar foto ya vista por coach | Bloqueado si coach_seen=true (server + frontend) |
| D-12 | Storage Docker entre deploys | Flat path = mismo volume que ProgressPhoto |
| D-13 | Performance coach con muchos clientes | Paginate(15) + eager load + índices DB |
| D-14 | Upload de archivo malicioso | mimes real + Intervention re-encode JPEG |
| D-15 | Extensión futura IA | ai_analysis JSON NULL desde inicio |

---

## 13. Sistema de Diseño — Obligatorio

`FoodTracking.vue` **debe ser visualmente indistinguible** del resto del dashboard. No hay excepción.

### Estructura del componente
```vue
<template>
  <RiseLayout>
    <!-- Loading skeleton -->
    <div v-if="loading" class="space-y-6">
      <div class="h-9 w-48 animate-pulse rounded-lg bg-wc-bg-tertiary"></div>
      <!-- ... mismo patrón que NutritionView.vue y Dashboard.vue -->
    </div>

    <!-- Error state -->
    <div v-else-if="error"
         class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6 text-center">
      <!-- ícono wc-accent/50 + mensaje + botón Reintentar -->
    </div>

    <!-- Content -->
    <div v-else class="space-y-6"> ... </div>
  </RiseLayout>
</template>
```

### Tokens de diseño a usar (NUNCA colores hardcoded)
| Clase | Uso |
|---|---|
| `bg-wc-bg-secondary` | Fondo de cards |
| `bg-wc-bg-tertiary` | Fondo de skeletons y elementos secundarios |
| `border-wc-border` | Bordes de cards |
| `text-wc-text` | Texto principal |
| `text-wc-text-secondary` | Texto secundario |
| `bg-wc-accent` | Botón principal, dot "subida" |
| `text-wc-accent` | Iconos de acción, XP badge color |
| `bg-wc-accent/10 text-wc-accent` | Badge de XP ganado |
| `font-display` | Título de sección (Oswald) |

### Color-coding de comidas (igual que NutritionView.vue)
```js
function getMealColor(nombre) {
  const n = (nombre || '').toLowerCase();
  if (n.includes('desayuno'))                         return 'bg-amber-500/10 text-amber-400';
  if (n.includes('pre-entreno') || n.includes('pre')) return 'bg-green-500/10 text-green-400';
  if (n.includes('almuerzo') || n.includes('post'))   return 'bg-blue-500/10 text-blue-400';
  if (n.includes('cena'))                             return 'bg-indigo-500/10 text-indigo-400';
  if (n.includes('snack') || n.includes('merienda'))  return 'bg-pink-500/10 text-pink-400';
  return 'bg-wc-accent/10 text-wc-accent';
}
```

### Cards de comida
```
rounded-xl border border-wc-border bg-wc-bg-secondary
overflow-hidden transition-all duration-200
hover:border-wc-border-hover  (si no subida)
border-green-500/30           (si foto subida)
```

### Botón de upload
```
flex items-center justify-center gap-2
border-t border-wc-border border-dashed
py-3 text-sm font-medium text-wc-accent
bg-wc-accent/5 hover:bg-wc-accent/10
transition-colors cursor-pointer
```

### Thumbnail de foto subida
```
w-full h-28 object-cover
```
Con overlay de "Reemplazar" en hover: `absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity`.

### Botón primario (Subir / Confirmar)
```
bg-wc-accent hover:bg-red-700 text-white
rounded-lg px-4 py-2 text-sm font-medium
transition-colors disabled:opacity-50 disabled:cursor-not-allowed
```

### Loading skeleton
Mismo patrón exacto de `NutritionView.vue`:
```
animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary
```

### Racha y XP badge
```html
<!-- Racha -->
<span class="inline-flex items-center gap-1 rounded-full
             bg-gradient-to-r from-wc-accent/15 to-amber-400/10
             px-3 py-1 text-xs font-bold uppercase tracking-wider text-wc-accent">
  🔥 3 días seguidos
</span>

<!-- XP badge por comida -->
<span class="rounded-full bg-amber-500/10 px-2 py-0.5 text-xs font-bold text-amber-400">
  +15 XP
</span>
```

### Coach Livewire — nivel de diseño
Seguir el estilo de `resources/views/livewire/coach/checkin-review.blade.php`:
- Cards con `rounded-xl border border-gray-800/50 bg-gray-900/50`
- Botones de reacción con clases Tailwind inline
- Badges de estado con colores semánticos

---

## 14. Rutas

```php
// routes/api.php — bajo grupo cliente autenticado
Route::prefix('food-photos')->group(function () {
    Route::get('/', [FoodPhotoController::class, 'index']);
    Route::get('/history', [FoodPhotoController::class, 'history']);
    Route::post('/', [FoodPhotoController::class, 'store'])->middleware('throttle:20,1');
    Route::delete('/{photo}', [FoodPhotoController::class, 'destroy']);
});

// routes/web.php — coach
Route::get('/coach/food-photos', FoodPhotoReview::class)
    ->name('coach.food-photos')
    ->middleware('auth:wellcore');
```

---

## 15. Extensión Futura (fuera de scope actual)

- **Análisis IA:** columna `ai_analysis` ya existe. Job `AnalyzeFoodPhotoJob` llama `AIService::analyzeImage()` y escribe resultado. Sin migration adicional.
- **Medallas:** "Fotógrafo Constante" (7 días racha), "Chef Documentado" (30 días en un mes). Usar sistema de medallas existente.
- **Coach API:** Si el coach portal migra a Vue, los endpoints REST del coach pueden añadirse sin romper el Livewire existente.
