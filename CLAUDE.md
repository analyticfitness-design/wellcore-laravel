# WellCore Laravel

## Project Overview
WellCore Fitness — Laravel 13 + PHP 8.4, coaching platform LATAM. Strangler Fig: Laravel + PHP vanilla comparten MySQL `wellcore_fitness`. **TODO en producción** — editar → push → EasyPanel → verificar wellcorefitness.com con Chrome MCP.

@CONTEXT.md

## Tech Stack
Laravel 13.1.1 · PHP 8.4 · Vue 3.5 + TypeScript + Pinia · Livewire 3 + Alpine.js · Tailwind CSS 4 · MySQL · Vite 8

## Architecture
- Strangler Fig: Laravel + vanilla PHP comparten `wellcore_fitness` DB
- WellCoreGuard: auth_tokens table (64-char hex, 30-day expiry) — sesión → Bearer → cookie
- No migrations para tablas existentes — modelos con `$table` explícito en app/Models/
- Vue 3 SPA en resources/js/vue/ — migración gradual desde Livewire 3

## Motor v2 de creación de planes (en desarrollo desde 2026-05-17)
- Authoring toolchain **local** en Herd MySQL — reemplaza el flujo "Claude Code humano lee 27 MDs"
- **Killswitch**: `WC_ENGINE_V2_ENABLED=false` en `.env` (default — apagado hasta Sprint 4)
- DB local `wellcore_kb` con 8 tablas (conexión `kb` en `config/database.php`)
- 6 stages: INTAKE → SELECT → COMPOSE → VALIDATE → PERSIST → VERIFY
- Linter pre-INSERT como GATE DURO (30 rules iniciales, DB-driven)
- **NO toca el frontend Vue ni el schema `assigned_plans`** (excepto 1 migración aditiva en Sprint 4)
- Diseño completo: `docs/wellcore-engine-v2/` (9 docs)
- Overview operativo: `app/PlanEngine/README.md`
- Comandos: `php artisan kb:install`, `kb:seed`, `kb:status` · Slash commands: `/plan-create`, `/engine-health`

## Database
MySQL wellcore_fitness · 60+ tablas · NO migraciones destructivas · Todos los modelos con `$table` explícito · DB local `wellcore_kb` separada para el motor v2 (conexión `kb`)

## Design System
- Tokens en resources/css/app.css (@theme) · wc-accent: #DC2626
- Fuentes reales: Oswald (títulos) + Raleway (cuerpo). Bebas Neue + Inter = fallback
- Dark mode: clase `.dark` en html, Alpine.js + localStorage

## Deploy Workflow
1. Editar source localmente (este directorio)
2. `npm run build` LOCAL — nunca en container EasyPanel
3. `git add <paths>` → `git commit` → `git push origin main`
4. Click `silvia-gitpull-load` en EasyPanel via Chrome MCP (NUNCA "Rebuild Docker image")
5. Verificar: mobile 414×896 + desktop 1440×900, 0 errores consola

## Context Management
- **0–70%** — trabajar libremente
- **70–90%** — usar `/compact` ANTES de continuar
- **90%+** — `/clear` requerido — respuestas erráticas a este nivel

## Rules (safety-critical)
- NEVER modificar `C:\Users\GODSF\Herd\wellcorefitness` (vanilla PHP app)
- NEVER migraciones destructivas (DROP, modificar tipos) — DB compartida
- NEVER `npm run build` en container EasyPanel — tumba AWS host (OOM)
- NEVER `killall php-fpm` en EasyPanel — 2026-05-06: 22 min downtime, reboot EC2 manual
- NEVER `Rebuild Docker image` — solo `silvia-gitpull-load`
- NEVER `git push --force` a main

## Workflow planes: auto-deploy terminado el plan (autoritativa Daniel 2026-05-18)
Cuando el motor v2 termina de generar un plan (`plan:bundle` con status=validated):
1. Generar script PHP standalone con `plan:export-bundle-prod-script`
2. Editar el archivo cambiar `DRY_RUN = false` (autorización implícita ya dada)
3. `git add + commit + push origin main`
4. EasyPanel: `silvia-gitpull-load` via Chrome MCP
5. Consola del container: ejecutar el script (insert real)
6. Invalidar cache: `php artisan tinker --execute="Cache::forget('client_plan_v3_<X>'); Cache::forget('wp:plan:<X>'); Cache::forget('wp:weekdays:<X>'); Cache::forget('dashboard:<X>');"`

**NO pedir confirmación intermedia** entre pasos. La revisión final la hace Daniel o el coach en el portal del cliente. Solo parar y preguntar si:
- Hay error de schema/migración detectado en DRY-RUN
- El LintEngine devuelve violations severity=error
- Hay conflictos no resueltos en el ticket (edad/peso/género irreconciliables)

## Ley dura Motor v2: ejercicios solo del repo v2 (autoritativa Daniel 2026-05-18)
**Todo plan de entrenamiento DEBE usar ejercicios del repo oficial `analyticfitness-design/wellcore-exercise-gifs-v2`.**
- Los nombres de los GIFs en el repo SON los nombres canónicos. No se inventan.
- El validator `hard_exercise_gif_from_v2_repo` (severity=error) bloquea PERSIST si:
  - `gif_url` apunta fuera del repo v2
  - `gif_filename` no existe en `wellcore_kb.exercise_metadata`
  - el path no termina en `.gif`
- `ExerciseSelector` filtra ejercicios sin `gif_filename` poblado.
- `ExerciseMetadata::gifUrl()` throws si no hay `gif_filename` (no inventa).
- Cuando agregás un ejercicio nuevo: subir GIF al repo v2 → `php artisan kb:import-exercise-catalog`.
- Auto-sync: `php artisan kb:clean-exercise-catalog --delete` elimina aliases broken.

