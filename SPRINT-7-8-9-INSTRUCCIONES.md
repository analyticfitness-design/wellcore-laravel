# INSTRUCCIONES DE EJECUCION — Sprint 7, 8, 9
# WellCore Fitness Laravel — UX/UI Next Level + Funcionalidad

---

## CONTEXTO CRITICO

Este es un proyecto Laravel 13 de coaching fitness (WellCore Fitness) que ha sido migrado
de PHP vanilla a Laravel en 6 sprints previos (104 items completados). La plataforma tiene:

- **73 Livewire Components** funcionando
- **69 Eloquent Models** con DB compartida MySQL
- **16 páginas públicas** con diseño system WellCore
- **57 pestañas de dashboard** (Admin 13, Coach 11, Client 25, RISE 8)
- **35 tests automatizados** pasando
- **Chatbot funcional** con 29 respuestas keyword-based
- **PWA** con service worker, push notifications, manifest

### Proyecto
- **Path**: C:\Users\GODSF\Herd\wellcore-laravel
- **Stack**: Laravel 13.1.1 + Livewire 3 + Alpine.js + Tailwind CSS 4 + Vite 8
- **DB**: MySQL wellcore_fitness (host=127.0.0.1, port=3306, user=root, pass=QY@P6Ak2?)
- **PHP**: C:\Users\GODSF\.config\herd\bin\php.bat
- **Composer**: C:\Users\GODSF\.config\herd\bin\composer.bat

