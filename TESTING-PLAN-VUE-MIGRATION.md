# Plan de Pruebas — Migración Vue 3 SPA (WellCore Fitness)

## Contexto para el agente de testing

WellCore Fitness migró componentes Livewire → Vue 3 SPA.
La SPA corre bajo el prefijo `/v/*`. Las rutas Livewire originales siguen funcionando en paralelo.

**Stack Vue:** Vue 3.5 + Pinia + Vue Router 4 + Axios (Bearer token)
**Auth localStorage keys:** `wc_token`, `wc_user_type`, `wc_user_id`
**Mount point HTML:** `<div id="vue-app">` (no `#app`)
**URL local:** `http://wellcore-laravel.test` (Laravel Herd)
**Credenciales test:** `daniel.esparza` / `RISE2026Admin!SuperPower` (superadmin)

---

## Setup del agente antes de empezar

```
1. Verificar que Chrome está corriendo con debug port:
   "/c/Program Files/Google/Chrome/Application/chrome.exe" --remote-debugging-port=9222 --user-data-dir="C:/Users/GODSF/chrome-debug-profile" &

2. Confirmar MCP disponible: /mcp debe mostrar chrome-devtools conectado

3. URL base: http://wellcore-laravel.test

4. Para verificar auth en cualquier test:
   evaluate_script: localStorage.getItem('wc_token')          // token activo
   evaluate_script: localStorage.getItem('wc_user_type')      // rol del usuario
   evaluate_script: localStorage.getItem('wc_user_id')        // id del usuario
```

---

## BLOQUE 0 — Infraestructura Vue SPA

### T-00.1: Vue app monta correctamente
```
Navegar a: http://wellcore-laravel.test/v/login
Verificar:
  - [ ] No hay errores 404 en recursos (app.js, vue router chunks)
  - [ ] No hay errores en consola del navegador
  - [ ] El div#vue-app tiene contenido renderizado (no vacío)
  - [ ] El title del tab dice "Iniciar Sesion — WellCore"
evaluate_script: document.getElementById('vue-app').innerHTML.length > 100
  → debe retornar true
```

### T-00.2: Vite build chunks — lazy loading correcto
```
Navegar a: http://wellcore-laravel.test/v/login
list_network_requests, filtrar por .js:
  - [ ] Se carga app-*.js (bundle principal, debe ser <500KB)
  - [ ] NO existe un chunk único de 990KB (bug pre-fix)
  - [ ] Al navegar a otra ruta, se carga un chunk adicional (lazy loading activo)
Navegar a /v/client/dashboard (con login previo):
  - [ ] Aparece un nuevo .js chunk en network (Dashboard cargado on-demand)
```

### T-00.3: Vue Router history mode — catch-all funciona
```
Navegar directamente (URL en barra del navegador) a: http://wellcore-laravel.test/v/client
  - [ ] Responde con HTML (no 404 de servidor)
  - [ ] Vue Router toma control y redirige a /v/login (si no autenticado)
Navegar a: http://wellcore-laravel.test/v/ruta-que-no-existe
  - [ ] No retorna 404 de servidor — Vue Router maneja la ruta
```

### T-00.4: Content Security Policy no bloquea Vue
```
Navegar a: http://wellcore-laravel.test/v/login
list_console_messages:
  - [ ] Sin errores "Content Security Policy" en consola
  - [ ] Sin errores "Refused to load script" o "Refused to load style"
  - [ ] Sin errores relacionados con fonts.googleapis.com
```

### T-00.5: Dark mode aplicado desde blade
```
evaluate_script: document.documentElement.classList.contains('dark')
  → debe ser true (vue.blade.php aplica dark mode por defecto)
evaluate_script: document.body.className
  → debe incluir "bg-wc-bg text-wc-text"
```

---

## BLOQUE 1 — Autenticación

### T-01.1: Página de Login renderiza
```
Navegar a: http://wellcore-laravel.test/v/login
take_screenshot()
Verificar:
  - [ ] Formulario visible con campos email y password
  - [ ] Logo WellCore visible
  - [ ] Botón "Iniciar sesión" presente
  - [ ] Link "Olvidé mi contraseña" presente
  - [ ] Fondo oscuro con colores WellCore (#DC2626 rojo accent)
```

### T-01.2: Login con credenciales incorrectas
```
fill [campo email]: "incorrecto@test.com"
fill [campo password]: "wrongpassword"
click [botón Iniciar sesión]
Verificar:
  - [ ] Mensaje de error visible ("Credenciales incorrectas" o similar)
  - [ ] NO redirige a dashboard
  - [ ] NO hay token en localStorage
evaluate_script: localStorage.getItem('wc_token')  →  null
```

### T-01.3: Login exitoso — superadmin
```
fill [campo email]: "daniel.esparza"
fill [campo password]: "RISE2026Admin!SuperPower"
click [botón Iniciar sesión]
Verificar:
  - [ ] Redirige a /v/client o /v/admin
  - [ ] wc_token en localStorage (string de 64 chars hex)
  - [ ] wc_user_type en localStorage (debe ser "superadmin" o "admin")
  - [ ] No hay errores en consola
evaluate_script: localStorage.getItem('wc_token')?.length  →  64
evaluate_script: localStorage.getItem('wc_user_type')       →  rol del usuario
list_network_requests: POST /api/v/auth/login → status 200
```

### T-01.4: Guard — ruta protegida sin auth redirige a login
```
(Sesión sin login — limpiar localStorage primero)
evaluate_script: localStorage.clear()
Navegar a: http://wellcore-laravel.test/v/client
  - [ ] Redirige automáticamente a /v/login
  - [ ] URL en barra de dirección es /v/login
```

### T-01.5: Guard — usuario autenticado no puede ir a /login
```
(Con sesión activa de T-01.3)
Navegar a: http://wellcore-laravel.test/v/login
  - [ ] Redirige automáticamente a /v/client (no muestra formulario de login)
```

