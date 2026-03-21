# Sprint 6 — UX Elite + Functional Improvements (28 items)

## Execution Plan — Subagent-Driven Development

### WAVE 1 — Functional + Content (9 items, 5 subagents en paralelo)

**Subagent 1: la-02-backend** — Functional Backend (3 items)
- Newsletter: crear tabla `newsletter_subscribers`, modelo, endpoint POST /api/newsletter
- Middleware roles: crear AdminMiddleware + CoachMiddleware, registrar en bootstrap/app.php
- Cache queries: cachear stats del admin dashboard (5 min TTL)

**Subagent 2: la-03-livewire-blade** — Shop Cart (1 item)
- ProductDetail: implementar addToCart con session-based cart
- Cart count en layout, cart dropdown

**Subagent 3: la-03-livewire-blade** — Notifications (1 item)
- NotificationBell: cargar WellcoreNotification reales del usuario
- Mark as read, count badge, polling cada 30s

**Subagent 4: la-04-tailwind-ds** — Visual Content (3 items)
- Blog headers: gradientes y patrones SVG unicos por articulo
- Testimonios: mejorar before/after cards con mejor diseño
- Dark mode audit: verificar todos los componentes client/coach/admin en dark

**Subagent 5: la-04-tailwind-ds** — Favicons + Offline (1 item)
- Favicon: generar de logo real (16x16, 32x32, apple-touch)
- Offline UI: pagina de "sin conexion" en sw.js

---

### WAVE 2 — UX Client Dashboard Core (10 items, 3 subagents en paralelo)

**Subagent 6: la-03-livewire-blade** — Dashboard Enhancements (5 items)
- Welcome Onboarding Modal: Alpine.js modal 3 slides, localStorage flag
- Weekly Summary Card: resumen semana anterior (entrenamientos, check-ins, peso)
- Coach Avatar: mostrar iniciales/nombre del coach asignado
- Motivational Quotes: array de 30 frases, una por dia
- Plan Progress Timeline: barra visual de semanas completadas

**Subagent 7: la-04-tailwind-ds** — Animations & Visual (3 items)
- Progress Ring: SVG circle animado que se llena con % semanal
- Streak Flame: CSS animation en icono de fuego cuando racha >= 3
- Achievement Toasts: confetti CSS keyframes al desbloquear logro

**Subagent 8: la-03-livewire-blade** — Interactive Elements (2 items)
- Quick Actions FAB: boton flotante con 4 acciones rapidas (Alpine.js expand)
- Countdown to check-in: timer con dias/horas hasta proximo check-in

---

### WAVE 3 — UX Advanced (9 items, 3 subagents en paralelo)

**Subagent 9: la-03-livewire-blade** — Advanced Features (3 items)
- Before/After Slider: input range que mueve clip-path para comparar fotos
- Smart Notifications: logica que detecta inactividad y crea WellcoreNotification
- Keyboard shortcuts: Alpine.js @keydown en layout client para C/T/M/P

**Subagent 10: la-04-tailwind-ds** — Visual UX (3 items)
- Nutricion donut chart: Chart.js doughnut para macros del dia
- Training completion: Web Audio API beep sutil (toggle en settings)
- Dark mode per-dashboard: toggle independiente en client layout

**Subagent 11: la-02-backend** — Testing & Quality (3 items)
- Tests: agregar 15+ tests para auth, chatbot, RISE, inscripcion
- Onboarding flow: tabla onboarding_steps, tracking de pasos completados
- Cleanup: audit final de ñ, tildes, textos placeholders

---

### WAVE 4 — Integration + Report

- Merge todos los cambios
- npm run build
- Responsive check (Playwright iPhone + iPad)
- Git commit por wave
- HTML report final en ruta especificada

## Agentes Especializados Asignados
- **la-02-backend**: middleware, newsletter, cache, tests, onboarding
- **la-03-livewire-blade**: dashboard components, FAB, countdown, slider, cart, notifications
- **la-04-tailwind-ds**: animations, visual improvements, dark mode, favicons
- **la-05-security**: middleware de roles (via la-02-backend)

## Estimado
- Wave 1: ~30 min (5 subagents paralelos)
- Wave 2: ~30 min (3 subagents paralelos)
- Wave 3: ~30 min (3 subagents paralelos)
- Wave 4: ~10 min (integration)
- Total: ~1.5-2 horas

## Report Path
C:\Users\GODSF\Music\ROLES Y DATOS DE LA PLATAFORMA WELLCORE\AGENTES Y EQUIPO DE DESARROLLO WELLCOREFITNESS\LARAVEL NUEVA INTERFAZ Y CODIGO\SPRINT-6-REPORTE-UX-ELITE.html
