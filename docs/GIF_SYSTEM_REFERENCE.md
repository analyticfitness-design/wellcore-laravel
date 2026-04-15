# Sistema de GIFs — Referencia para Reimplementación

> **Estado:** Sistema GIF eliminado en Abril 2026 para hacer reset completo.
> Este documento sirve de guía para reconstruirlo correctamente con la nueva base de datos.

---

## Qué se eliminó

### Backend
- `app/Services/ExerciseMediaService.php` — Motor de lookup: dado el nombre de un ejercicio,
  buscaba en `ejercicios_fitcron` y `exercise_aliases` para retornar `gif_url` y `video_url`.
- Import y llamadas a `enrichWithMedia()` en:
  - `app/Http/Controllers/Api/TrainingController.php` (3 bloques: semanas, dias legacy, current day)
  - `app/Http/Controllers/Api/RiseController.php` (2 bloques: program view y workout player)
- 8 comandos Artisan de mantenimiento:
  - `wellcore:sync-gif-catalog` — sincronizaba catálogo JSON → DB
  - `wellcore:smart-gif-matcher` — fuzzy match de ejercicios en planes activos → exercise_aliases
  - `wellcore:check-silvia-gifs`, `wellcore:fix-gif-mismatches`, `wellcore:fuzzy-match-exercise-gifs`
  - `wellcore:match-gifs-from-json`, `wellcore:migrate-gif-names`, `wellcore:sync-gif-filenames`

### Base de datos
- Tabla `ejercicios_fitcron` — columnas limpiadas: `gif_filename`, `gif_url`, `gif_path`, `gif_path_sin_fondo`, `sin_fondo_listo`
- Tabla `exercise_aliases` — truncada (4,382 rows)
- Archivos JSON vaciados: `scripts/gif-catalog.json` → `[]`, `scripts/gif-aliases.json` → `{}`

### GitHub CDN
- Repo `analyticfitness-design/wellcore-exercise-gifs` eliminado y recreado vacío
- CDN base URL (para cuando se suba el nuevo catálogo):
  `https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/{filename}`

### Frontend — referencias eliminadas
| Archivo | Qué se limpió |
|---|---|
| `ExerciseMediaModal.vue` | `gif_url` fallback en `exImageUrl()`, función `stopAndShowGif()` renombrada, botón "Ver GIF" |
| `Client/WorkoutPlayer.vue` | `gif_url` fallback en `exImageUrl()` |
| `Rise/WorkoutPlayer.vue` | `gif_url` fallback en `exImageUrl()` |
| `Rise/ProgramView.vue` | Bloque `v-if="ej.gif_url || ej.gif_filename"` con `img` src a `/media/gif/` |
| `Client/PlanViewer.vue` | Bloque `v-if="ejercicio.gif_url"` con thumbnail |

---

## Cómo funcionaba el sistema anterior

```
Plan JSON del cliente
  └─ ejercicio.nombre = "Sentadilla con Barra"
         │
         ▼
TrainingController / RiseController
  └─ ExerciseMediaService::enrichWithMedia($ejercicios)
         │
         ├── LAYER 1: Exact match normalizado
         │     SELECT gif_filename FROM ejercicios_fitcron
         │     WHERE normalize(nombre) = normalize(ejercicio.nombre)
         │
         └── LAYER 2: Hash lookup en exercise_aliases
               SELECT gif_filename FROM exercise_aliases
               WHERE alias = normalize(ejercicio.nombre)
         │
         ▼
gif_url = "https://raw.githubusercontent.com/analyticfitness-design/
           wellcore-exercise-gifs/master/{gif_filename}"
         │
         ▼
Frontend Vue:
  ejercicio.gif_url → img src en PlanViewer, WorkoutPlayer, ProgramView
  ejercicio.video_url → YouTube embed en ExerciseMediaModal
```

### Normalización de nombres (crítica para el matching)
```php
private function normalize(string $name): string
{
    // 1. Elimina texto entre paréntesis: "Sentadilla (Sumo)" → "Sentadilla"
    $name = preg_replace('/\([^)]*\)/', '', $name);
    // 2. Minúsculas
    $name = mb_strtolower($name);
    // 3. Remueve acentos
    $name = transliterator_transliterate('Any-Latin; Latin-ASCII', $name);
    // 4. Remueve puntuación
    $name = preg_replace('/[^\w\s]/', ' ', $name);
    // 5. Colapsa espacios
    $name = preg_replace('/\s+/', ' ', trim($name));
    // 6. Elimina stopwords
    $stopwords = ['de','con','en','el','la','los','las','un','una','y','a','al'];
    foreach ($stopwords as $sw) {
        $name = preg_replace('/\b' . preg_quote($sw) . '\b/', ' ', $name);
    }
    return trim(preg_replace('/\s+/', ' ', $name));
}
```