## Supply chain & secrets (CVE-2026-45321 Shai-Hulud y similares)
- **NEVER `npm install <paquete>` ni `composer require <paquete>`** sin que el paquete ya esté en `package.json`/`composer.json`. El hook `dangerous-actions-blocker.php` lo bloquea. Para agregar uno nuevo: editar manifiesto manualmente → `npm install` / `composer install` (sin args) → revisar diff del lockfile → commit del lockfile.
- **NEVER `--force`, `--legacy-peer-deps`, `--no-audit`, `--ignore-scripts=false`** en npm/composer. Si necesitás bypassear algo, pregunta al humano primero.
- **NEVER ejecutar `npm run` ni `composer run` con scripts no listados en `scripts:`** del manifiesto.
- **NEVER agregar `postinstall`/`preinstall`/`install` scripts** al `package.json` sin revisión manual de Daniel — son el vector primario de supply-chain attacks (Shai-Hulud roba `.npmrc`, `~/.aws`, env vars vía postinstall).
- **NEVER commitear** `.env`, `.env.backup`, `.env.production`, `node_modules/`, `vendor/`, archivos con tokens/passwords. Verificar con `git status` antes de cada commit.
- **NEVER ejecutar scripts descargados con `curl ... | bash`** ni `wget ... | sh`. Si el instalador oficial pide eso, descargar primero, leer, ejecutar.
- **NEVER editar archivos en `node_modules/` o `vendor/`** manualmente (incluyendo stubs para tests). Si necesitás mockear deps, usa `data:` URLs o vitest mocks — NUNCA tocar el directorio real.
- **Antes de cualquier `npm ci` después de cambios**: revisar `git diff package-lock.json` — paquetes nuevos sin entrada en `package.json` = alerta roja.
- **Rotación de secrets si hay sospecha de compromiso**: `.env` en producción (`DB_PASSWORD`, `WOMPI_*_SECRET`, `ANTHROPIC_API_KEY`, `AWS_*`, `REVERB_APP_SECRET`, `GOOGLE_CLIENT_SECRET`, `META_CONVERSIONS_TOKEN`) + tokens en `auth_tokens` table. Daniel decide cuándo rotar.
- **Auditoría periódica**: `npm audit` + `composer audit` en cada PR significativo. Si reportan `high`/`critical`, pausar deploy hasta resolver.
- **Para auditorías profundas de seguridad usar el prompt** `SECURITY-AUDIT-PROMPT.md` (no improvisar — el prompt define alcance y reglas de no-romper-prod).

## Test Accounts
| Rol | Usuario | Contraseña |
|-----|---------|-----------|
| Superadmin | daniel.esparza | RISE2026Admin!SuperPower |
| Cliente nativa | analyticfitcamps@gmail.com | Wellcore6962 |
| Coach | coachdann | KingLord6962 |

## Credentials & Deploy
Credenciales de producción (EasyPanel, AWS, DB, APIs, Mailjet, Wompi, GitHub):
@CREDENTIALS-DEPLOY.md

## Behavioral Rules

### Razonamiento
- **Exhaustive on first pass**: Analizar → leer TODOS los archivos relevantes. Sin scan superficial. Si el scope es incierto, preguntar antes de entregar resultados incompletos.
- **Visible reasoning en decisiones complejas**: Antes de recomendar arquitectura, schema, o solución con múltiples opciones válidas — mostrar el razonamiento paso a paso, listar suposiciones explícitas y nivel de confianza (alto/medio/bajo) antes de la respuesta final.
- **Anti-speculation**: Si algo está fuera del conocimiento actual o es incierto, decirlo directamente ("No lo sé", "No tengo suficiente info"). Nunca especular con confianza falsa. Preferir incertidumbre honesta sobre respuesta inventada.
- **If stuck >2 attempts**: Explicar el bloqueador directamente. No seguir intentando en loop.

### Honestidad y calidad
- **Honesty override**: Sin sugar-coating. Si un plan tiene un fallo fatal → decirlo directo, sin disclaimers de cortesía que suavicen el problema. Daniel necesita la verdad dura ahora, no el fracaso después.
- **Assumption surfacing**: Después de cualquier recomendación compleja, listar las suposiciones ocultas. Para cada una: (a) qué pasa si está equivocada, (b) cómo cambiaría la recomendación. La mayoría de bugs de producción vienen de suposiciones no verificadas.
- **Pre-mortem en features grandes**: Antes de implementar un cambio que toque >3 archivos, nueva integración, o cambio de schema — identificar los 3 modos de falla más probables en producción. Especialmente crítico con la DB compartida y el deploy directo a prod.

### Output
- **Bias toward action**: Output concreto temprano, iterar. Sin loops de exploración sin producir archivos reales.
- **Format default para recomendaciones**: (1) resumen en 1 frase, (2) bullets clave (máx 5), (3) siguiente acción concreta. Sin prosa larga cuando los bullets bastan.

## Code Quality — Karpathy Guidelines
Think before coding · Simplicity first · Surgical changes · Verifiable success

## Commands (/command)
`/checkpoint` · `/commit` · `/ship` · `/security-check` · `/learn` · `/model-route` · `/diagnose` · `/grill-with-docs` · `/handoff` · `/zoom-out`

@.claude/docs/agents.md

@docs/adr/0001-strangler-fig.md

@docs/adr/0002-wellcoreguard.md

@docs/adr/0003-no-destructive-migrations.md

@.claude/docs/planes.md

@.claude/docs/validation.md