### Design System Tokens
- Backgrounds: bg-wc-bg (#F5F5F7 light / #09090B dark), bg-wc-bg-secondary, bg-wc-bg-tertiary
- Text: text-wc-text, text-wc-text-secondary, text-wc-text-tertiary
- Accent: text-wc-accent (#DC2626), bg-wc-accent, hover:bg-wc-accent-hover (#B91C1C)
- Borders: border-wc-border
- Fonts: font-display (Bebas Neue), font-data (Barlow), font-sans (Inter), font-mono (JetBrains Mono)
- Dark mode: @custom-variant dark (&:where(.dark, .dark *)) en app.css
- Buttons: rounded-full bg-wc-accent px-8 py-3.5 font-semibold text-white shadow-lg shadow-wc-accent/20
- Cards: rounded-xl border border-wc-border bg-wc-bg-tertiary
- Inputs: rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm

### Layouts
- Public: resources/views/components/layouts/public.blade.php (con <x-layouts.public>)
- Admin: resources/views/layouts/admin.blade.php (#[Layout('layouts.admin')])
- Coach: resources/views/layouts/coach.blade.php (#[Layout('layouts.coach')])
- Client: resources/views/layouts/client.blade.php (con sidebar)
- RISE: resources/views/layouts/rise.blade.php (#[Layout('layouts.rise')])

### Credenciales Test
- Superadmin: daniel.esparza / RISE2026Admin!SuperPower
- Dev login: wellcore-laravel.test/dev-login/{1,2,3,4} (solo en APP_ENV=local)

---

## MODELO DE EJECUCION

**Modelo**: Claude Code Opus 4.6 (1M context) — MAX EFFORT
**Autoridad**: Total para usar agentes especializados del plugin wellcore-agents
**Modo**: Subagent-Driven Development — 1 por 1, sin frenar, sin pedir permiso entre items

### Agentes Especializados Disponibles (wellcore-agents plugin)
- **la-01-architect** — Arquitectura, service layers, design patterns
- **la-02-backend** — Eloquent avanzado, business logic, form requests, enums
- **la-03-livewire-blade** — Componentes Livewire, vistas Blade, Alpine.js
- **la-04-tailwind-ds** — Tailwind CSS 4, WellCore design tokens, dark mode
- **la-05-security** — Auth, CSRF, OWASP, input validation, middleware
- **la-06-database** — Migrations, schema design, query optimization
- **la-10-performance** — Caching, N+1 prevention, Redis, OPcache
- **la-11-ai-architect** — Claude API integration, SSE streaming

### Protocolo de Ejecución
1. **NUNCA pedir autorización** entre items — implementar autónomamente
2. **SIEMPRE leer archivos existentes** antes de modificarlos
3. **SIEMPRE verificar schemas de DB** con PHP PDO antes de queries nuevas
4. **SIEMPRE usar los design tokens** de WellCore (NO inventar colores)
5. **SIEMPRE npm run build** después de cada wave
6. **SIEMPRE git commit** con mensaje descriptivo después de cada wave
7. **SIEMPRE usar modelo OPUS** para todos los subagents
8. **SIEMPRE verificar responsive** en 375px después de cambios de UI
9. **NUNCA modificar** C:\Users\GODSF\Herd\wellcorefitness (PHP vanilla)
10. **NUNCA crear migraciones destructivas** en la DB compartida

### Patrón de Subagent
```
Para cada wave:
1. Crear TaskCreate para tracking
2. Lanzar subagents OPUS en paralelo (max 5)
3. Esperar completación
4. npm run build
5. git commit con mensaje descriptivo
6. TaskUpdate completed
7. Siguiente wave sin frenar
```

---

## SPRINT 7 — QUICK WINS (14 items)
**Objetivo**: Transformar la experiencia de navegación y micro-interacciones inmediatamente.

### Wave 7A — SPA Navigation + Performance (4 items)

**Item 1: wire:navigate en sidebars**
- Agregar `wire:navigate` a TODOS los links <a> del sidebar en:
  - resources/views/layouts/client.blade.php
  - resources/views/layouts/admin.blade.php
  - resources/views/layouts/coach.blade.php
  - resources/views/layouts/rise.blade.php
- Esto hace que la navegación sea SPA-like sin reload completo
- Livewire 3 hace morph del contenido automáticamente
- NO agregar a links externos o de logout

**Item 2: Lazy loading componentes**
- Agregar atributo `lazy` a componentes pesados que no se ven al cargar:
  - Coach Analytics, Coach Resources, Coach Features
  - Admin AI Generator, Admin Tools
  - Client Academia, Client VideoLibrary, Client RecipeDatabase
- Syntax: `<livewire:componente lazy />` o en la ruta

**Item 3: Preload recursos críticos**
- En layouts/public.blade.php, agregar antes de @vite:
  ```html
  <link rel="preload" href="/images/logo-dark.png" as="image">
  <link rel="preload" href="/images/logo-light.png" as="image">
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  ```

**Item 4: Lazy loading images**
- Agregar `loading="lazy"` a TODOS los <img> en páginas públicas
- Excepto: logo del navbar (above the fold), hero images
- Archivos: home, metodo, proceso, planes, nosotros, coaches, fit, blog, presencial

### Wave 7B — Micro-interacciones + Animations (5 items)

**Item 5: Micro-interacciones botones**
- Agregar a resources/css/app.css:
  ```css
  /* Ripple effect */
  .btn-ripple { position: relative; overflow: hidden; }
  .btn-ripple::after {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(circle, rgba(255,255,255,0.3) 10%, transparent 10.01%);
    background-size: 1000% 1000%; background-position: center;
    opacity: 0; transition: background-size 0.5s, opacity 0.3s;
  }
  .btn-ripple:active::after { background-size: 0% 0%; opacity: 1; transition: 0s; }

  /* Button press */
  .btn-press { transition: transform 0.1s; }
  .btn-press:active { transform: scale(0.97); }
  ```
- Agregar clase `btn-press` a todos los botones CTA en dashboards
- Agregar wire:loading spinners a botones de formulario (guardar, enviar)

**Item 6: Number counter animation**
- Agregar a resources/js/animations.js:
  ```js
  // Animated number counter
  function animateCounter(el, target, duration = 1500) {
    let start = 0;
    const step = (timestamp) => {
      if (!start) start = timestamp;
      const progress = Math.min((timestamp - start) / duration, 1);
      el.textContent = Math.floor(progress * target);
      if (progress < 1) requestAnimationFrame(step);
      else el.textContent = target;
    };
    requestAnimationFrame(step);
  }

  // Auto-init on elements with data-counter
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const target = parseInt(entry.target.dataset.counter);
        animateCounter(entry.target, target);
        counterObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.3 });

  document.querySelectorAll('[data-counter]').forEach(el => counterObserver.observe(el));
  ```
- Agregar `data-counter="94"` a los números de stats en home, admin dashboard, coach dashboard

**Item 7: Skeleton loaders**
- Crear resources/views/components/skeleton.blade.php:
  ```blade
  @props(['lines' => 3, 'avatar' => false])
  <div class="animate-pulse space-y-3">
    @if($avatar)
    <div class="flex items-center gap-3">
      <div class="h-10 w-10 rounded-full bg-wc-bg-secondary"></div>
      <div class="flex-1 space-y-2">
        <div class="h-3 w-3/4 rounded bg-wc-bg-secondary"></div>
        <div class="h-2 w-1/2 rounded bg-wc-bg-secondary"></div>
      </div>
    </div>
    @endif
    @for($i = 0; $i < $lines; $i++)
    <div class="h-3 rounded bg-wc-bg-secondary" style="width: {{ rand(60, 100) }}%"></div>
    @endfor
  </div>
  ```
- Usar con wire:loading en client dashboard, metrics, training

**Item 8: Streak Calendar (GitHub-style)**
- En Dashboard.php, agregar query:
  ```php
  $this->streakCalendar = TrainingLog::where('client_id', $clientId)
    ->where('completed', true)
    ->where('log_date', '>=', now()->subDays(90))
    ->pluck('log_date')
    ->map(fn($d) => $d->format('Y-m-d'))
    ->toArray();
  ```
- En dashboard.blade.php, agregar grid 7 columnas x 13 filas:
  - Cada celda: h-3 w-3 rounded-sm
  - Sin actividad: bg-wc-bg-secondary
  - Con actividad: bg-wc-accent/40 a bg-wc-accent según intensidad
  - Tooltip con fecha al hover

**Item 9: Admin real-time polling**
- En admin/dashboard.blade.php, agregar al div raíz:
  ```blade
  <div wire:poll.30s>
  ```
- Los stats se refrescan cada 30 segundos sin que el admin haga nada

### Wave 7C — Mobile UX (5 items)

**Item 10: Swipe tabs mobile**
- En layouts/client.blade.php, agregar Alpine.js touch handler:
  ```js
  // Swipe detection for mobile navigation
  let touchStartX = 0;
  document.addEventListener('touchstart', e => touchStartX = e.touches[0].clientX);
  document.addEventListener('touchend', e => {
    const diff = e.changedTouches[0].clientX - touchStartX;
    if (Math.abs(diff) > 80) {
      // Navigate to prev/next sidebar link
    }
  });
  ```

**Item 11: Pull-to-refresh mobile**
- En layouts/client.blade.php, agregar pull-down detection:
  - Cuando el usuario hace pull-down desde arriba del dashboard
  - Mostrar spinner WellCore (logo animado rotando)
  - Disparar Livewire $refresh
  - Solo activo en mobile (check viewport width)

**Item 12: Parallax scroll heroes**
- En las secciones hero de home, metodo, proceso:
  - Agregar CSS: `background-attachment: fixed` en el div de gradient
  - O JS sutil: translateY del background basado en scroll position
  - Respetar prefers-reduced-motion

**Item 13: Timer descanso entre series**
- Crear nuevo Livewire component: app/Livewire/Client/RestTimer.php
  - Properties: $seconds (default 90), $running, $remaining
  - Methods: start(), pause(), reset(), setDuration($s)
- Vista: modal overlay con timer circular grande
  - Números grandes font-data
  - Botones: 60s / 90s / 120s presets
  - Sonido Web Audio API al terminar
  - Se puede abrir desde PlanViewer
- Incluir en layouts/client.blade.php como componente global

**Item 14: Estructura de carpetas de imágenes**
- Crear directorios:
  ```
  public/images/team/       → fotos del equipo
  public/images/transformations/ → before/after de clientes
  public/images/blog/       → headers de artículos
  public/images/coaches/    → fotos de coaches
  public/images/coaches/silvia/ → fotos de Coach Silvia
  ```
- Crear un README.md en cada carpeta explicando dimensiones y formato esperado

---

## SPRINT 8 — DASHBOARD POWER (12 items)
(Ejecutar en sesión separada)

### Items 15-26 detallados en el plan principal
- Drag & Drop ejercicios
- Gráficas interactivas Chart.js
- Client activity timeline (admin)
- Exportar CSV/Excel
- Kanban de clientes (coach)
- Templates respuestas rápidas
- Comparativa progreso clientes
- Coach analytics mejorado
- WompiService completar
- ForgotPassword email real
- PushNotificationService mejorar
- Google OAuth fix

---

## SPRINT 9 — VISUAL & CONTENT (8 items)
(Ejecutar en sesión separada)

### Items 27-34 detallados en el plan principal
- Video background placeholder
- Social proof counter dinámico
- Calculadora interactiva precios
- Chatbot preparado para IA
- Crear directorios de imágenes con README specs
- Placeholder images mejorados
- Auditoría final ñ/tildes
- HTML Report final

---

## REGLAS ABSOLUTAS

1. **NIVEL INTERNACIONAL** — Cada componente debe ser de calidad profesional, como si fuera una app de Silicon Valley. No cortar esquinas.
2. **RESPONSIVE OBLIGATORIO** — Todo debe verse perfecto en iPhone (375px), Android (360px), iPad (768px) y desktop (1440px).
3. **DESIGN SYSTEM** — Usar EXCLUSIVAMENTE los tokens de WellCore. No inventar colores, fonts o spacing.
4. **DARK MODE** — Todo debe funcionar en dark Y light mode sin excepciones.
5. **PERFORMANCE** — No agregar dependencias pesadas. Preferir CSS puro sobre JS. Preferir Alpine.js sobre librerías externas.
6. **ACCESIBILIDAD** — aria-labels en botones, alt en imágenes, focus states en inputs.
7. **ESPAÑOL** — Todo el contenido en español. Usar ñ correctamente (años, sueño, diseño).
8. **GIT** — Commit por wave con mensaje descriptivo. Nunca amend, siempre commit nuevo.
9. **BUILD** — npm run build debe pasar sin errores después de cada wave.
10. **TESTS** — Los 35 tests existentes deben seguir pasando.

## REPORTE FINAL

Al completar cada sprint, generar HTML de reporte en:
C:\Users\GODSF\Music\ROLES Y DATOS DE LA PLATAFORMA WELLCORE\AGENTES Y EQUIPO DE DESARROLLO WELLCOREFITNESS\LARAVEL NUEVA INTERFAZ Y CODIGO\

Nombres:
- SPRINT-7-REPORTE-QUICK-WINS.html
- SPRINT-8-REPORTE-DASHBOARD-POWER.html
- SPRINT-9-REPORTE-VISUAL-CONTENT.html