---

## Plan para reimplementación con nueva base de datos

### 1. Estructura recomendada para la nueva DB de ejercicios

La nueva base de datos debe tener ejercicios con nombres en español y sus GIFs.
Se recomienda una tabla más simple:

```sql
CREATE TABLE ejercicios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(255) UNIQUE NOT NULL,     -- "sentadilla-con-barra"
    nombre      VARCHAR(255) NOT NULL,            -- "Sentadilla con Barra"
    grupo       VARCHAR(100),                     -- "Pierna"
    gif_url     VARCHAR(500),                     -- URL CDN completa
    video_url   VARCHAR(500),                     -- YouTube URL
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 2. Flujo de matching recomendado (simplificado)

En lugar de 2 capas + tabla de aliases, usar una sola tabla con búsqueda por slug:

```php
// En el plan JSON cada ejercicio puede tener ya su gif_url precargado
// (asignado por el coach al crear el plan desde el catálogo)
// → No se necesita lookup en runtime si el gif_url viene en el JSON
```

O si se necesita enriquecimiento en runtime:
```php
// ExerciseMediaService nuevo (más simple)
public function enrichWithMedia(array &$ejercicios): void
{
    $nombres = collect($ejercicios)->pluck('nombre')->unique()->values()->all();
    $slugs   = array_map(fn($n) => Str::slug($n), $nombres);

    $media = DB::table('ejercicios')
        ->whereIn('slug', $slugs)
        ->pluck('gif_url', 'slug');

    foreach ($ejercicios as &$ex) {
        $slug = Str::slug($ex['nombre'] ?? '');
        if (isset($media[$slug])) {
            $ex['gif_url'] = $media[$slug];
        }
    }
}
```

### 3. Frontend — qué agregar de vuelta

En los 5 archivos Vue limpiados, agregar `gif_url` o `image_url` cuando esté disponible:

```vue
<!-- PlanViewer.vue / WorkoutPlayer.vue / ProgramView.vue -->
<div v-if="ejercicio.gif_url" class="relative h-12 w-12 overflow-hidden rounded-lg bg-wc-bg-secondary">
  <img :src="ejercicio.gif_url" :alt="ejercicio.nombre" class="h-full w-full object-cover" loading="lazy" />
</div>

<!-- ExerciseMediaModal.vue -->
function exImageUrl(ex) { return ex?.gif_url || ex?.image_url || ex?.imagen || null; }
```

### 4. GitHub CDN — proceso de subida

```bash
# 1. Clonar repo vacío
git clone https://TOKEN@github.com/analyticfitness-design/wellcore-exercise-gifs.git
cd wellcore-exercise-gifs

# 2. Copiar los nuevos GIFs (nombrados en español con guiones)
# Ejemplo: "sentadilla-con-barra.gif", "press-de-banca.gif"

# 3. Commit y push
git add *.gif
git commit -m "feat: add new exercise GIF catalog"
git push origin master

# URL de cada GIF:
# https://raw.githubusercontent.com/analyticfitness-design/wellcore-exercise-gifs/master/sentadilla-con-barra.gif
```

### 5. Archivos a recrear / scripts

```
wellcore-laravel/
├── app/Services/ExerciseMediaService.php       ← recrear simplificado
├── app/Console/Commands/SyncGifCatalog.php     ← recrear (importar nueva DB)
├── scripts/gif-catalog.json                    ← poblar con nueva DB
└── scripts/gif-aliases.json                    ← opcional, si se necesitan aliases
```

---

## Checklist de reimplementación

- [ ] Definir estructura final de la nueva DB de ejercicios (nombres, grupos, GIFs)
- [ ] Subir GIFs al repo GitHub con nombres en español
- [ ] Crear tabla `ejercicios` (o repoblar `ejercicios_fitcron`) con los datos nuevos
- [ ] Recrear `ExerciseMediaService` simplificado
- [ ] Reconectar `TrainingController::enrichWithMedia()` (3 puntos)
- [ ] Reconectar `RiseController::enrichWithMedia()` (2 puntos)
- [ ] Restaurar thumbnails en `PlanViewer.vue`, `WorkoutPlayer.vue` (client + rise), `ProgramView.vue`
- [ ] Restaurar `exImageUrl()` con fallback a `gif_url` en `ExerciseMediaModal.vue`
- [ ] Probar en cliente de prueba: Daniel Esparza (superadmin)
- [ ] Deploy: git push → silvia-gitpull-load → npm-build

---

*Documento generado: Abril 2026 — Reset del sistema GIF*