### T-01.6: Logout limpia localStorage y redirige
```
(Con sesión activa)
Encontrar y click [botón Logout / Cerrar sesión] en navbar o menú
Verificar:
  - [ ] wc_token eliminado de localStorage
  - [ ] wc_user_type eliminado de localStorage
  - [ ] wc_user_id eliminado de localStorage
  - [ ] Redirige a /v/login
  - [ ] POST /api/v/auth/logout llamado
evaluate_script: localStorage.getItem('wc_token')  →  null
```

### T-01.7: Forgot Password — envío de email
```
Navegar a: http://wellcore-laravel.test/v/forgot-password
Verificar:
  - [ ] Formulario de recuperación renderiza
  - [ ] Campo email visible
fill [campo email]: "test@ejemplo.com"
click [botón enviar]
  - [ ] Mensaje de confirmación visible (sin error 500)
list_network_requests: POST /api/v/auth/forgot-password → status 200
```

### T-01.8: Reset Password — página con token en URL
```
Navegar a: http://wellcore-laravel.test/v/reset-password/token-fake-123abc
Verificar:
  - [ ] Página renderiza (no 404, no pantalla en blanco)
  - [ ] Formulario con campos: email, nueva contraseña, confirmar contraseña
  - [ ] El parámetro :token del URL es accesible para el formulario
  - [ ] Sin errores de consola JS al cargar la ruta dinámica
  - [ ] POST /api/v/auth/reset-password llamado al enviar (puede fallar por token inválido — eso es correcto)
```

---

## BLOQUE 2 — Formularios Públicos

### T-02.1: Inscripción — carga sin auth
```
Navegar a: http://wellcore-laravel.test/v/inscripcion
Verificar:
  - [ ] Página carga sin requerir autenticación
  - [ ] Step 0 visible: selección de plan (Esencial, Método, Elite)
  - [ ] CSS WellCore aplicado (fondo oscuro, rojo #DC2626)
  - [ ] Sin errores de consola
```

### T-02.2: Inscripción — flujo multi-step
```
click [plan Método]
  - [ ] Avanza a Step 1 (datos personales)
fill nombre, email, whatsapp, edad, peso, estatura
click [Siguiente]
  - [ ] Avanza a Step 2 (experiencia)
click [Anterior]
  - [ ] Regresa a Step 1 con datos pre-cargados (no se borran)
```

### T-02.3: Coach Application
```
Navegar a: http://wellcore-laravel.test/v/coaches/apply
Verificar:
  - [ ] Formulario renderiza sin auth
  - [ ] Campos del formulario visibles
  - [ ] POST /api/v/public/coach-apply al enviar
```

### T-02.4: RISE Enrollment
```
Navegar a: http://wellcore-laravel.test/v/rise-enroll
Verificar:
  - [ ] Formulario de enrollment renderiza sin auth
  - [ ] POST /api/v/public/rise-enroll al enviar
```

### T-02.5: Formulario Presencial
```
Navegar a: http://wellcore-laravel.test/v/presencial/inscripcion
Verificar:
  - [ ] Formulario renderiza sin auth
  - [ ] Campos: nombre, apellido, email, whatsapp, edad, objetivo, experiencia, horario, días
  - [ ] POST /api/v/public/presencial al enviar
```

---

## BLOQUE 3 — Tienda / Shop

### T-03.1: Catálogo de productos
```
Navegar a: http://wellcore-laravel.test/v/tienda
Verificar:
  - [ ] Lista de productos carga
  - [ ] Sin auth requerida
  - [ ] Cards de productos visibles con imagen, nombre, precio
  - [ ] Imágenes cargan (no broken images)
list_network_requests: GET /api/v/shop/products → status 200
```

### T-03.2: Detalle de producto
```
click [un producto del catálogo]
  - [ ] URL cambia a /v/tienda/:slug
  - [ ] Datos del producto visibles (nombre, precio, descripción)
list_network_requests: GET /api/v/shop/products/:slug → status 200
```

---

## BLOQUE 4 — Dashboard Cliente

### T-04.1: Dashboard principal
```
(Logueado)
Navegar a: http://wellcore-laravel.test/v/client
Verificar:
  - [ ] Dashboard renderiza con datos reales
  - [ ] Nombre del usuario visible
  - [ ] Sin errores 401/403 en network
  - [ ] ClientLayout aplicado (navbar/sidebar correctos)
list_network_requests: GET /api/v/client/dashboard → status 200
```

### T-04.2: Métricas
```
Navegar a: http://wellcore-laravel.test/v/client/metrics
  - [ ] Página carga
  - [ ] Gráficas Chart.js renderizadas (no canvas vacío)
list_network_requests: GET /api/v/client/metrics → status 200
```

### T-04.3: Perfil
```
Navegar a: http://wellcore-laravel.test/v/client/profile
  - [ ] Formulario con datos del usuario pre-cargados (no campos vacíos)
  - [ ] Campos editables visibles
list_network_requests: GET /api/v/client/profile → status 200
```

### T-04.4: Configuración
```
Navegar a: http://wellcore-laravel.test/v/client/settings
  - [ ] Opciones de configuración visibles
  - [ ] Sección de cambio de contraseña presente
list_network_requests: GET /api/v/client/settings → status 200
```

### T-04.5: Notificaciones
```
Navegar a: http://wellcore-laravel.test/v/client (o cualquier página)
  - [ ] Badge de notificaciones visible en navbar (si hay notificaciones)
list_network_requests: GET /api/v/client/notifications → status 200
```

---

## BLOQUE 5 — Entrenamiento

### T-05.1: Plan de entrenamiento
```
Navegar a: http://wellcore-laravel.test/v/client/plan
  - [ ] Plan del cliente carga (semanas, ejercicios)
list_network_requests: GET /api/v/client/plan → status 200
```

### T-05.2: Calendario de entrenamiento
```
Navegar a: http://wellcore-laravel.test/v/client/training
  - [ ] Calendario con días de entrenamiento carga
  - [ ] Días completados marcados visualmente (diferente color)
list_network_requests: GET /api/v/client/training → status 200
```

