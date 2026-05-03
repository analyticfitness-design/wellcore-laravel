# Auditoría de Código WellCore — Scope D (Limpieza raíz) — 2026-04-27

> **Auditor:** Magíster Principal de Ingeniería de Software (juramento Hipocrático del código).
> **Modo:** SOLO REPORTE. Cero acciones aplicadas.
> **Scope D:** archivos sueltos en raíz del repo `C:\Users\GODSF\Herd\wellcore-laravel\`.
> **Reporte de riesgos detallado:** `docs/AUDIT_RISK_REGISTER_20260427_SCOPE_D.md` (R-001 a R-013).

---

## Resumen ejecutivo

| Métrica | Valor |
|---|---|
| Archivos en raíz analizados | ~110 (entre `.ps1`, `.md`, `.txt`, `.php`, `.bat`, basura, carpetas) |
| Candidatos a consolidación | **94** archivos sueltos + 4 carpetas |
| Hallazgos CRÍTICOS | **1** (R-001 — credenciales hardcodeadas en working tree) |
| Hallazgos ALTOS | 0 |
| Hallazgos MEDIOS | **4** (R-002, R-005, R-009, R-010) |
| Hallazgos BAJOS | **8** (R-003, R-004, R-006, R-007, R-008, R-011, R-012, R-013) |
| Líneas/archivos eliminables con evidencia | 94 archivos sueltos (~1.5 MB sin contar screenshots) + 9 archivos zero-byte |
| Estimación horas de cleanup (si se aprueba) | **2-3 h** (rotación credenciales 30 min + estructurar carpeta legacy + `git mv` de los pocos rastreados + actualizar `.gitignore`) |
| Cambios aplicados automáticamente | **0** (modo reporte) |

> ⚠️ La auditoría detectó **un riesgo CRÍTICO de seguridad inesperado** en el scope: 43 scripts `.ps1` y 1 archivo PHP de raíz contienen **credenciales del panel EasyPanel y MySQL producción en texto claro**. Aunque están en `?? ` (untracked), están en el working tree y no hay regla en `.gitignore` que los proteja. Un `git add .` accidental los publicaría a `origin/main`. **Recomiendo actuar primero sobre R-001 (rotar credenciales) antes de cualquier otra cosa.**

---

## Hallazgos por severidad

### CRÍTICO

#### [C-1] Credenciales hardcodeadas en archivos sueltos no versionados (R-001)
- **Archivos:** 43 `.ps1` con prefijo `_` + `check_users.php` (44 archivos en total).
- **Problema:** texto claro de email/password de panel EasyPanel y password de MySQL producción.
- **Riesgo si se ignora:** publicación accidental → RCE de deploy + dump de BD `wellcore_fitness` con datos reales (clientes, pagos Wompi, fotos, planes, comisiones, wellcoins).
- **Evidencia:** `Grep "password|fYCVgn4XZ7twq34|QY@P6Ak2"` → 44 coincidencias. `git ls-files` → 0 versionados. `.gitignore` → no protege estos patrones.
- **Propuesta:** ver R-001. **Rotar ya** las credenciales en EasyPanel + MySQL prod, después mover archivos a `scripts/legacy/20260427/CRITICAL_HAS_SECRETS/` y añadir patrones al `.gitignore`.
- **Plan de rollback:** la rotación no se revierte (es lo correcto); el movimiento de archivos sí (`mv` reverso).
- **Tests requeridos:** confirmar con usuario que ningún flujo activo usa estas credenciales (los `scripts/` activos no las contienen).

### ALTO

(Ninguno detectado en este scope.)

### MEDIO

#### [M-1] Scripts `.ps1` de deploy/EasyPanel one-off (R-002)
- **Archivos:** 16 scripts (`_deploy_*.ps1`, `_easypanel_*.ps1`, `_run_*.ps1`, `_update_*.ps1`).
- **Problema:** scripts de un solo uso (deploy SP-4, gitpull-load, migrate, etc.). Ya no forman parte del flujo.
- **Propuesta:** mover a `scripts/legacy/20260427/`. NO commitear (siguen con secretos hasta resolver R-001).

#### [M-2] Snippets Reverb sueltos (`_reverb_*`) (R-005)
- **Archivos:** 5 (`_reverb_deploy_steps.md`, `_reverb_env_values.txt` (gitignored), `_reverb_healthcheck.sh`, `_reverb_nginx.conf`, `_reverb_start.sh`).
- **Problema:** documentación operativa de un servicio activo (Reverb SP-5 en prod) viviendo en raíz mezclada con basura.
- **Propuesta:** mover a `scripts/operations/reverb/` (NO a legacy — siguen siendo vivos).

#### [M-3] PHP sueltos de debug (R-009)
- **Archivos:** `check_users.php` (CRÍTICO por contraseña — ver R-001), `test_create.php`, `gen_files.php`, `reset_kimi_auth.bat`.
- **Propuesta:** mover a `scripts/legacy/20260427/` después de R-001.

#### [M-4] Carpeta `_audit_jsons/` (R-010)
- **Archivos:** `gen_cristian_v2.py` (untracked, output ya borrado), `gen_john_v1.py` (rastreado en git).
- **Propuesta:** mover el primero a `scripts/legacy/`. **DIFERIR el segundo** porque está en git y mover requiere `git mv` + commit.

### BAJO

#### [B-1] Scripts `.ps1` de verificación one-off (R-003)
- **Archivos:** 16 (`_verify_*.ps1`, `_check_*.ps1`, `_full_verify.ps1`, `_final_verify.ps1`).
- **Propuesta:** mover a `scripts/legacy/20260427/`.

#### [B-2] Scripts `.ps1` de diagnóstico/exploración one-off (R-004)
- **Archivos:** 13-15 (`_clear_*`, `_debug_*`, `_find_*`, `_fix_cristian.ps1`, `_get_lock_*`, `_kill_*`, `_make_*`, `_poll_*`, `_read_*`, `_test_*`) + `_lock_part1.txt`.
- **Propuesta:** mover a `scripts/legacy/20260427/`.

#### [B-3] Documentos `PROMPT_*` sueltos (R-006)
- **Archivos:** 7 (`PROMPT_AUDIT_COACH_DASHBOARD.md` + `.txt` duplicado, `PROMPT_COACH_INVITATION_WOMPI.md`, `PROMPT_REFACTOR_COACH.txt`, `PROMPT_REFACTOR_COACH_DESIGN_SYSTEM.md`, `PROMPT_FIX_CLIENT_TOPNAV_Y_AUDIT.md`, `PROMPT_SONNET_EJECUCION_SECURITY.txt`). El activo (`PROMPT_CODE_REVIEW_MAGISTER.md`) **NO se toca** mientras dure esta auditoría.
- **Hallazgo bonus:** `PROMPT_AUDIT_COACH_DASHBOARD.md` y `.txt` son **el mismo contenido** (diff confirma diferencias de formato únicamente).
- **Propuesta:** mover a `docs/prompts/archived/20260427/`.

#### [B-4] Documentos plan/optimización (R-007)
- **Archivos:** `IMPLEMENTATION_PLAN_HOMEPAGE_V3_FINAL.md`, `OPTIMIZACION_MOBILE_WELLCORE.md`.
- **Propuesta:** mover a `docs/plans/archived/20260427/`.

#### [B-5] Archivos basura y nombres corruptos (R-008)
- **Archivos:** 12 entre zero-byte y outputs huérfanos: `[`, `coach_id,`, `admin_edits_diff,`, `iso_year,`, `name,`, `original_content,`, `value,`, `status-`, `coach?-`, `arch_result.txt`, `test_out.txt`, `_lock_part1.txt`.
- **Propuesta:** borrar (son strictly garbage) o mover a `scripts/legacy/20260427/garbage/`. Decisión del usuario.

#### [B-6] Carpeta `_screenshots_perf/` (R-011)
- **Archivos:** 26 capturas (~4 MB) de auditorías ya cerradas.
- **Propuesta:** mover a `docs/audit/screenshots/20260427/`.

#### [B-7] Carpeta `audit-design/` (R-012)
- **Archivos:** 24 (HTMLs Claude Artifacts + screenshots prod vs design).
- **Propuesta:** mover a `docs/audit/design/`. Actualizar referencia en `AGENTS.md`.

#### [B-8] Carpeta `.kimi/` (R-013)
- **Archivos:** skills locales de Kimi CLI (no del proyecto Laravel).
- **Propuesta:** añadir `.kimi/` al `.gitignore`. Mantener carpeta (skills personales de Daniel).

---

## Lista de archivos con sus categorías de decisión propuestas

### A mover a `scripts/legacy/20260427/` (62 archivos `.ps1` + 3 `.txt` + 4 `.php/.bat` = 69)

**Bloqueado hasta resolver R-001 (credenciales).**

#### `.ps1` con credenciales (43 — ver Anexo A)

#### `.ps1` y `.txt` adicionales sin credenciales pero one-off
(Pocos: la mayoría sí incluye credenciales por usar el patrón `Invoke-WebRequest auth.login`.)

- `_lock_part1.txt`
- `arch_result.txt`, `test_out.txt`

#### Scripts PHP/BAT
- `check_users.php` ← contiene secreto MySQL
- `test_create.php`
- `gen_files.php`
- `reset_kimi_auth.bat`

### A mover a `scripts/operations/reverb/` (5)
- `_reverb_deploy_steps.md`
- `_reverb_env_values.txt` (mantener gitignored)
- `_reverb_healthcheck.sh`
- `_reverb_nginx.conf`
- `_reverb_start.sh`

### A mover a `docs/prompts/archived/20260427/` (7)
- `PROMPT_AUDIT_COACH_DASHBOARD.md`
- `PROMPT_AUDIT_COACH_DASHBOARD.txt` (duplicado del .md)
- `PROMPT_COACH_INVITATION_WOMPI.md`
- `PROMPT_REFACTOR_COACH.txt`
- `PROMPT_REFACTOR_COACH_DESIGN_SYSTEM.md`
- `PROMPT_FIX_CLIENT_TOPNAV_Y_AUDIT.md`
- `PROMPT_SONNET_EJECUCION_SECURITY.txt`

### A mover a `docs/plans/archived/20260427/` (2)
- `IMPLEMENTATION_PLAN_HOMEPAGE_V3_FINAL.md`
- `OPTIMIZACION_MOBILE_WELLCORE.md`

### A mover a `docs/audit/screenshots/20260427/` (1 carpeta)
- `_screenshots_perf/` (26 imágenes)

### A mover a `docs/audit/design/` (1 carpeta)
- `audit-design/` (24 archivos)

### A borrar o mover a garbage/ (12)
- `[`, `coach_id,`, `admin_edits_diff,`, `iso_year,`, `name,`, `original_content,`, `value,`, `status-`, `coach?-`
- `arch_result.txt`, `test_out.txt`, `_lock_part1.txt`

### A `_audit_jsons/` (especial)
- `_audit_jsons/gen_cristian_v2.py` → `scripts/legacy/20260427/audit-generators/`
- `_audit_jsons/gen_john_v1.py` → **DIFERIR** (está en git, decisión separada)

### `.kimi/`
- Añadir patrón `.kimi/` al `.gitignore`. NO mover.

---

## Propuesta de estructura de carpetas destino

```
docs/
├── audit/
│   ├── design/                      # ex audit-design/
│   └── screenshots/
│       └── 20260427/                # ex _screenshots_perf/
├── plans/
│   └── archived/
│       └── 20260427/                # IMPLEMENTATION_PLAN_*, OPTIMIZACION_*
├── prompts/
│   └── archived/
│       └── 20260427/                # PROMPT_* (excepto el activo)
└── AUDIT_RISK_REGISTER_20260427_SCOPE_D.md   # ya creado
└── AUDIT_CODE_REVIEW_20260427_SCOPE_D.md     # este archivo

scripts/
├── operations/
│   └── reverb/                      # ex _reverb_*
└── legacy/
    └── 20260427/
        ├── CRITICAL_HAS_SECRETS/    # _*.ps1 con credenciales (post-rotación)
        ├── audit-generators/        # gen_cristian_v2.py
        ├── garbage/                 # [, coach_id, etc. (alternativa a borrar)
        └── ...                      # resto de _*.ps1, .php, .bat one-off
```

---

## Cambios seguros aplicados automáticamente

**Ninguno.** Esta auditoría es 100% en modo reporte. No se ha:
- Movido ningún archivo.
- Borrado ningún archivo.
- Modificado código fuera de la creación de los 2 documentos en `docs/`.
- Ejecutado git commit.
- Ejecutado git push.
- Cambiado `.gitignore`.

---

## Cambios propuestos NO aplicados (requieren aprobación humana)

Ver `docs/AUDIT_RISK_REGISTER_20260427_SCOPE_D.md`. Cada riesgo (R-001 a R-013) tiene los 4 checkboxes (ACEPTAR/DIFERIR/RECHAZAR/PEDIR ALTERNATIVA) listos para que Daniel firme.

**Orden recomendado de acción:**
1. **R-001** primero — rotar credenciales antes de cualquier otra cosa. Este es el único riesgo CRÍTICO y puede estar comprometido ya.
2. R-002 + R-009 (incluyen los archivos con credenciales — moverlos después de la rotación).
3. R-005 (separar Reverb operations de la basura).
4. R-008 (limpiar archivos basura — ganancia rápida, riesgo cero).
5. R-003 + R-004 + R-006 + R-007 + R-010 + R-011 + R-012 + R-013 (cleanup general).

---

## Anexos

### Anexo A — Lista exhaustiva de los 43 `.ps1` con credenciales (R-001)

Detectados con `Grep "password|fYCVgn4XZ7twq34|QY@P6Ak2"` en `_*.ps1`:

```
_check_manifest.ps1            _easypanel_v2.ps1            _run_artisan_migrate.ps1
_check_nginx_ws.ps1            _final_verify.ps1            _run_gitpull.ps1
_check_scripts_format.ps1      _fix_cristian.ps1            _run_migrate.ps1
_check_vue_manifest.ps1        _fix_nginx_ws.ps1            _run_script_fixed.ps1
_clear_views_debug.ps1         _full_verify.ps1             _test_ws.ps1
_debug_vite.ps1                _get_lock_all.ps1            _update_gitpull_script.ps1
_deploy_final.ps1              _get_lock_part1.ps1          _update_lock.ps1
_deploy_final_verify.ps1       _get_migrate_log.ps1         _verify_contract_gate.ps1
_deploy_ip.ps1                 _kill_opcache_debug.ps1      _verify_deploy.ps1
_easypanel_deploy.ps1          _make_nginx_persistent.ps1   _verify_gate_v2.ps1
_easypanel_deploy_full.ps1     _poll_latest.ps1             _verify_gate_v3.ps1
_easypanel_discover.ps1        _read_nginx.ps1              _verify_reverb.ps1
_easypanel_js.ps1              _verify_silvia.ps1           _verify_silvia2.ps1
_easypanel_probe.ps1           _verify_silvia3.ps1          _verify_silvia4.ps1
_easypanel_run_script.ps1      _verify_silvia_prod.ps1
_easypanel_scripts.ps1         
_easypanel_trpc_names.ps1      
```

Más: `check_users.php` (raíz) — contiene password MySQL prod.

### Anexo B — Patrones recomendados a añadir al `.gitignore`

(Solo después de aprobación R-001):

```gitignore
# Scripts personales / one-off de deploy y debug
/_*.ps1
/_*.txt
/_*.sh
/_*.conf
/_*.md
# Excepciones para los Reverb operations (si se decide mantenerlos en raíz):
# !/_reverb_*

# Archivos PHP/BAT de debug ad-hoc
/check_users.php
/test_create.php
/gen_files.php
/reset_kimi_auth.bat

# Archivos basura por shell mal escapado
/[
/coach_id,
/admin_edits_diff,
/iso_year,
/name,
/original_content,
/value,
/status-
/arch_result.txt
/test_out.txt

# Skills personales del Kimi CLI
.kimi/

# Carpetas de auditoría temporal (mover a docs/audit/ y ignorar nuevas instancias)
/_audit_jsons/
/_screenshots_perf/
/audit-design/
```

### Anexo C — TODOs/FIXMEs/HACK encontrados en archivos del scope

(No aplica en este scope — los archivos del scope son scripts y docs, no código de producción.)

### Anexo D — Imports no usados detectados

(No aplica en este scope.)

---

## Próximos pasos sugeridos (siguiente scope)

Una vez Daniel decida sobre los 13 riesgos de Scope D, el siguiente scope recomendado es:

**Scope B (Frontend Vue 3)** — `resources/js/vue/`. Razón:
- Es el área de migración más activa (project_vue_migration.md), donde más rotación y mayor probabilidad de duplicación/dead code.
- Hay 86 componentes Livewire en migración a Vue, con riesgo de componentes huérfanos a ambos lados.
- ROI alto: bundle size, lazy loading, composables vs lógica en componentes.
- Riesgo controlable con verificación visual en cliente/coach/admin (sec. 8 del prompt magíster).

Alternativa: **Scope A (Backend)** si prefiere atacar primero la capa de datos/servicios donde están los servicios críticos (Wompi, WellCoreGuard, etc.).

**Scope C (Livewire/Blade)** se recomienda al final, porque será reemplazado por Vue 3 — auditarlo agresivamente puede ser tirar plata.

---

**Recordatorio final del juramento Hipocrático:** la auditoría se mide por la confianza que genera, no por las líneas que cambia. Cero acciones tomadas. Todas las decisiones quedan en manos del usuario.
