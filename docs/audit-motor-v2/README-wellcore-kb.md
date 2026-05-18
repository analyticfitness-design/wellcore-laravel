# wellcore_kb — Sprint 0 Motor v2

Base de datos LOCAL (HERD MySQL) que contiene el knowledge base curado para el motor v2 de creación de planes.

## SAFETY GUARANTEES

- `wellcore_kb` es base de datos **NUEVA y SEPARADA** — NO toca `wellcore_fitness` (la compartida con vanilla PHP en producción).
- Conexión exclusivamente a `127.0.0.1:3306` (Herd MySQL local).
- Schema **solo aditivo** (CREATE IF NOT EXISTS, sin DROP/ALTER/TRUNCATE/DELETE).
- Seed **idempotente** (upsert por `slug`).
- Audit trail en `wellcore_kb.methodologies_seed_runs`.

## Estado actual (2026-05-17)

```
wellcore_kb.methodologies              → 15 metodologías activas
wellcore_kb.methodologies_seed_runs    → audit trail de ejecuciones
```

## Re-ejecución del seed

Si actualizás `methodologies-seed.json` y querés sincronizar con la BD:

```bash
"/c/Users/GODSF/.config/herd/bin/php.bat" docs/audit-motor-v2/02-seed-methodologies.cli.php
```

El script:
1. Lee `methodologies-seed.json`
2. Hace upsert por `slug` (INSERT nuevas, UPDATE existentes)
3. Registra ejecución en `methodologies_seed_runs`
4. Reporta count final

## Archivos del sprint 0

| Archivo | Propósito |
|---------|-----------|
| `methodologies-seed.json` | Catálogo curado de 15 metodologías (fuente de verdad) |
| `01-create-wellcore-kb-methodologies.sql` | DDL idempotente (BD + 2 tablas) |
| `02-seed-methodologies.cli.php` | Script de seed re-ejecutable |
| `01-AUDIT-POR-TIER.md` | Audit previo a sprint 0 (referencia) |
| `README-wellcore-kb.md` | Este archivo |

## Schema `methodologies`

| Columna | Tipo | Nota |
|---------|------|------|
| `id` | BIGINT PK | autoincremental |
| `slug` | VARCHAR(120) UNIQUE | identificador único kebab-case |
| `name` | VARCHAR(200) | nombre humano |
| `type` | VARCHAR(40) | `entrenamiento` por ahora |
| `source` | VARCHAR(60) | `literatura_clasica` / `literatura_cientifica` / `wellcore_adaptado` |
| `evidence_level` | VARCHAR(20) | `alta` / `moderada` / `baja` / `anecdotica` |
| `is_split_agnostic` | TINYINT(1) | 1 = se monta sobre split base |
| `short_description` | TEXT | resumen 1-2 frases |
| `applicable_tiers` | JSON | array indexable |
| `applicable_levels` | JSON | array indexable |
| `applicable_objectives` | JSON | array indexable |
| `applicable_gender` | JSON | array indexable |
| `applicable_days_range` | JSON | array indexable |
| `applicable_locations` | JSON | array indexable |
| `raw_data` | JSON | entry completo del JSON (no perder estructura) |
| `version`, `active`, `created_at`, `updated_at` | metadata |

Las 6 columnas `applicable_*` son JSON arrays — el motor v2 las consulta con `JSON_CONTAINS(applicable_tiers, '"esencial"')` etc.

## Queries de ejemplo

**¿Qué metodologías aplican para cliente Esencial intermedio mujer 5 días foco hipertrofia?**
```sql
SELECT slug, name, is_split_agnostic FROM methodologies
WHERE active = 1
  AND JSON_CONTAINS(applicable_tiers, '"esencial"')
  AND JSON_CONTAINS(applicable_levels, '"intermedio"')
  AND JSON_CONTAINS(applicable_gender, '"femenino"')
  AND JSON_CONTAINS(applicable_objectives, '"hipertrofia"')
  AND JSON_CONTAINS(applicable_days_range, '5');
```
Resultado real (test 2026-05-17):
- `body-part-split-5d` (split base — único candidato por días)
- `periodizacion-dup` (periodización opcional)
- `periodizacion-lineal-4sem-esencial` (periodización default Esencial)
- `entrenamiento-femenino-autoregulado` (overlay femenino evidence-based)

El motor v2 hace dos decisiones independientes:
1. **Split base** → 1 candidato (filtrado por días)
2. **Periodización** → 1-3 candidatos (filtrado por tier+nivel+objetivo)

## Próximos catálogos del sprint 0

| Catálogo | Status | Notas |
|----------|--------|-------|
| `methodologies` | ✅ Sprint 0 cerrado (15/15) | Esta tabla |
| `exercise_metadata` | Pending | Top 100 ejercicios con muscle groups, equipment, level, variations |
| `nutrition_foods` | Pending | Top 60 alimentos Colombia con macros |
| `supplement_catalog` | Pending | 30 suplementos con dosis canónicas |
| `supplement_stacks` | Pending | 8 stacks pre-armados |
| `plan_templates_local` | Pending | 8 templates desde clientes reales curados |