### T-05.3: WorkoutPlayer — carga del día actual
```
Navegar a: http://wellcore-laravel.test/v/client/workout
  - [ ] Ejercicios del día visibles
  - [ ] Botones de completar set / log visibles
list_network_requests: GET /api/v/client/workout → status 200
```

### T-05.4: WorkoutPlayer — día específico por parámetro
```
Navegar a: http://wellcore-laravel.test/v/client/workout/1
  - [ ] Carga entrenamiento del día 1
  - [ ] Parámetro 'day' procesado correctamente
list_network_requests: GET /api/v/client/workout/1 → status 200
```

### T-05.5: Timer de entrenamiento
```
Navegar a: http://wellcore-laravel.test/v/client/timer
  - [ ] Timer renderiza
  - [ ] Controles (play, pause, reset) visibles y clicables
  - [ ] Texto del timer legible (bug anterior: texto invisible en light mode)
take_screenshot()
```

### T-05.6: Workout Summary
```
Navegar a: http://wellcore-laravel.test/v/client/workout-summary/1
  - [ ] Página renderiza o maneja 404 sin crash de SPA
  - [ ] Layout correcto aplicado
```

### T-05.7: Check-in semanal
```
Navegar a: http://wellcore-laravel.test/v/client/checkin
  - [ ] Formulario de check-in carga
  - [ ] Campos de métricas visibles (peso, fotos, estado anímico, etc.)
list_network_requests: GET /api/v/client/checkin → status 200
```

---

## BLOQUE 6 — Social & Recursos

### T-06.1: Comunidad
```
Navegar a: http://wellcore-laravel.test/v/client/community
  - [ ] Feed de posts carga (posts o mensaje "sin publicaciones")
list_network_requests: GET /api/v/client/community → status 200
```

### T-06.2: Retos
```
Navegar a: http://wellcore-laravel.test/v/client/challenges
  - [ ] Lista de retos activos carga
list_network_requests: GET /api/v/client/challenges → status 200
```

### T-06.3: Chat cliente
```
Navegar a: http://wellcore-laravel.test/v/client/chat
  - [ ] Interfaz de chat renderiza
  - [ ] Historial de mensajes visible o estado vacío
  - [ ] Input para escribir mensaje presente
list_network_requests: GET /api/v/client/chat → status 200
```

### T-06.4: Plan de nutrición
```
Navegar a: http://wellcore-laravel.test/v/client/nutrition
  - [ ] Plan nutricional carga (comidas del día)
  - [ ] Botones de toggle agua visibles
list_network_requests: GET /api/v/client/nutrition → status 200
```

### T-06.5: Hábitos
```
Navegar a: http://wellcore-laravel.test/v/client/habits
  - [ ] Lista de hábitos del cliente carga
  - [ ] Checkboxes/botones de toggle visibles por hábito
list_network_requests: GET /api/v/client/habits → status 200
```

### T-06.6: Referidos
```
Navegar a: http://wellcore-laravel.test/v/client/referrals
  - [ ] Código/link de referido visible
  - [ ] Lista de referidos (o estado vacío)
list_network_requests: GET /api/v/client/referrals → status 200
```

### T-06.7: Suplementación
```
Navegar a: http://wellcore-laravel.test/v/client/supplements
  - [ ] Lista de suplementos del plan carga
  - [ ] Botones de toggle por suplemento
list_network_requests: GET /api/v/client/supplements → status 200
```

### T-06.8: Fotos de progreso
```
Navegar a: http://wellcore-laravel.test/v/client/photos
  - [ ] Galería de fotos carga (o estado vacío)
  - [ ] Botón de subir foto visible
list_network_requests: GET /api/v/client/photos → status 200
```

### T-06.9: Personal Records
```
Navegar a: http://wellcore-laravel.test/v/client/records
  - [ ] Records personales cargan (o estado vacío)
list_network_requests: GET /api/v/client/records → status 200
```

### T-06.10: AI Nutrition
```
Navegar a: http://wellcore-laravel.test/v/client/ai-nutrition
  - [ ] Interfaz de análisis IA carga
  - [ ] Sin errores de CSP para api.anthropic.com
  - [ ] Input para subir foto o describir alimentos visible
list_console_messages: sin errores CSP ni 401
```

---

## BLOQUE 7 — Programa RISE

### T-07.1: Dashboard RISE
```
(Logueado como usuario RISE o superadmin)
Navegar a: http://wellcore-laravel.test/v/rise
  - [ ] Dashboard RISE renderiza
  - [ ] RiseLayout aplicado (diferente al ClientLayout)
list_network_requests: GET /api/v/rise/dashboard → status 200
```

### T-07.2: Programa RISE
```
Navegar a: http://wellcore-laravel.test/v/rise/program
  - [ ] Semanas y módulos del programa visibles
list_network_requests: GET /api/v/rise/program → status 200
```

### T-07.3: Tracking diario RISE
```
Navegar a: http://wellcore-laravel.test/v/rise/tracking
  - [ ] Vista de tracking del día carga
  - [ ] Campos de registro diario visibles
```

### T-07.4: Hábitos RISE
```
Navegar a: http://wellcore-laravel.test/v/rise/habits
  - [ ] Hábitos del programa RISE cargan
  - [ ] Botones de toggle visibles
list_network_requests: GET /api/v/rise/habits → status 200
```

### T-07.5: Mediciones RISE
```
Navegar a: http://wellcore-laravel.test/v/rise/measurements
  - [ ] Formulario de mediciones corporales carga
  - [ ] Historial de mediciones visible
list_network_requests: GET /api/v/rise/measurements → status 200
```

### T-07.6: WorkoutPlayer RISE
```
Navegar a: http://wellcore-laravel.test/v/rise/workout
  - [ ] Player de entrenamiento RISE carga
  - [ ] Ejercicios visibles
list_network_requests: GET /api/v/rise/workout → status 200
```

