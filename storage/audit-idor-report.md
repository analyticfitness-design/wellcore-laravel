# P2.6 — IDOR Review Report

Fecha: 2026-04-19
Scope: rutas `/api/v/coach/*` y `/api/v/admin/*` que reciben `{id}` o `client_id` en body/path.

Criterio de riesgo:
- **ALTO** — coach puede acceder a recursos de OTROS coaches sin ownership scope.
- **MEDIO** — acceso permitido pero sin logging; posible escalada de info.
- **BAJO** — admin/superadmin con scope amplio por diseno (OK).

---

## Riesgos ALTOS (hay que mitigar)

### 1. `POST /api/v/coach/plan-tickets` — IDOR en store
- **Archivo:** `app/Http/Controllers/Api/CoachPlanTicketController.php:88-128`
- **Validacion actual:** `'client_id' => ['required', 'integer', Rule::exists('clients', 'id')]`
- **Problema:** el coach puede crear un ticket contra CUALQUIER client_id que exista
  en la tabla, incluso clientes que no son suyos. El `client_id` no se cruza con
  `getCoachClientIds($coach->id)`.
- **Riesgo:** ALTO. Permite enumerar clientes (exists check) y crear tickets
  espureos visibles en otras dashboards.
- **Recomendacion:** antes de `PlanTicket::create`, llamar al metodo de CoachController
  `getCoachClientIds($coach->id)` (via reflexion como hace `CoachClientRequestController::assertOwnsClient`)
  y hacer `abort(403)` si el `client_id` no pertenece al coach.

### 2. `GET /api/v/coach/plan-tickets/autofill` — IDOR en autofill
- **Archivo:** `app/Http/Controllers/Api/CoachPlanTicketController.php:283-292`
- **Validacion actual:** `exists:clients,id`.
- **Problema:** retorna snapshot de perfil, planes previos y checkins de CUALQUIER
  cliente. Filtracion de data sensible entre coaches.
- **Riesgo:** ALTO (data leak, no solo enumeracion).
- **Recomendacion:** misma mitigacion que el item #1 — verificar ownership via
  `getCoachClientIds` antes de invocar `$this->autofill->forClient(...)`.

---

## Riesgos MEDIOS

### 3. `POST /api/v/coach/clients/{id}/requests` — store
- **Archivo:** `app/Http/Controllers/Api/CoachClientRequestController.php:53-98`
- **Validacion:** `assertOwnsClient($coach, $clientId)` — OK.
- **Riesgo:** BAJO. Ownership scope presente. Marcar como verificado.

### 4. `GET /api/v/coach/clients/{id}/requests` — index
- **Archivo:** `app/Http/Controllers/Api/CoachClientRequestController.php:100-111`
- **Validacion:** `assertOwnsClient` + `where('coach_id', $coach->id)` doble check — OK.
- **Riesgo:** BAJO.

### 5. `DELETE /api/v/coach/client-requests/{id}` — cancel
- **Archivo:** `app/Http/Controllers/Api/CoachClientRequestController.php:113-129`
- **Validacion:** `where('coach_id', $coach->id)` — OK, scope directo.
- **Riesgo:** BAJO.

### 6. `POST /api/v/coach/clients/{id}/impersonate`
- **Archivo:** `app/Http/Controllers/Api/CoachController.php:1490-1531`
- **Validacion:** `getCoachClientIds($coach->id)->contains($clientId)` — OK.
- **Riesgo:** BAJO (ahora ademas con throttle:impersonate y audit log).

### 7. `GET /api/v/coach/kanban/detail/{id}`
- **Archivo:** `app/Http/Controllers/Api/CoachController.php:522-545`
- **Validacion:** `AssignedPlan::where('assigned_by', $coachId)->where('client_id', $id)->exists()` — OK.
- **Riesgo:** BAJO.

### 8. `POST /api/v/coach/checkins/{id}/reply`
- **Archivo:** `app/Http/Controllers/Api/CoachController.php:665-690`
- **Validacion:** ownership via `getCoachClientIds` sobre `$checkin->client_id` — OK.
- **Riesgo:** BAJO.

### 9. `GET/PUT/DELETE /api/v/coach/plan-tickets/{id}` (show/update/destroy/duplicate/comments/attachments)
- **Archivo:** `app/Http/Controllers/Api/CoachPlanTicketController.php:133, 148, 230, 251`
- **Validacion:** `PlanTicket::forCoach($coach->id)->find($id)` — scope correcto via scope local.
- **Riesgo:** BAJO.

### 10. `PUT /api/v/coach/profile`, `POST /api/v/coach/notes`, etc.
- **Archivo:** varios.
- **Validacion:** accion siempre opera sobre `$coach->id` (auth user). OK.

---

## Admin endpoints (tratamiento BAJO por diseno)

Todos los endpoints `/api/v/admin/*` reciben `{id}` sin ownership scope — es
intencional: admin/superadmin tienen alcance global. Sin embargo, P2.3 agrega
audit log a las acciones criticas (`client_request.approve/reject`,
`coach.create/update/delete/reset_password`) para trazabilidad.

Endpoints admin sin audit log explicito todavia (considerar agregar en P2.3 extension):
- `DELETE /api/v/admin/clients/{id}` — deleteClient (AdminController:867)
- `PUT /api/v/admin/clients/{id}` — updateClient (AdminController:792)
- `PUT /api/v/admin/plans/{id}` — updatePlan (AdminController:1509)
- `DELETE /api/v/admin/plans/{id}` — deletePlan (AdminController:1552)
- `POST /api/v/admin/clients/{id}/plans` — assignClientPlan (AdminController:1430)

Estos NO tienen IDOR (admin global), pero deberian loggearse en `audit_logs`
para cumplir la politica de trazabilidad de P2.3 en una siguiente iteracion.

---

## Acciones inmediatas recomendadas (fuera del scope de P2.6 — reportar solo)

1. Agregar `assertOwnsClient` helper en `CoachPlanTicketController` (inspirado en
   `CoachClientRequestController::assertOwnsClient`) y llamarlo en `store()` y `autofill()`.
2. Migrar `AdminController` actions criticas al trait `Auditable` creado en P2.3.
3. Considerar middleware `EnsureCoachOwnsClient` parametrizable para dry-run
   centralizado (mediano plazo).
