# 0001 — Arquitectura Strangler Fig: Laravel + Vanilla PHP comparten DB

La plataforma WellCore comenzó como una app vanilla PHP en `wellcorefitness/`. Se decidió migrar a Laravel gradualmente usando el patrón Strangler Fig: ambas apps coexisten y comparten la misma base de datos MySQL `wellcore_fitness`. Laravel reemplaza rutas y funcionalidades de forma incremental sin big-bang rewrite.

## Consecuencias vinculantes

- **NUNCA modificar** el directorio `C:\Users\GODSF\Herd\wellcorefitness` desde el contexto Laravel
- **NUNCA correr migraciones destructivas** (DROP TABLE, ALTER COLUMN, renombrar) — la app vanilla PHP depende del schema actual
- Todos los modelos Eloquent deben tener `$table` explícito — los nombres de tabla son legacy, no siguen convenciones Laravel
- Las tablas existentes NO tienen migraciones Laravel — los modelos mapean directamente a tablas vanilla
- Un cambio de schema que rompe vanilla PHP tumba producción inmediatamente (zero rollback automático)

## Boundary actual (2026-05)

- **Vanilla PHP maneja**: lógica legacy, algunas rutas públicas, scripts de cron legacy
- **Laravel maneja**: auth (WellCoreGuard), dashboard de cliente/coach/admin, API REST, Vue 3 SPA, pagos Wompi
- **Compartido**: todas las tablas de `wellcore_fitness` — clients, coaches, plans, workout_sessions, checkins, auth_tokens, payments