### T-07.7: Perfil RISE
```
Navegar a: http://wellcore-laravel.test/v/rise/profile
  - [ ] Perfil RISE renderiza con datos del usuario
list_network_requests: GET /api/v/rise/profile → status 200
```

### T-07.8: Chat RISE
```
Navegar a: http://wellcore-laravel.test/v/rise/chat
  - [ ] Interfaz de chat RISE renderiza
  - [ ] Historial de mensajes visible (o vacío)
  - [ ] Input de mensaje presente
list_network_requests: GET /api/v/rise/chat → status 200
```

### T-07.9: Fotos RISE
```
Navegar a: http://wellcore-laravel.test/v/rise/photos
  - [ ] Galería de fotos RISE carga
  - [ ] Botón de subir foto visible
list_network_requests: GET /api/v/rise/photos → status 200
```

### T-07.10: Workout Summary RISE
```
Navegar a: http://wellcore-laravel.test/v/rise/workout-summary/1
  - [ ] Página renderiza o maneja 404 limpiamente (sin crash SPA)
  - [ ] Layout RISE aplicado
```

---

## BLOQUE 8 — Portal Coach

### T-08.1: Dashboard Coach
```
(Logueado como coach/admin/superadmin)
Navegar a: http://wellcore-laravel.test/v/coach
  - [ ] Dashboard coach renderiza (no 403)
  - [ ] CoachLayout aplicado
  - [ ] Métricas de clientes visibles
list_network_requests: GET /api/v/coach/dashboard → status 200
```

### T-08.2: Lista de clientes del coach
```
Navegar a: http://wellcore-laravel.test/v/coach/clients
  - [ ] Lista de clientes asignados carga
  - [ ] Datos básicos visibles (nombre, plan, estado)
list_network_requests: GET /api/v/coach/clients → status 200
```

### T-08.3: Kanban de clientes
```
Navegar a: http://wellcore-laravel.test/v/coach/kanban
  - [ ] Board Kanban renderiza con columnas (ej: Activo, En riesgo, Inactivo)
  - [ ] Clientes distribuidos en columnas
list_network_requests: GET /api/v/coach/kanban → status 200
```

### T-08.4: Revisión de check-ins
```
Navegar a: http://wellcore-laravel.test/v/coach/checkins
  - [ ] Lista de check-ins de clientes carga
list_network_requests: GET /api/v/coach/checkins → status 200
```

### T-08.5: Centro de mensajes
```
Navegar a: http://wellcore-laravel.test/v/coach/messages
  - [ ] Conversaciones/mensajes cargan
  - [ ] Input para responder presente
list_network_requests: GET /api/v/coach/messages → status 200
```

### T-08.6: Planes del coach
```
Navegar a: http://wellcore-laravel.test/v/coach/plans
  - [ ] Lista de planes creados carga
  - [ ] Botón crear nuevo plan visible
list_network_requests: GET /api/v/coach/plans → status 200
```

### T-08.7: Analytics del coach
```
Navegar a: http://wellcore-laravel.test/v/coach/analytics
  - [ ] Gráficas y métricas de clientes cargan
list_network_requests: GET /api/v/coach/analytics → status 200
```

### T-08.8: Perfil del coach
```
Navegar a: http://wellcore-laravel.test/v/coach/profile
  - [ ] Formulario de perfil pre-cargado con datos reales
list_network_requests: GET /api/v/coach/profile → status 200
```

### T-08.9: Mi Marca
```
Navegar a: http://wellcore-laravel.test/v/coach/brand
  - [ ] Editor de marca carga
  - [ ] Campos para colores, logo, nombre de marca visibles
list_network_requests: GET /api/v/coach/brand → status 200
```

### T-08.10: Broadcast Center
```
Navegar a: http://wellcore-laravel.test/v/coach/broadcast
  - [ ] Interfaz de mensajería masiva renderiza
  - [ ] Selector de destinatarios visible
  - [ ] Área de redacción de mensaje visible
```

### T-08.11: Notas del coach
```
Navegar a: http://wellcore-laravel.test/v/coach/notes
  - [ ] Lista de notas carga
  - [ ] Botón crear nueva nota visible
list_network_requests: GET /api/v/coach/notes → status 200
```

### T-08.12: Herramientas del coach
```
Navegar a: http://wellcore-laravel.test/v/coach/features
  - [ ] Página de herramientas renderiza
list_network_requests: GET /api/v/coach/features → status 200
```

### T-08.13: Recursos del coach
```
Navegar a: http://wellcore-laravel.test/v/coach/resources
  - [ ] Recursos/materiales visibles o estado vacío
list_network_requests: GET /api/v/coach/resources → status 200
```

---

## BLOQUE 9 — Panel Admin

### T-09.1: Dashboard Admin
```
(Logueado como superadmin)
Navegar a: http://wellcore-laravel.test/v/admin
  - [ ] Dashboard admin renderiza (no 403)
  - [ ] AdminLayout aplicado
  - [ ] KPIs globales visibles (clientes activos, revenue, etc.)
list_network_requests: GET /api/v/admin/dashboard → status 200
```

### T-09.2: Live Feed
```
Navegar a: http://wellcore-laravel.test/v/admin/feed
  - [ ] Feed de actividad en tiempo real carga
list_network_requests: GET /api/v/admin/feed → status 200
```

### T-09.3: Tabla de clientes
```
Navegar a: http://wellcore-laravel.test/v/admin/clients
  - [ ] Tabla con todos los clientes carga
  - [ ] Paginación o scroll infinito funciona
list_network_requests: GET /api/v/admin/clients → status 200
```

### T-09.4: Detalle de cliente
```
click [un cliente de la tabla]
  - [ ] URL cambia a /v/admin/clients/:id
  - [ ] Datos completos del cliente visibles
list_network_requests: GET /api/v/admin/clients/:id → status 200
```

### T-09.5: Dashboard de pagos
```
Navegar a: http://wellcore-laravel.test/v/admin/payments
  - [ ] Historial y métricas de pagos cargan
list_network_requests: GET /api/v/admin/payments → status 200
```

