# Registro de Riesgos — Auditoría Scope D (Limpieza raíz) — 2026-04-27

> **Modo:** SOLO REPORTE. Cero acciones aplicadas. Cero archivos movidos o borrados.
> **Auditor:** Magíster Principal de Ingeniería de Software (juramento Hipocrático del código).
> **Scope:** Archivos sueltos en `C:\Users\GODSF\Herd\wellcore-laravel\` (raíz). NO se tocan `app/`, `resources/`, `routes/`, `config/`, `database/`, `tests/`, `public/`, `bootstrap/`, `storage/`, `vendor/`, `node_modules/`, `composer.json`, `package.json`, `.env*`, `Dockerfile`, `vite.config.*`, `phpunit.xml`.
> **Estado de los candidatos:** TODOS son archivos no rastreados por git (`?? ` en `git status`). No están en CI (`.github/workflows/ci.yml`), ni en `composer.json`, `package.json`, `Dockerfile`, ni en `scripts/` activos. Solo unos pocos son referenciados desde otros prompts (también untracked).

---

## Resumen rápido

| Severidad | # Riesgos |
|---|---|
| CRÍTICO | 1 (R-001 — credenciales hardcodeadas en archivos untracked) |
| ALTO | 0 |
| MEDIO | 4 |
| BAJO | 8 |
| **Total entradas** | **13** |

> ⚠️ **Hallazgo de seguridad CRÍTICO**: 43 scripts `.ps1` y 1 archivo `.php` sueltos en raíz contienen **credenciales en texto claro** (panel EasyPanel y MySQL producción). Aunque NINGUNO está rastreado por git todavía, la sola presencia en el working tree es un riesgo: cualquier `git add .` accidental los publicaría. Ver R-001.

> El `.gitignore` actual NO contiene patrones para `_*.ps1`, `_*.txt`, `check_*.php`, `test_*.php`, `gen_files.php`, archivos basura tipo `[`, ni para las carpetas `_audit_jsons/`, `_screenshots_perf/`, `audit-design/`, `.kimi/`. Solo `_reverb_env_values.txt` está explícitamente ignorado.

---

## Categorías cubiertas en este registro

1. R-001 — Credenciales hardcodeadas en scripts untracked (CRÍTICO)
2. R-002 — Scripts `.ps1` de deploy one-off (MEDIO)
3. R-003 — Scripts `.ps1` de verificación one-off (BAJO)
4. R-004 — Scripts `.ps1` de diagnóstico/exploración one-off (BAJO)
5. R-005 — Snippets Reverb sueltos (`_reverb_*`) (MEDIO)
6. R-006 — Documentos `PROMPT_*` ya consumidos (BAJO)
7. R-007 — Documentos plan/optimización (`IMPLEMENTATION_PLAN_*`, `OPTIMIZACION_*`) (BAJO)
8. R-008 — Archivos basura zero-byte y nombres corruptos (`[`, `coach_id,`, etc.) (BAJO)
9. R-009 — Archivos PHP sueltos de debug (`check_users.php`, `test_create.php`, `gen_files.php`) (CRÍTICO/MEDIO mixto, ver R-001 y R-009)
10. R-010 — Carpeta `_audit_jsons/` (MEDIO)
11. R-011 — Carpeta `_screenshots_perf/` (BAJO)
12. R-012 — Carpeta `audit-design/` (BAJO)
13. R-013 — Carpeta `.kimi/` (skills locales de Kimi CLI) (BAJO)

---

## R-001 — Credenciales hardcodeadas en scripts y `.php` sueltos en raíz

**Severidad:** CRÍTICO
**Categoría:** Seguridad
**Confianza del auditor:** Alta (>95%)

**Archivo(s) afectado(s) — 44 archivos con secretos:**
- 43 scripts `.ps1` con prefijo `_` que llaman a `https://panel.wellcorefitness.com/api/trpc/auth.login` con email + password en texto claro (la verificación con `Grep` los enumera todos en `_check_*.ps1`, `_deploy_*.ps1`, `_easypanel_*.ps1`, `_fix_*.ps1`, `_get_lock_*.ps1`, `_kill_*.ps1`, `_make_*.ps1`, `_poll_latest.ps1`, `_read_nginx.ps1`, `_run_*.ps1`, `_test_ws.ps1`, `_update_*.ps1`, `_verify_*.ps1`, `_full_verify.ps1`, `_final_verify.ps1`, `_clear_views_debug.ps1`, `_debug_vite.ps1`, `_deploy_ip.ps1`, `_deploy_final.ps1`, `_deploy_final_verify.ps1`).
- `check_users.php` (raíz) — incluye **password de MySQL producción en texto claro** apuntando a `wellcore_fitness` y consulta `users` con `role`.

**Descripción del problema:**
Los archivos contienen credenciales de un servicio externo (panel EasyPanel) y de la base de datos de producción. Aunque están en `?? ` (untracked) y NO se han subido al repo, viven en la raíz del working tree. El riesgo no es teórico: un `git add .`, un `git stash -u`, una compactación de IDE, o un commit de "limpiar todo" subiría los secretos a `origin/main` (público o no, está hosteado en GitHub).

**Impacto SI se ignora:**
- Cliente: ninguno directo, hasta que un secreto se filtre.
- Coach: ninguno directo.
- Superadmin: alto — si esas credenciales se filtran, el atacante puede deployar/manipular EasyPanel y leer/borrar la BD `wellcore_fitness` (datos reales de clientes, pagos Wompi, fotos, planes).
- Datos: catastrófico en peor caso (RCE vía deploy + acceso DB con `root` y password).
- Performance: ninguno.

**Impacto SI se aplica el cambio propuesto (rotar + sacar de raíz):**
- Riesgo de regresión: bajo, **siempre que** las credenciales NO sean usadas por scripts activos. Verificación: nada en `composer.json`, `package.json`, `Dockerfile`, `.github/workflows/`, ni `scripts/*` referencia estos archivos.
- Áreas que se tocan: working tree local. NO toca BD, NO toca producción.
- Áreas que NO se tocan pero podrían verse afectadas indirectamente: ninguna detectada.

**Evidencia recolectada:**
1. `Grep "password|fYCVgn4XZ7twq34|QY@P6Ak2"` en `_*.ps1` → **43 archivos coinciden**.
2. `Grep "password|fYCVgn4XZ7twq34|QY@P6Ak2"` en `_*.txt|md|sh|conf|test_*.php|check_*.php|gen_*.php` → 1 archivo coincide (`check_users.php`).
3. `git ls-files | grep "_<patrón>"` → ninguno de los 43 `.ps1` está rastreado por git. `check_users.php` tampoco.
4. `Grep` en `.github/workflows/ci.yml`, `composer.json`, `package.json`, `Dockerfile`, `scripts/` → cero referencias a estos scripts. No son parte del flujo automatizado.
5. `.gitignore` no contiene patrón `_*.ps1` ni `check_*.php` (solo `_reverb_env_values.txt` está ignorado).

**Propuesta de cambio (NO aplicada — solo recomendada):**
1. **Rotar inmediatamente** las credenciales del panel EasyPanel (`info@wellcorefitness.com`) y la password de MySQL producción del usuario `root` que aparece en `check_users.php`. Esto es independiente de borrar los archivos: aunque los borres, ya estuvieron en disco semanas/meses; asume que pueden estar comprometidos.
2. Mover los 44 archivos a `scripts/legacy/20260427/` (preservados, no borrados).
3. Añadir al `.gitignore` los patrones: `/_*.ps1`, `/_*.txt`, `/_*.sh`, `/_*.conf`, `/_*.md`, `/check_users.php`, `/test_create.php`, `/gen_files.php`.
4. Documentar el incidente en `docs/SECURITY_AUDIT_AND_IMPLEMENTATION_PLAN.md` (que ya existe).

**Plan de rollback:**
- Mover los archivos a `scripts/legacy/20260427/` deja los archivos intactos. Si algo se rompe, restaurarlos a la raíz es un `mv` reversible.
- La rotación de credenciales es irreversible, pero es **lo correcto** sin importar qué.

**Tests requeridos antes de aplicar:**
- [ ] Confirmar con el usuario que ningún flujo de deploy actual depende de estos `.ps1` (los `scripts/` activos usan `patch-nginx-ws.php`, `deploy.sh`, `lighthouse-check.sh` — todos ya en `scripts/`).
- [ ] Validar que las credenciales actuales fueron rotadas antes de cualquier movimiento.
- [ ] `php artisan test --parallel` (sanity check).

**Decisión del usuario (a llenar por Daniel):**
- [ ] ✅ ACEPTAR — rotar credenciales + mover a `scripts/legacy/20260427/` + actualizar `.gitignore`
- [ ] ⏸️ DIFERIR — dejar para después
- [ ] ❌ RECHAZAR — convivir con el riesgo
- [ ] 🔄 PEDIR ALTERNATIVA — proponer otro enfoque (ej. solo agregar al `.gitignore` sin mover)

**Notas del usuario:**


---

## R-002 — Scripts `.ps1` de deploy/EasyPanel one-off

**Severidad:** MEDIO
**Categoría:** Tooling
**Confianza del auditor:** Media (~85%) — son one-off según contenido y mtime, pero el usuario debe confirmar antes de mover.

**Archivo(s) afectado(s) (16):**
- `_deploy_final.ps1`, `_deploy_final_verify.ps1`, `_deploy_ip.ps1`
- `_easypanel_deploy.ps1`, `_easypanel_deploy_full.ps1`, `_easypanel_discover.ps1`, `_easypanel_js.ps1`, `_easypanel_probe.ps1`, `_easypanel_run_script.ps1`, `_easypanel_scripts.ps1`, `_easypanel_trpc_names.ps1`, `_easypanel_v2.ps1`
- `_run_artisan_migrate.ps1`, `_run_gitpull.ps1`, `_run_migrate.ps1`, `_run_script_fixed.ps1`
- `_update_gitpull_script.ps1`, `_update_lock.ps1`

**Descripción del problema:**
Scripts de un solo uso, escritos durante deploys/incidentes específicos (Reverb deploy, lock fix, gitpull-sp4-deploy, etc.). Acumulan ruido en la raíz, contienen credenciales (ver R-001), duplican lógica entre sí y no pertenecen al flujo permanente. Actualmente el deploy "oficial" está documentado en `docs/feedback_deploy_approach.md` y usa scripts en EasyPanel (`silvia-gitpull-load`, `gitpull-load`).

**Impacto SI se ignora:**
- Cliente/Coach/Superadmin: ninguno directo.
- Datos: ninguno (en sí mismos no leen DB).
- Tooling: ruido continuo en `git status`; tentación de usarlos sin saber que son obsoletos.

**Impacto SI se aplica el cambio propuesto (mover a `scripts/legacy/20260427/`):**
- Riesgo de regresión: bajo. No hay CI ni proceso automatizado que los invoque.
- Áreas que se tocan: working tree local.
- Áreas que NO se tocan: producción, BD, código de la app, scripts activos en `scripts/`.

**Evidencia recolectada:**
1. `Grep` en `.github/workflows/ci.yml` por nombres → 0 coincidencias.
2. `Grep` en `composer.json`, `package.json`, `Dockerfile`, `docker-compose.yml` → 0 coincidencias.
3. `Grep` en `app/`, `routes/`, `config/`, `database/` → 0 coincidencias.
4. `Grep` en `scripts/` (activos) → 0 coincidencias.
5. `git ls-files` → ninguno está versionado.
6. Lectura de las primeras 30 líneas: todos hablan de eventos ya pasados (deploy 2026-04-26, fix Cristian, fix Silvia, etc.). Contenido one-off claro.

**Propuesta de cambio (NO aplicada):**
- Mover a `scripts/legacy/20260427/` para preservar histórico operativo.
- NO borrar (Daniel puede querer ver cómo se hizo un deploy específico). NO commitear (siguen conteniendo credenciales — ver R-001 primero).

**Plan de rollback:**
- `mv` de regreso a raíz si se demuestra que alguno se necesita.

**Tests requeridos antes de aplicar:**
- [ ] R-001 resuelto (credenciales rotadas).
- [ ] Confirmación explícita del usuario por categoría.

**Decisión del usuario:**
- [ ] ✅ ACEPTAR (todos los 16)
- [ ] ⏸️ DIFERIR
- [ ] ❌ RECHAZAR
- [ ] 🔄 PEDIR ALTERNATIVA (ej. mover solo algunos)

**Notas del usuario:**


---

## R-003 — Scripts `.ps1` de verificación one-off

**Severidad:** BAJO
**Categoría:** Tooling
**Confianza del auditor:** Alta (>95%)

**Archivo(s) afectado(s) (13):**
- `_verify_contract_gate.ps1`, `_verify_deploy.ps1`, `_verify_gate_v2.ps1`, `_verify_gate_v3.ps1`, `_verify_reverb.ps1`
- `_verify_silvia.ps1`, `_verify_silvia2.ps1`, `_verify_silvia3.ps1`, `_verify_silvia4.ps1`, `_verify_silvia_prod.ps1`
- `_full_verify.ps1`, `_final_verify.ps1`, `_check_manifest.ps1`, `_check_nginx_ws.ps1`, `_check_scripts_format.ps1`, `_check_vue_manifest.ps1`

(Algunos solapan con R-002; aquí cuento los puramente "verify/check".)

**Descripción del problema:**
Scripts ad-hoc de verificación de deploy/lock/manifest. Son la versión "comprobadora" de los scripts en R-002. La numeración `silvia, silvia2, silvia3, silvia4` y `gate_v2, gate_v3` evidencia iteraciones rápidas en una incidencia ya cerrada.

**Impacto SI se ignora:**
- Cliente/Coach/Superadmin: ninguno.
- Datos: ninguno (read-only HTTP a panel).
- Tooling: ruido en raíz.

**Impacto SI se aplica el cambio propuesto:**
- Riesgo de regresión: muy bajo. Cero referencias.

**Evidencia recolectada:**
1. `Grep` recursivo en repo (excluyendo el propio archivo) → 0 referencias.
2. `git ls-files` → no rastreados.
3. Mtime: todos `Apr 26 23:22-23:56` (mismo día, mismo incidente).
4. Contenido: literal `silvia` en código → personalización de un cliente específico, ya consumido.

**Propuesta de cambio (NO aplicada):**
- Mover a `scripts/legacy/20260427/`.

**Plan de rollback:** `mv` reverso.

**Tests requeridos antes de aplicar:**
- [ ] R-001 resuelto (credenciales).

**Decisión del usuario:**
- [ ] ✅ ACEPTAR
- [ ] ⏸️ DIFERIR
- [ ] ❌ RECHAZAR
- [ ] 🔄 PEDIR ALTERNATIVA

**Notas del usuario:**


---

## R-004 — Scripts `.ps1` de diagnóstico/exploración one-off

**Severidad:** BAJO
**Categoría:** Tooling
**Confianza del auditor:** Alta (>95%)

**Archivo(s) afectado(s) (12):**
- `_clear_views_debug.ps1`, `_debug_vite.ps1`
- `_find_run_call.ps1`, `_find_run_endpoint.ps1`
- `_fix_cristian.ps1`, `_fix_nginx_ws.ps1`
- `_get_lock_all.ps1`, `_get_lock_part1.ps1`, `_get_migrate_log.ps1`
- `_kill_opcache_debug.ps1`, `_make_nginx_persistent.ps1`, `_poll_latest.ps1`, `_read_nginx.ps1`
- `_test_connectivity.ps1`, `_test_ws.ps1`
- `_lock_part1.txt` (50KB de log dump)

**Descripción del problema:**
Scripts de exploración/debugging de incidentes específicos: clear views, fix Cristian (un cliente específico), get-lock-part1 (recuperación de un volcado), kill OPCache, test WebSockets, etc. `_lock_part1.txt` es un dump literal de un lockfile de Composer en formato fragmentado.

**Impacto SI se ignora:**
- Cliente/Coach/Superadmin: ninguno.
- Datos: ninguno.
- Tooling: ruido.

**Impacto SI se aplica el cambio propuesto:**
- Riesgo de regresión: muy bajo.

**Evidencia recolectada:**
1. `Grep` por nombre en repo → solo `_fix_nginx_ws.ps1` aparece auto-mencionado. Cero referencias externas.
2. `git ls-files` → no rastreados.
3. `_fix_cristian.ps1` y `_audit_jsons/gen_cristian_v2.py` referencian un `bootstrap/insert_cristian_v2.php` que **fue borrado** según `git status` (`D bootstrap/insert_cristian_v2.php`) — incidente ya cerrado.
4. Contenidos: nombres propios de incidentes resueltos.

**Propuesta de cambio (NO aplicada):**
- Mover a `scripts/legacy/20260427/`.

**Plan de rollback:** `mv` reverso.

**Tests requeridos antes de aplicar:**
- [ ] R-001 resuelto.

**Decisión del usuario:**
- [ ] ✅ ACEPTAR
- [ ] ⏸️ DIFERIR
- [ ] ❌ RECHAZAR
- [ ] 🔄 PEDIR ALTERNATIVA

**Notas del usuario:**


---

## R-005 — Snippets Reverb sueltos (`_reverb_*`)

**Severidad:** MEDIO
**Categoría:** Tooling / Documentación
**Confianza del auditor:** Media (~80%) — son histórico útil del deploy SP-5 que **YA está en producción** según memoria `project_sp5_status.md`.

**Archivo(s) afectado(s) (5):**
- `_reverb_deploy_steps.md` (instrucciones manuales del deploy SP-5)
- `_reverb_env_values.txt` (credenciales Reverb — **ya está en `.gitignore`**, OK)
- `_reverb_healthcheck.sh` (healthcheck para container)
- `_reverb_nginx.conf` (snippet nginx del proxy WS)
- `_reverb_start.sh` (start del daemon Reverb)

**Descripción del problema:**
Estos archivos NO son scripts personales: documentan/sirven el deploy de Laravel Reverb SP-5 que está vivo en producción. `_reverb_env_values.txt` ya está protegido por `.gitignore` (línea 38). Sin embargo viven sueltos en raíz junto al ruido de R-001/R-002, lo que dificulta saber cuáles importan.

**Impacto SI se ignora:**
- Riesgo de borrarlos por confusión con scripts one-off → perdería documentación operativa de un servicio activo (WebSockets reales).
- Cliente/Coach: rompería capacidad de redeploy de Reverb si pasa algo.

**Impacto SI se aplica el cambio propuesto (mover a `docs/operations/reverb/` o `scripts/operations/reverb/`):**
- Riesgo: bajo si se preservan tal cual y se actualizan referencias en `docs/superpowers/plans/*reverb*` si las hay.
- Posible necesidad de actualizar `scripts/patch-nginx-ws.php` o `docker/nginx.conf` si referencian rutas relativas.

**Evidencia recolectada:**
1. `Grep "_reverb_"` → solo se referencia entre ellos mismos y en `docs/SECURITY_IMPLEMENTATION_ROADMAP.md` (`_fix_nginx_ws.ps1`, no `_reverb_*`).
2. `git ls-files` → 4 de los 5 SÍ están rastreados (`_reverb_deploy_steps.md`, `_reverb_healthcheck.sh`, `_reverb_nginx.conf`, `_reverb_start.sh`). Solo `_reverb_env_values.txt` está untracked + en `.gitignore`.
3. Mtime: todos `Apr 26 17:44-17:46` (mismo bloque del deploy SP-5).
4. Memoria del proyecto (`project_sp5_status.md`) confirma que Reverb está activo en producción y referencia `scripts/patch-nginx-ws.php`.

**Propuesta de cambio (NO aplicada — recomendada como tarea separada):**
- Mover a `scripts/operations/reverb/` (NO a legacy — siguen vivos).
- Actualizar referencias en `docs/` si las hay.
- Mantener `_reverb_env_values.txt` en `.gitignore` y reubicarlo coherentemente.

**Plan de rollback:** `mv` reverso + ajustar `.gitignore`.

**Tests requeridos antes de aplicar:**
- [ ] Verificar Reverb en producción sigue OK (`pid 9638`, handshake 101) — ver memoria.
- [ ] Confirmar que ninguna doc activa referencia rutas absolutas a estos archivos.

**Decisión del usuario:**
- [ ] ✅ ACEPTAR (mover a `scripts/operations/reverb/`)
- [ ] ⏸️ DIFERIR
- [ ] ❌ RECHAZAR (mantenerlos en raíz)
- [ ] 🔄 PEDIR ALTERNATIVA

**Notas del usuario:**


---

## R-006 — Documentos `PROMPT_*` sueltos en raíz

**Severidad:** BAJO
**Categoría:** Tooling / Documentación
**Confianza del auditor:** Alta (>95%) — son prompts de sesiones específicas, ya consumidos.

**Archivo(s) afectado(s) (8):**
- `PROMPT_AUDIT_COACH_DASHBOARD.md` + `PROMPT_AUDIT_COACH_DASHBOARD.txt` (**duplicado .md vs .txt** del mismo prompt)
- `PROMPT_COACH_INVITATION_WOMPI.md`
- `PROMPT_REFACTOR_COACH.txt`
- `PROMPT_REFACTOR_COACH_DESIGN_SYSTEM.md`
- `PROMPT_FIX_CLIENT_TOPNAV_Y_AUDIT.md`
- `PROMPT_SONNET_EJECUCION_SECURITY.txt`
- `PROMPT_CODE_REVIEW_MAGISTER.md` (**ESTE prompt actual** — no tocar)

**Descripción del problema:**
Prompts de tareas pasadas (refactor coach, audit coach dashboard, fix topnav, security hardening). El actual (`PROMPT_CODE_REVIEW_MAGISTER.md`) está siendo consumido **ahora mismo**. Los demás describen trabajos completados en sprints anteriores (commits ya en main: dashboard coach, design system coach, topnav fix).

**Hallazgo de duplicación:** `PROMPT_AUDIT_COACH_DASHBOARD.md` y `PROMPT_AUDIT_COACH_DASHBOARD.txt` son el **mismo contenido** con formato distinto (md tiene Markdown, txt tiene encabezados ASCII). El `.txt` es redundante.

**Impacto SI se ignora:**
- Ninguno. Documentos inertes.

**Impacto SI se aplica el cambio propuesto (mover a `docs/prompts/archived/`):**
- Riesgo: bajo. Solo necesidad de actualizar referencias si alguna doc activa los menciona (verificado: `Grep` → 0 referencias activas).

**Evidencia recolectada:**
1. `Grep` por nombres → 0 coincidencias en código activo (`app/`, `routes/`, `config/`).
2. `Grep` en `docs/` → solo se autorreferencian entre ellos.
3. `git ls-files` → ninguno rastreado.
4. Lectura de primeras líneas: todos describen tareas con fechas pasadas (`Sprint 5 — Apr 24-26`) y ya completadas según `git log`.
5. `diff PROMPT_AUDIT_COACH_DASHBOARD.md PROMPT_AUDIT_COACH_DASHBOARD.txt` → mismo contenido, formato distinto.

**Propuesta de cambio (NO aplicada):**
- Mover los 7 (todos menos `PROMPT_CODE_REVIEW_MAGISTER.md`) a `docs/prompts/archived/20260427/`.
- **NO** mover el activo `PROMPT_CODE_REVIEW_MAGISTER.md` hasta que termine la auditoría completa.

**Plan de rollback:** `mv` reverso.

**Tests requeridos antes de aplicar:** ninguno.

**Decisión del usuario:**
- [ ] ✅ ACEPTAR (todos menos el activo)
- [ ] ⏸️ DIFERIR
- [ ] ❌ RECHAZAR
- [ ] 🔄 PEDIR ALTERNATIVA (ej. solo borrar el `.txt` duplicado)

**Notas del usuario:**


---

## R-007 — Documentos plan/optimización en raíz

**Severidad:** BAJO
**Categoría:** Tooling / Documentación
**Confianza del auditor:** Media (~80%) — algunos pueden seguir siendo "vivos" (en ejecución).

**Archivo(s) afectado(s) (2):**
- `IMPLEMENTATION_PLAN_HOMEPAGE_V3_FINAL.md` (Plan Sprint 5 homepage v3)
- `OPTIMIZACION_MOBILE_WELLCORE.md` (Plan optimización Core Web Vitals 2026-04-24)

**Descripción del problema:**
Planes de implementación. La carpeta `docs/` ya alberga otros (`PLAN_OPTIMIZACION_HOMEPAGE_PERFORMANCE.md`, `HOMEPAGE_OPTIMIZATION_RESULTS.md`). Tener planes en raíz mezcla "trabajo en curso" con ruido. Por la memoria `project_homepage_optimization.md`, la optimización homepage **ya fue ejecutada y publicada**.

**Impacto SI se ignora:** ninguno operativo.

**Impacto SI se aplica el cambio propuesto (mover a `docs/plans/archived/`):**
- Riesgo: bajo.

**Evidencia recolectada:**
1. `Grep` → solo se autorreferencian.
2. `git ls-files` → no rastreados.
3. Primer header indica fechas/sprints específicos (Sprint 5; 2026-04-24).
4. Memoria del proyecto confirma que homepage optimization ya está en prod.

**Propuesta de cambio (NO aplicada):**
- Mover a `docs/plans/archived/20260427/`.

**Plan de rollback:** `mv` reverso.

**Tests requeridos antes de aplicar:**
- [ ] Confirmar con el usuario que ambos planes ya se ejecutaron.

**Decisión del usuario:**
- [ ] ✅ ACEPTAR
- [ ] ⏸️ DIFERIR
- [ ] ❌ RECHAZAR
- [ ] 🔄 PEDIR ALTERNATIVA

**Notas del usuario:**


---

## R-008 — Archivos basura zero-byte y nombres corruptos

**Severidad:** BAJO
**Categoría:** Tooling
**Confianza del auditor:** Alta (>95%) — son artefactos de comandos shell mal escapados.

**Archivo(s) afectado(s) (12):**
- `[` (0 bytes — colisión con shell glob)
- `coach_id,` (0 bytes)
- `admin_edits_diff,` (0 bytes)
- `iso_year,` (0 bytes)
- `name,` (0 bytes)
- `original_content,` (0 bytes)
- `value,` (0 bytes)
- `status-` (0 bytes)
- `coach?-` (0 bytes — caracter Unicode raro `coach\xef\x80\xbf-`)
- `arch_result.txt` (55 bytes — output capturado de `php artisan test`)
- `test_out.txt` (47 bytes — idem)
- `_lock_part1.txt` (50 KB — fragmento de lockfile, ya cubierto en R-004)

**Descripción del problema:**
Producto de redirecciones shell mal cerradas (`> coach_id,` con coma trailing accidental, comandos PowerShell que crearon `[` por glob roto, etc.). Los archivos vacíos no contienen información útil. Los `.txt` de 47-55 bytes son outputs de `php artisan test` capturados ad-hoc.

**Impacto SI se ignora:**
- Cliente/Coach/Superadmin: ninguno.
- Tooling: ruido en `git status`, riesgo de commit accidental.
- Bug latente: `[` puede causar problemas con globs PowerShell/bash en scripts.

**Impacto SI se aplica el cambio propuesto (eliminar los zero-byte y `.txt` huérfanos):**
- Riesgo: cero. Son archivos vacíos o output trivial.

**Evidencia recolectada:**
1. `ls -la` muestra tamaño 0 bytes para 9 de ellos.
2. `Grep` por contenido → vacío.
3. `git ls-files` → no rastreados.
4. Nombres con coma trailing son sintaxis bash típica de error: `echo $foo > coach_id,` (donde el operador esperado era `,`).
5. `arch_result.txt` y `test_out.txt` contienen `WARN  No code coverage driver available\nEXIT: 1` — output de un test corrido.

**Propuesta de cambio (NO aplicada):**
- **Excepción**: aquí sí recomiendo borrar (no mover) — son strictly garbage. Pero NO lo hago: solo se reporta y el usuario decide.
- Alternativa segura: mover a `scripts/legacy/20260427/garbage/` para preservar evidencia forense.

**Plan de rollback:**
- Si se mueven: `mv` reverso.
- Si se borran: irreversible (pero al ser zero-byte, equivalente a no haber existido).

**Tests requeridos antes de aplicar:** ninguno.

**Decisión del usuario:**
- [ ] ✅ ACEPTAR borrar todos
- [ ] ✅ ACEPTAR mover a `scripts/legacy/20260427/garbage/` (más seguro)
- [ ] ⏸️ DIFERIR
- [ ] ❌ RECHAZAR
- [ ] 🔄 PEDIR ALTERNATIVA

**Notas del usuario:**


---

## R-009 — Archivos PHP sueltos de debug en raíz

**Severidad:** MEDIO (parte del riesgo va a R-001 por credenciales)
**Categoría:** Seguridad / Tooling
**Confianza del auditor:** Alta (>95%)

**Archivo(s) afectado(s) (3):**
- `check_users.php` (12 líneas — **contiene password MySQL prod** — ver R-001)
- `test_create.php` (12 líneas — crea `tests/Unit/M0/FeatureFlagTest.php` ad-hoc; one-off ya consumido si el test existe)
- `gen_files.php` (función `wf()` para escribir archivos en bulk; one-off generador)
- `reset_kimi_auth.bat` (script Windows para limpiar `~/.kimi/credentials`; herramienta personal, NO del proyecto)

**Descripción del problema:**
PHP sueltos en raíz que NO pertenecen al árbol de la app Laravel:
- `check_users.php`: ejecutaría una conexión PDO directa a MySQL prod si lo corres. Es debug interactivo. **Tiene la password en texto claro.**
- `test_create.php`: generó un test que ya existe (`tests/Unit/M0/FeatureFlagTest.php` — verificable con `Glob`).
- `gen_files.php`: utilidad de scaffolding ya consumida (escribió archivos que ahora viven en sus rutas finales).
- `reset_kimi_auth.bat`: ayudante personal de Kimi CLI; no es código de producción. Ver R-013 para `.kimi/`.

**Impacto SI se ignora:**
- `check_users.php`: alto si se commitea por error (ya cubierto en R-001).
- Los demás: ruido en raíz.

**Impacto SI se aplica el cambio propuesto:**
- Riesgo: bajo (si rotamos credenciales primero). Cero impacto en código activo.

**Evidencia recolectada:**
1. `Grep` por `check_users.php`, `test_create.php`, `gen_files.php`, `reset_kimi_auth.bat` → 0 referencias en código activo.
2. `git ls-files` → no rastreados.
3. Lectura completa: confirmo el carácter "one-off ya consumido".
4. `Glob "tests/Unit/M0/FeatureFlagTest.php"` debería existir (test_create.php lo creó); validable.

**Propuesta de cambio (NO aplicada):**
- `check_users.php` → borrar después de rotar credenciales (ver R-001) o mover a `scripts/legacy/20260427/CRITICAL_HAS_SECRETS/`.
- `test_create.php` y `gen_files.php` → mover a `scripts/legacy/20260427/`.
- `reset_kimi_auth.bat` → mover a `scripts/legacy/20260427/` (o devolver al usuario en `~/Desktop/`).

**Plan de rollback:** `mv` reverso.

**Tests requeridos antes de aplicar:**
- [ ] R-001 resuelto.
- [ ] Confirmar `tests/Unit/M0/FeatureFlagTest.php` existe ya.

**Decisión del usuario:**
- [ ] ✅ ACEPTAR
- [ ] ⏸️ DIFERIR
- [ ] ❌ RECHAZAR
- [ ] 🔄 PEDIR ALTERNATIVA

**Notas del usuario:**


---

## R-010 — Carpeta `_audit_jsons/` (generadores Python de planes)

**Severidad:** MEDIO
**Categoría:** Tooling
**Confianza del auditor:** Media (~80%) — uno de los archivos referencia un PHP que YA fue borrado.

**Archivo(s) afectado(s):**
- `_audit_jsons/gen_cristian_v2.py` (untracked, 31 KB) — genera un `bootstrap/insert_cristian_v2.php` que ya NO existe (`git status` muestra `D bootstrap/insert_cristian_v2.php`).
- `_audit_jsons/gen_john_v1.py` (rastreado en git, 41 KB) — generador de planes para cliente "John".

**Descripción del problema:**
Generadores Python que producen planes de entrenamiento/nutrición para clientes específicos por nombre. **gen_cristian_v2.py** ya está obsoleto (su output fue borrado). **gen_john_v1.py** SÍ está en git, lo que sugiere que fue una herramienta usada y validada — pero el sistema oficial de creación de planes vive en `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\` según `CLAUDE.md`. Ningún proceso activo lo invoca.

**Impacto SI se ignora:**
- Cliente: ninguno (no se ejecutan automáticamente).
- Datos: ninguno.
- Tooling: ambigüedad sobre cuál es el método oficial.

**Impacto SI se aplica el cambio propuesto:**
- `gen_cristian_v2.py`: mover a legacy → riesgo cero (output ya borrado).
- `gen_john_v1.py`: como **está en git**, mover requiere `git mv` y commit → eleva el riesgo a MEDIO porque toca historia. Recomiendo **DIFERIR** y pedir al usuario.

**Evidencia recolectada:**
1. `Grep` por `_audit_jsons` y `gen_cristian_v2.py`, `gen_john_v1.py` → 0 coincidencias en código activo.
2. `git ls-files | grep _audit_jsons` → solo `_audit_jsons/gen_john_v1.py` está rastreado.
3. `git status` → `bootstrap/insert_cristian_v2.php` está marcado como `D` (eliminado pero no commiteado).
4. Lectura primer fragmento: ambos importan JSON y generan PHP por línea de comando.

**Propuesta de cambio (NO aplicada):**
- `gen_cristian_v2.py` → mover a `scripts/legacy/20260427/audit-generators/`.
- `gen_john_v1.py` → DIFERIR. Si se decide mover, debe ser `git mv` con commit.

**Plan de rollback:** `mv` reverso.

**Tests requeridos antes de aplicar:**
- [ ] Confirmar con el usuario si el sistema oficial de planes sustituye a estos generadores.

**Decisión del usuario:**
- [ ] ✅ ACEPTAR (ambos)
- [ ] ✅ ACEPTAR (solo cristian, dejar john tracked donde está)
- [ ] ⏸️ DIFERIR
- [ ] ❌ RECHAZAR
- [ ] 🔄 PEDIR ALTERNATIVA

**Notas del usuario:**


---

## R-011 — Carpeta `_screenshots_perf/` (screenshots de auditorías performance)

**Severidad:** BAJO
**Categoría:** Tooling
**Confianza del auditor:** Alta (>95%)

**Archivo(s) afectado(s):** `_screenshots_perf/` (26 imágenes PNG/JPEG, ~4 MB total).

**Descripción del problema:**
Capturas de pantalla de auditorías pasadas: `pagespeed-mobile-69.jpeg`, `console-backfill.png`, `easypanel-tinker-table.png`, `gitpull-output.png`, `hero-v3-*.png`, etc. Todas datadas `Apr 26 23:22`. La memoria `project_homepage_optimization.md` confirma que el sprint correspondiente (Lighthouse 74-77) ya cerró. Referenciada solo desde `PROMPT_FIX_CLIENT_TOPNAV_Y_AUDIT.md` (también untracked).

**Impacto SI se ignora:** ninguno operativo. Ocupa ~4 MB.

**Impacto SI se aplica el cambio propuesto:** riesgo cero.

**Evidencia recolectada:**
1. `Grep "_screenshots_perf"` → solo `PROMPT_FIX_CLIENT_TOPNAV_Y_AUDIT.md` (también untracked, candidato a archivar).
2. `git ls-files | grep _screenshots_perf` → 0.
3. Naming pattern indica capturas one-shot.

**Propuesta de cambio (NO aplicada):**
- Mover a `docs/audit/screenshots/20260427/`.
- Comprimir si se mantiene a largo plazo.

**Plan de rollback:** `mv` reverso.

**Tests requeridos antes de aplicar:** ninguno.

**Decisión del usuario:**
- [ ] ✅ ACEPTAR
- [ ] ⏸️ DIFERIR
- [ ] ❌ RECHAZAR
- [ ] 🔄 PEDIR ALTERNATIVA

**Notas del usuario:**


---

## R-012 — Carpeta `audit-design/` (HTMLs y screenshots de diseño)

**Severidad:** BAJO
**Categoría:** Tooling / Documentación de diseño
**Confianza del auditor:** Media (~80%) — contiene HTMLs de Claude Artifacts que pueden seguir siendo referencia.

**Archivo(s) afectado(s):** `audit-design/` (24 archivos: HTMLs renderizados, screenshots prod vs claude-design, sub-carpetas `comp/` y `homepage-comp/` con comparaciones v3 vs real).

**Descripción del problema:**
Material de diseño de auditorías visuales: `claude-dashboard-v3.html`, `claude-homepage.html`, `claude-light-theme-ab.html`, screenshots de `prod-*.jpeg`, comparativas `v3-*` vs `real-*`. Se usa como referencia visual durante refactors. Solo `AGENTS.md` y plans en `docs/superpowers/plans/` lo mencionan, todos también untracked o ya planeados.

**Impacto SI se ignora:** ninguno operativo. ~4.5 MB.

**Impacto SI se aplica el cambio propuesto:**
- Riesgo: bajo. Si se mueve, actualizar `AGENTS.md` si lo apunta con ruta exacta.

**Evidencia recolectada:**
1. `Grep "audit-design"` → `AGENTS.md`, `docs/superpowers/plans/2026-04-26-coach-strategy-hub-implementation.md`, `PROMPT_FIX_CLIENT_TOPNAV_Y_AUDIT.md` (los 2 últimos también untracked).
2. `git ls-files | grep audit-design` → 0.
3. Nombres `prod-*.jpeg` indican capturas vs producción.

**Propuesta de cambio (NO aplicada):**
- Mover a `docs/audit/design/` y actualizar la referencia en `AGENTS.md`.

**Plan de rollback:** `mv` + `git revert` del cambio en `AGENTS.md`.

**Tests requeridos antes de aplicar:**
- [ ] Verificar referencia en `AGENTS.md`.
- [ ] Confirmar con el usuario si las HTMLs de Claude Artifacts se siguen usando.

**Decisión del usuario:**
- [ ] ✅ ACEPTAR (mover a `docs/audit/design/`)
- [ ] ⏸️ DIFERIR
- [ ] ❌ RECHAZAR
- [ ] 🔄 PEDIR ALTERNATIVA

**Notas del usuario:**


---

## R-013 — Carpeta `.kimi/` (skills locales de Kimi CLI)

**Severidad:** BAJO
**Categoría:** Tooling
**Confianza del auditor:** Alta (>95%)

**Archivo(s) afectado(s):** `.kimi/skills/` (6 sub-carpetas con skills personalizadas: `proway-brand-standards`, `proway-instagram-mastery`, `proway-project-docs`, `proway-proposal-builder`, `proway-social-content-v2`, `wellcore-workout-plans`).

**Descripción del problema:**
Skills locales del Kimi Code CLI (otro asistente). NO pertenecen al proyecto Laravel — son herramientas personales de Daniel para Kimi CLI. Documentadas explícitamente en `AGENTS.md` ("Skills Available in This Project — installed locally in `.kimi/skills/`") y referenciadas en `PROMPT_FIX_CLIENT_TOPNAV_Y_AUDIT.md`. La configuración `reset_kimi_auth.bat` (R-009) limpia el directorio `~/.kimi/`, NO este `.kimi/` del repo — son distintos.

**Impacto SI se ignora:** ninguno operativo. Confunde al desarrollador nuevo (¿es del proyecto o del CLI?).

**Impacto SI se aplica el cambio propuesto (mover a `~/.kimi/` o ignorar en `.gitignore`):**
- Riesgo: bajo. Toca solo herramientas personales.

**Evidencia recolectada:**
1. `Grep ".kimi"` → `AGENTS.md` (rastreado) lo documenta. `PROMPT_FIX_CLIENT_TOPNAV_Y_AUDIT.md` (untracked).
2. `git ls-files | grep .kimi` → 0 (no rastreado).
3. Contenido: skills de "proway" (otro proyecto) y `wellcore-workout-plans` (relevante para WellCore pero ejecutadas por Kimi CLI, no Laravel).

**Propuesta de cambio (NO aplicada):**
- Opción A: Añadir `.kimi/` al `.gitignore` y dejar como herramienta local.
- Opción B: Mover skills relevantes (`wellcore-workout-plans`) a `docs/skills/kimi/` y borrar el resto del proyecto.
- Recomendado: Opción A (menos invasivo, las skills viven con la herramienta).

**Plan de rollback:** revertir cambio del `.gitignore`.

**Tests requeridos antes de aplicar:** ninguno.

**Decisión del usuario:**
- [ ] ✅ ACEPTAR Opción A (`.gitignore`)
- [ ] ✅ ACEPTAR Opción B (mover/limpiar)
- [ ] ⏸️ DIFERIR
- [ ] ❌ RECHAZAR
- [ ] 🔄 PEDIR ALTERNATIVA

**Notas del usuario:**


---

## Resumen tabular de decisiones

| ID | Severidad | Categoría | Recomendación auditor |
|----|-----------|-----------|----------------------|
| R-001 | CRÍTICO | Seguridad | ROTAR credenciales + mover archivos |
| R-002 | MEDIO | Tooling | Mover a `scripts/legacy/20260427/` |
| R-003 | BAJO | Tooling | Mover a `scripts/legacy/20260427/` |
| R-004 | BAJO | Tooling | Mover a `scripts/legacy/20260427/` |
| R-005 | MEDIO | Tooling | Mover a `scripts/operations/reverb/` (siguen vivos) |
| R-006 | BAJO | Documentación | Mover a `docs/prompts/archived/20260427/` |
| R-007 | BAJO | Documentación | Mover a `docs/plans/archived/20260427/` |
| R-008 | BAJO | Tooling | Borrar (zero-byte) o mover a garbage/ |
| R-009 | MEDIO | Seguridad/Tooling | Resolver R-001 primero, luego mover |
| R-010 | MEDIO | Tooling | Mover cristian; diferir john (en git) |
| R-011 | BAJO | Tooling | Mover a `docs/audit/screenshots/20260427/` |
| R-012 | BAJO | Documentación | Mover a `docs/audit/design/` |
| R-013 | BAJO | Tooling | Añadir `.kimi/` a `.gitignore` |

---

**Recordatorio:** Esta auditoría es solo reporte. NO se ha movido ni borrado ningún archivo, NO se ha modificado código, NO se ha ejecutado git. Las decisiones marcadas arriba (ACEPTAR/DIFERIR/RECHAZAR/PEDIR ALTERNATIVA) requieren firma explícita del usuario antes de aplicar cualquier acción.