### T-09.6: Gestión de coaches
```
Navegar a: http://wellcore-laravel.test/v/admin/coaches
  - [ ] Lista de coaches carga
  - [ ] Botón agregar coach visible
list_network_requests: GET /api/v/admin/coaches → status 200
```

### T-09.7: Gestión de planes
```
Navegar a: http://wellcore-laravel.test/v/admin/plans
  - [ ] Lista de planes del sistema carga
list_network_requests: GET /api/v/admin/plans → status 200
```

### T-09.8: Inscripciones
```
Navegar a: http://wellcore-laravel.test/v/admin/inscriptions
  - [ ] Lista de inscripciones pendientes/completadas carga
list_network_requests: GET /api/v/admin/inscriptions → status 200
```

### T-09.9: Invitaciones
```
Navegar a: http://wellcore-laravel.test/v/admin/invitations
  - [ ] Gestor de invitaciones carga
  - [ ] Botón crear invitación visible
list_network_requests: GET /api/v/admin/invitations → status 200
```

### T-09.10: RISE Management
```
Navegar a: http://wellcore-laravel.test/v/admin/rise
  - [ ] Panel de gestión RISE carga
list_network_requests: GET /api/v/admin/rise → status 200
```

### T-09.11: Configuración Admin
```
Navegar a: http://wellcore-laravel.test/v/admin/settings
  - [ ] Formulario de configuración global carga
list_network_requests: GET /api/v/admin/settings → status 200
```

### T-09.12: AI Generator
```
Navegar a: http://wellcore-laravel.test/v/admin/ai-generator
  - [ ] Interfaz del generador IA carga
  - [ ] Sin errores de CSP para api.anthropic.com
  - [ ] Formulario para generar plan visible
```

### T-09.13: Chat Analytics
```
Navegar a: http://wellcore-laravel.test/v/admin/chat-analytics
  - [ ] Métricas y analytics de chat cargan
list_network_requests: GET /api/v/admin/chat-analytics → status 200
```

### T-09.14: Tickets
```
Navegar a: http://wellcore-laravel.test/v/admin/tickets
  - [ ] Gestor de tickets de soporte carga
```

### T-09.15: Referidos Admin
```
Navegar a: http://wellcore-laravel.test/v/admin/referrals
  - [ ] Panel de recompensas por referidos carga
```

### T-09.16: Campañas
```
Navegar a: http://wellcore-laravel.test/v/admin/campaigns
  - [ ] Tracker de campañas carga
```

### T-09.17: Enviar Invitación
```
Navegar a: http://wellcore-laravel.test/v/admin/send-invitation
  - [ ] Formulario de envío de invitación individual carga
```

### T-09.18: Admin Tools
```
Navegar a: http://wellcore-laravel.test/v/admin/tools
  - [ ] Página de herramientas admin renderiza
  - [ ] Sin errores de consola
```

---

## BLOQUE 10 — Cross-Cutting

### T-10.1: Bearer token enviado en cada request
```
(Logueado)
evaluate_script: localStorage.getItem('wc_token')  →  string de 64 chars
list_network_requests (después de navegar a cualquier página autenticada):
  - [ ] Requests a /api/v/* tienen header "Authorization: Bearer <token>"
  - [ ] No hay requests a /api/v/client/* con status 401
```

### T-10.2: Token inválido — redirige a login automáticamente
```
evaluate_script: localStorage.setItem('wc_token', 'token-invalido-de-prueba')
Navegar a: http://wellcore-laravel.test/v/client/dashboard
  - [ ] useApi interceptor detecta 401
  - [ ] wc_token eliminado de localStorage
  - [ ] Redirige a /v/login (no pantalla en blanco)
evaluate_script: localStorage.getItem('wc_token')  →  null
```

### T-10.3: Coexistencia Livewire + Vue — sin conflictos
```
Navegar a ruta Livewire: http://wellcore-laravel.test/client
  - [ ] Versión Livewire sigue funcionando
  - [ ] Vue Router no interfiere
Navegar a ruta Vue: http://wellcore-laravel.test/v/client
  - [ ] Vue SPA funciona independiente
  - [ ] Ambas rutas coexisten sin conflicto
```

### T-10.4: Dark mode persiste entre recargas
```
evaluate_script: localStorage.getItem('darkMode')
  → valor debe ser 'true' o null (dark es el default)
evaluate_script: document.documentElement.classList.contains('dark')
  → true
Recargar página (F5):
  - [ ] Dark mode persiste (no flash de tema claro)
```

### T-10.5: Responsive — viewport móvil
```
Cambiar viewport a 375x812 (iPhone SE)
take_screenshot()
Navegar a: http://wellcore-laravel.test/v/client
  - [ ] Navbar adapta para móvil (hamburger menu o similar)
  - [ ] Sin scroll horizontal
Navegar a: http://wellcore-laravel.test/v/login
  - [ ] Formulario usable en pantalla pequeña
```

### T-10.6: Pinia auth store — estado correcto
```
(Con sesión activa)
evaluate_script:
  JSON.stringify({
    token: localStorage.getItem('wc_token'),
    userType: localStorage.getItem('wc_user_type'),
    userId: localStorage.getItem('wc_user_id'),
    isAuthenticated: !!localStorage.getItem('wc_token')
  })
Verificar:
  - [ ] token: string de 64 chars (no null)
  - [ ] userType: "superadmin" (para creds de test)
  - [ ] userId: número (no null)
  - [ ] isAuthenticated: true
```

---

## BLOQUE 11 — Flujos Interactivos (POST / PUT / DELETE)

> Este bloque prueba las mutaciones de datos: formularios que envían, botones que cambian estado, y operaciones CRUD. Son los flujos más propensos a romper silenciosamente.

### T-11.1: Guardar nueva métrica (cliente)
```
Navegar a: http://wellcore-laravel.test/v/client/metrics
Encontrar formulario de registro de nueva métrica
fill [campo peso]: "75.5"
click [Guardar / Registrar]
list_network_requests: POST /api/v/client/metrics
  - [ ] Status 200 o 201
  - [ ] Métrica aparece en la lista/gráfica (sin recargar o tras reload)
```

### T-11.2: Actualizar perfil del cliente
```
Navegar a: http://wellcore-laravel.test/v/client/profile
Modificar un campo (ej: ciudad)
click [Guardar / Actualizar perfil]
list_network_requests: PUT /api/v/client/profile
  - [ ] Status 200
  - [ ] Cambio persiste al recargar la página
```

### T-11.3: Cambiar contraseña del cliente
```
Navegar a: http://wellcore-laravel.test/v/client/settings
Encontrar sección cambio de contraseña
fill [contraseña actual]: "RISE2026Admin!SuperPower"
fill [nueva contraseña]: "NuevaPass2026!"
fill [confirmar contraseña]: "NuevaPass2026!"
click [Cambiar contraseña]
list_network_requests: PUT /api/v/client/settings/password
  - [ ] Status 200 (o error de validación apropiado)
  - [ ] Sin crash de la SPA
```

### T-11.4: Flujo completo de entrenamiento — start → sets → finish
```
Navegar a: http://wellcore-laravel.test/v/client/workout
click [Iniciar entrenamiento / Empezar]
list_network_requests: POST /api/v/client/workout/start → status 200
  - [ ] Vista cambia a modo entrenamiento activo

click [Completar set] en el primer ejercicio
list_network_requests: POST /api/v/client/workout/complete-set → status 200
  - [ ] Set marcado como completado visualmente

click [Finalizar entrenamiento]
list_network_requests: POST /api/v/client/workout/finish → status 200
  - [ ] Redirige a /v/client/workout-summary/:sessionId
  - [ ] URL contiene el sessionId de la sesión
```

### T-11.5: Calificar entrenamiento (feeling post-workout)
```
(Después de T-11.4, en la página de summary)
Encontrar sección de calificación/sentimiento
click [una opción: 😊 / 😐 / 😞 o similar]
list_network_requests: POST /api/v/client/workout-summary/:id/feeling → status 200
  - [ ] Calificación guardada sin error
```

### T-11.6: Enviar check-in semanal
```
Navegar a: http://wellcore-laravel.test/v/client/checkin
Completar los campos del formulario (peso, fotos opcionales, notas)
click [Enviar check-in]
list_network_requests: POST /api/v/client/checkin → status 200 o 201
  - [ ] Mensaje de confirmación visible
  - [ ] Sin error 422 (validación) si se completaron los campos requeridos
```

### T-11.7: Publicar en comunidad
```
Navegar a: http://wellcore-laravel.test/v/client/community
Encontrar input/botón para crear publicación
fill [texto del post]: "Test de publicación desde Vue SPA"
click [Publicar]
list_network_requests: POST /api/v/client/community → status 200 o 201
  - [ ] Post aparece en el feed (sin recargar o tras reload)
```

### T-11.8: Reaccionar a un post de comunidad
```
(En la página de comunidad, con al menos un post visible)
click [botón de reacción ❤️ / 👍 de un post]
list_network_requests: POST /api/v/client/community/:id/react → status 200
  - [ ] Contador de reacciones actualiza visualmente
```

### T-11.9: Comentar en un post
```
Encontrar sección de comentarios en un post
fill [campo comentario]: "Comentario de prueba"
click [Comentar / Enviar]
list_network_requests: POST /api/v/client/community/:id/comment → status 200
  - [ ] Comentario aparece bajo el post
```

### T-11.10: Unirse a un reto
```
Navegar a: http://wellcore-laravel.test/v/client/challenges
click [Unirse / Join] en un reto disponible
list_network_requests: POST /api/v/client/challenges/:id/join → status 200
  - [ ] Estado del reto cambia a "Inscrito" o similar
```

### T-11.11: Enviar mensaje en chat del cliente
```
Navegar a: http://wellcore-laravel.test/v/client/chat
fill [input de mensaje]: "Mensaje de prueba desde Vue"
click [Enviar] o presionar Enter
list_network_requests: POST /api/v/client/chat → status 200
  - [ ] Mensaje aparece en el hilo de conversación
```

### T-11.12: Toggle agua en nutrición
```
Navegar a: http://wellcore-laravel.test/v/client/nutrition
click [un vaso de agua / toggle de agua]
list_network_requests: POST /api/v/client/nutrition/water → status 200
  - [ ] Indicador visual actualiza (vaso marcado/desmarcado)
```

### T-11.13: Toggle hábito del cliente
```
Navegar a: http://wellcore-laravel.test/v/client/habits
click [toggle/checkbox de un hábito]
list_network_requests: POST /api/v/client/habits/toggle → status 200
  - [ ] Hábito marcado como completado visualmente
```

### T-11.14: Toggle suplemento
```
Navegar a: http://wellcore-laravel.test/v/client/supplements
click [toggle de un suplemento]
list_network_requests: POST /api/v/client/supplements/toggle → status 200
  - [ ] Suplemento marcado visualmente
```

### T-11.15: Invitar referido
```
Navegar a: http://wellcore-laravel.test/v/client/referrals
fill [campo email del referido]: "amigo@test.com"
click [Invitar / Enviar]
list_network_requests: POST /api/v/client/referrals/invite → status 200
  - [ ] Mensaje de confirmación visible
```

### T-11.16: Subir foto de progreso
```
Navegar a: http://wellcore-laravel.test/v/client/photos
click [Subir foto / Upload]
(Seleccionar una imagen JPG de prueba del filesystem)
list_network_requests: POST /api/v/client/photos → status 200 o 201
  - [ ] Foto aparece en la galería
  - [ ] Sin error de Content-Type o de tamaño
```

### T-11.17: Eliminar foto de progreso
```
(Con al menos una foto en la galería)
click [Eliminar / Delete] en una foto
list_network_requests: DELETE /api/v/client/photos/:id → status 200
  - [ ] Foto desaparece de la galería
```

### T-11.18: Analizar nutrición con IA
```
Navegar a: http://wellcore-laravel.test/v/client/ai-nutrition
Subir imagen de alimento o describir comida en texto
click [Analizar]
list_network_requests: POST /api/v/client/ai-nutrition/analyze
  - [ ] Status 200 (no 500 ni error CSP)
  - [ ] Resultado del análisis IA aparece en pantalla
  - [ ] Sin errores de "Content Security Policy" para api.anthropic.com
```

### T-11.19: Flujo entrenamiento RISE — start → sets → finish
```
Navegar a: http://wellcore-laravel.test/v/rise/workout
click [Iniciar entrenamiento]
list_network_requests: POST /api/v/rise/workout/start → status 200

click [Completar set] en un ejercicio
list_network_requests: POST /api/v/rise/workout/complete-set → status 200

click [Finalizar entrenamiento]
list_network_requests: POST /api/v/rise/workout/finish → status 200
  - [ ] Redirige a /v/rise/workout-summary/:sessionId
```

### T-11.20: Registrar medición RISE
```
Navegar a: http://wellcore-laravel.test/v/rise/measurements
Completar formulario (peso, cintura, cadera, etc.)
click [Guardar medición]
list_network_requests: POST /api/v/rise/measurements → status 200 o 201
  - [ ] Medición aparece en historial
```

### T-11.21: Toggle hábito RISE
```
Navegar a: http://wellcore-laravel.test/v/rise/habits
click [toggle de un hábito RISE]
list_network_requests: POST /api/v/rise/habits/toggle → status 200
  - [ ] Hábito marcado visualmente
```

### T-11.22: Enviar mensaje en chat RISE
```
Navegar a: http://wellcore-laravel.test/v/rise/chat
fill [input de mensaje]: "Mensaje RISE de prueba"
click [Enviar]
list_network_requests: POST /api/v/rise/chat → status 200
  - [ ] Mensaje aparece en conversación
```

### T-11.23: Mover cliente en Kanban (coach)
```
Navegar a: http://wellcore-laravel.test/v/coach/kanban
Arrastrar un cliente de una columna a otra (o usar botón de mover)
list_network_requests: POST /api/v/coach/kanban/move → status 200
  - [ ] Cliente aparece en la nueva columna
  - [ ] Sin error 422
```

### T-11.24: Responder check-in de cliente (coach)
```
Navegar a: http://wellcore-laravel.test/v/coach/checkins
click [Ver / Responder] en un check-in
fill [respuesta del coach]: "Excelente progreso esta semana"
click [Enviar respuesta]
list_network_requests: POST /api/v/coach/checkins/:id/reply → status 200
  - [ ] Respuesta guardada
```

### T-11.25: Enviar mensaje a cliente (coach)
```
Navegar a: http://wellcore-laravel.test/v/coach/messages
Seleccionar un cliente
fill [mensaje]: "Hola, ¿cómo va tu semana?"
click [Enviar]
list_network_requests: POST /api/v/coach/messages → status 200
  - [ ] Mensaje aparece en la conversación
```

### T-11.26: Crear plan desde coach
```
Navegar a: http://wellcore-laravel.test/v/coach/plans
click [Crear nuevo plan]
Completar formulario básico del plan
click [Guardar]
list_network_requests: POST /api/v/coach/plans → status 200 o 201
  - [ ] Plan aparece en la lista
```

### T-11.27: Generar plan con IA (coach)
```
Navegar a: http://wellcore-laravel.test/v/coach/plans
click [Generar con IA] o encontrar la opción de generación automática
Completar parámetros del cliente (objetivo, nivel, días disponibles)
click [Generar]
list_network_requests: POST /api/v/coach/plans/generate → status 200
  - [ ] Plan generado aparece o se pre-llena el formulario
  - [ ] Sin error 500 ni timeout visible
```

### T-11.28: Crear nota del coach
```
Navegar a: http://wellcore-laravel.test/v/coach/notes
click [Nueva nota]
fill [título y contenido de la nota]
click [Guardar]
list_network_requests: POST /api/v/coach/notes → status 200 o 201
  - [ ] Nota aparece en la lista

click [Editar] en la nota recién creada
Modificar contenido
list_network_requests: PUT /api/v/coach/notes/:id → status 200

click [Eliminar] en la nota
list_network_requests: DELETE /api/v/coach/notes/:id → status 200
  - [ ] Nota desaparece de la lista
```

### T-11.29: Enviar broadcast a clientes (coach)
```
Navegar a: http://wellcore-laravel.test/v/coach/broadcast
fill [mensaje]: "Recordatorio: check-in esta semana"
Seleccionar destinatarios (todos los clientes o un grupo)
click [Enviar broadcast]
list_network_requests: POST /api/v/coach/broadcast → status 200
  - [ ] Confirmación de envío visible
```

### T-11.30: Actualizar cliente desde Admin
```
Navegar a: http://wellcore-laravel.test/v/admin/clients
click [un cliente] → /v/admin/clients/:id
Modificar un campo editable (ej: plan asignado, coach asignado)
click [Guardar / Actualizar]
list_network_requests: PUT /api/v/admin/clients/:id → status 200
  - [ ] Cambio persiste
```

### T-11.31: Crear invitación desde Admin
```
Navegar a: http://wellcore-laravel.test/v/admin/invitations
click [Crear invitación / Nueva invitación]
fill [email del invitado]: "nuevocliente@test.com"
Seleccionar plan
click [Crear]
list_network_requests: POST /api/v/admin/invitations → status 200 o 201
  - [ ] Invitación aparece en la lista con status "pendiente"
```

### T-11.32: AI Generator Admin — generar plan completo
```
Navegar a: http://wellcore-laravel.test/v/admin/ai-generator
Completar formulario del cliente (objetivo, nivel, disponibilidad, etc.)
click [Generar plan]
list_network_requests: POST /api/v/admin/ai-generator → status 200
  - [ ] Plan generado aparece en pantalla (puede tomar varios segundos)
  - [ ] Sin error 500 ni timeout visible
  - [ ] Respuesta de IA renderizada correctamente
```

### T-11.33: Actualizar configuración global (Admin)
```
Navegar a: http://wellcore-laravel.test/v/admin/settings
Modificar un valor de configuración
click [Guardar]
list_network_requests: PUT /api/v/admin/settings → status 200
  - [ ] Cambio persistido (verificar recargando la página)
```

---

## Instrucciones para el agente de testing

### Herramientas Chrome DevTools MCP disponibles
```
navigate_page(url)              — navegar a URL
take_screenshot()               — captura visual
take_snapshot()                 — DOM snapshot (estructura HTML)
list_console_messages()         — errores y warnings JS
list_network_requests()         — todas las llamadas HTTP
get_network_request(id)         — detalle completo de un request
evaluate_script(script)         — ejecutar JavaScript en la página
fill(selector, value)           — llenar campo de formulario
click(selector)                 — hacer click en elemento
wait_for(condition)             — esperar condición (ej: URL cambie)
list_pages()                    — listar tabs abiertos en Chrome
select_page(id)                 — seleccionar tab
new_page(url)                   — abrir nueva pestaña
```

### Flujo recomendado por test
```
1. navigate_page(url)
2. take_screenshot()             — captura estado inicial
3. list_console_messages()       — verificar sin errores JS
4. list_network_requests()       — verificar requests y status codes
5. [acción si aplica]
6. take_screenshot()             — captura estado post-acción
7. Reportar resultado
```

### Severidad de bugs
- 🔴 **CRÍTICO** — SPA no monta, login roto, error 500 en API, 401 en rutas autenticadas, datos que no cargan en páginas principales
- 🟠 **ALTO** — Página en blanco, formulario que no envía, error de consola bloqueante, chunk JS que no carga
- 🟡 **MEDIO** — Datos incorrectos, UI desalineada, error de consola no bloqueante, feature parcialmente rota
- 🟢 **BAJO** — Detalle visual, texto incorrecto, mejora UX menor

### Template de reporte de bug
```
## Bug: [Nombre descriptivo corto]
- Test ID: T-XX.X
- Severidad: 🔴 / 🟠 / 🟡 / 🟢
- URL: http://wellcore-laravel.test/v/...
- Síntoma: [Qué ves exactamente]
- Esperado: [Qué debería pasar]
- Errores consola: [Copiar texto de list_console_messages]
- Network: [Endpoint que falla + status code + response body si hay]
- Screenshot: [adjuntar take_screenshot()]
```

---

## Archivos clave para hacer fixes

```
resources/js/vue/
  app.js                        — Entry point: monta en #vue-app, registra Pinia + Router
  App.vue                       — Root component
  router/index.js               — 70 rutas (lazy loading para todas menos Auth)
  stores/auth.js                — Pinia store: token en wc_token, userType, userId
  composables/useApi.js         — Axios interceptor: agrega Bearer token, maneja 401
  layouts/
    PublicLayout.vue            — Formularios públicos y tienda
    ClientLayout.vue            — Dashboard del cliente
    RiseLayout.vue              — Portal RISE
    CoachLayout.vue             — Portal coach
    AdminLayout.vue             — Panel admin
  pages/Auth/                   — Login, ForgotPassword, ResetPassword
  pages/Public/                 — InscriptionForm, CoachApplication, RiseEnrollment, PresencialForm
  pages/Shop/                   — ProductCatalog, ProductDetail
  pages/Client/                 — 20 páginas (Dashboard → AINutrition)
  pages/Rise/                   — 10 páginas (Dashboard → WorkoutSummary)
  pages/Coach/                  — 13 páginas (Dashboard → Resources)
  pages/Admin/                  — 18 páginas (Dashboard → AdminTools)

app/Http/Controllers/Api/
  AuthController.php            — login, logout, forgotPassword, resetPassword, me
  PublicFormController.php      — inscription, coachApply, riseEnroll, presencial
  ShopController.php            — products index + show
  ClientController.php          — dashboard, metrics, profile, settings, notifications
  TrainingController.php        — plan, training, workout, checkin, workoutSummary
  SocialController.php          — community, challenges, chat, nutrition, habits, photos, records, aiNutrition
  RiseController.php            — 17 endpoints del programa RISE
  CoachController.php           — 24 endpoints del portal coach
  AdminController.php           — 19 endpoints del panel admin
  Concerns/AuthenticatesVueRequests.php  — trait Bearer token auth

routes/api.php                  — 113 endpoints totales bajo /api/v/*
routes/web.php                  — catch-all: Route::get('/v/{any}', ...) → vista vue
resources/views/vue.blade.php   — HTML shell: <div id="vue-app">, carga app.js con @vite
app/Http/Middleware/ContentSecurityPolicy.php  — CSP con Vite dev server en local env
vite.config.js                  — plugin Vue + alias @ + entry point vue/app.js
```

---

## Conteo de cobertura

| Bloque | Descripción | Tests |
|--------|-------------|-------|
| T-00 | Infraestructura Vue SPA | 5 |
| T-01 | Autenticación | 8 |
| T-02 | Formularios públicos | 5 |
| T-03 | Tienda / Shop | 2 |
| T-04 | Dashboard cliente | 5 |
| T-05 | Entrenamiento | 7 |
| T-06 | Social & Recursos | 10 |
| T-07 | Programa RISE | 10 |
| T-08 | Portal Coach | 13 |
| T-09 | Panel Admin | 18 |
| T-10 | Cross-cutting | 6 |
| T-11 | Flujos interactivos (POST/PUT/DELETE) | 33 |
| **Total** | **70 páginas Vue · 9 controllers · 113 endpoints** | **122 tests** |

---

*Plan generado: 2026-03-31 — WellCore Vue 3 Migration v2.0 (cobertura completa)*
