# Coach Dashboard Redesign — Implementation Plan v1.0
# Generated: 2026-04-24 | Branch: feature/coach-dashboard-redesign

---

## 1. Executive Summary

### What is being redesigned
The WellCore Coach Dashboard at `/coach` receives a complete visual and UX overhaul replacing the current flat card layout with the Claude Design system: collapsible sidebar (4 sections), alert bar, hero-today header, KPI stat cards with SVG sparklines, timeline activity feed, tickets panel, and mobile FAB + bottom-sheet pattern.

### Why
The Claude Design HTML (`C:\Users\GODSF\Downloads\WellCore-Coach-Dashboard-CLAUDE-DESIGN.html`) has been reviewed and approved. The current implementation lacks urgency signaling (no alert bar), activity timeline, ticket visibility, SVG sparklines, and mobile UX patterns already present in the client dashboard.

### What is NOT touched
- Database schema — zero destructive migrations
- `auth_tokens` table, `WellCoreGuard`, or any vanilla PHP integration at `C:\Users\GODSF\Herd\wellcorefitness`
- Other Livewire components: `Coach\ClientList`, `Coach\CheckinReview`, `Coach\MessageCenter`, `Coach\Analytics`
- Routes — `/coach/*` route definitions unchanged
- Client, admin, or RISE layouts/views

### Scope boundary
| In scope | Out of scope |
|---|---|
| `layouts/coach.blade.php` — full rewrite | Any migration file |
| `Livewire/Coach/Dashboard.php` — extend | `Coach\ClientList.php` |
| `livewire/coach/dashboard.blade.php` — full rewrite | `routes/web.php` |
| `livewire/coach/partials/*.blade.php` — 9 new files | `resources/views/livewire/client/*` |
| `components/coach/*.blade.php` — 2 new components | `app/Auth/*` |
| `resources/css/app.css` — additive only | Any test database |
| `resources/js/coach-dashboard.js` — new file | |
| `lang/{es,en}/coach_dashboard.php` — new files | |

### Routes affected
Only visual rendering of `route('coach.dashboard')` changes. No route definitions are modified.

### DB changes
Zero. All queries use existing tables already confirmed in codebase:
`assigned_plans`, `clients`, `coach_messages`, `checkins`, `training_logs`, `tickets`

---

## 2. Analysis of Current State

### Current `Coach\Dashboard.php` structure
**File:** `C:\Users\GODSF\Herd\wellcore-laravel\app\Livewire\Coach\Dashboard.php`

**Public properties (10 total):**
`greeting`, `coachName`, `activeClients`, `pendingCheckins`, `unreadMessages`, `plansThisMonth`, `attentionClients[]`, `recentMessages[]`, `clientProgressData[]`, `checkinFrequencyData[]`

**Methods and what they do:**
- `mount()` — greeting, clientIds via AssignedPlan, 4 scalar queries, then calls 3 loaders. NO cache.
- `loadAttentionClients($clientIds)` — Checkin groupBy client_id WHERE coach_reply IS NULL. Enriches with Client + CoachMessage queries. Returns top 5.
- `loadRecentMessages($coachId)` — last 5 CoachMessages from clients, enriches with Client names.
- `loadChartData($clientIds)` — training sessions per client (horizontal bar) + check-in frequency per week (line chart). Both for Chart.js.

**Critical architectural gap:** No `Cache::remember()`. On every page load, 6+ queries execute. With 50 clients this is 400-800ms DB time. The Client Dashboard (`Livewire/Client/Dashboard.php`) solved this with `Cache::remember("dashboard:{$clientId}", 300, ...)`.

### Gaps vs new Claude Design

| New component | Data needed | Current status |
|---|---|---|
| Alert bar | urgentClientsCount (checkin >48h, no reply) | No: `attentionClients` exists but no 48h filter, and count not exposed |
| Hero today | Summary: urgentCount + pendingCheckins + unreadMessages | Data exists as separate props, no aggregation string |
| Stat card: Tickets | `tickets` table, `coach_id` + open status | Not loaded at all |
| Stat card: Sparklines | 7-day daily trend per metric | Not computed |
| Today activity timeline | Events last 24h: checkins + trainings + messages + tickets | Not loaded |
| Pending check-ins list | Checkins without reply + ISO week number | Partial: `attentionClients` is close but no week label |
| Tickets panel | Tickets ordered by priority | Not loaded |
| Collapsible sidebar | `sidebarCollapsed` Alpine state | Not implemented |
| FAB + bottom sheet | `fabOpen` Alpine state + slide-up | Not implemented |
| Mobile bottom nav | 4-tab structure with badge counts | Uses generic `x-mobile-bottom-nav` component |
| SVG sparklines | Pre-computed polyline points from 7-day aggregates | Not computed |
| Counter animation | Per-card x-init interval | Not implemented |

### New queries needed (N+1 safe)

All listed below use `whereIn` + `groupBy` patterns, never per-row queries:

1. **`loadUrgentClients`** — Checkin with `WHERE coach_reply IS NULL AND MAX(checkin_date) <= NOW()-48h`, then single `Client::whereIn` + single `CoachMessage::whereIn`
2. **`loadTodayActivity`** — Union of: Checkin (today), TrainingLog (today), CoachMessage (today), Ticket (today). Each uses `whereIn(client_id, $clientIds)` + `LIMIT 10`. One bulk `Client::whereIn` for names.
3. **`loadPendingCheckinsList`** — `Checkin::whereIn->whereNull('coach_reply')->orderBy->limit(8)` + `Client::whereIn`
4. **`loadOpenTickets`** — `Ticket::where('coach_id')->whereIn('status', ['open','pending'])->orderByRaw('FIELD(priority,...)')->limit(5)`
5. **`loadSparklines`** — 4 aggregate queries: `selectRaw('DATE(col) as d, COUNT(*) as cnt')->groupBy('d')` for last 7 days each

---

## 3. Target Architecture

### Files tree

```
app/Livewire/Coach/
└── Dashboard.php              [MODIFY — new properties + Cache::remember + 5 new loaders]

resources/views/livewire/coach/
├── dashboard.blade.php        [REWRITE — thin orchestrator using @include partials]
└── partials/
    ├── alert-bar.blade.php         [NEW — conditional on urgentClientsCount > 0]
    ├── hero-today.blade.php        [NEW — desktop H1 + mobile wc-hero-accent card]
    ├── kpi-cards.blade.php         [NEW — 4x x-coach.stat-card components]
    ├── urgent-clients.blade.php    [NEW — ATENCIÓN URGENTE section]
    ├── today-activity.blade.php    [NEW — timeline dots + connectors]
    ├── recent-messages.blade.php   [NEW — right column messages]
    ├── tickets.blade.php           [NEW — right column tickets]
    ├── pending-checkins.blade.php  [NEW — right column check-ins]
    └── charts-section.blade.php    [NEW — collapsible SVG charts]

resources/views/components/coach/
├── stat-card.blade.php           [NEW — KPI card: label/value/delta/accentColor/heroClass/spark/borderColor]
└── urgent-client-row.blade.php   [NEW — single urgent client row with swipe-item]

resources/views/layouts/
└── coach.blade.php               [REWRITE — collapsible sidebar + new mobile bottom nav + FAB]

resources/css/
└── app.css                       [ADDITIVE ONLY — slide-up, fade-in, pb-safe, no-scrollbar, nav-tap, swipe-item]

resources/js/
└── coach-dashboard.js            [NEW — Alpine stores + counter magic + swipe handlers]

lang/es/
└── coach_dashboard.php           [NEW — all visible strings]
lang/en/
└── coach_dashboard.php           [NEW — English equivalents]
```

### Component responsibilities

**`layouts/coach.blade.php`**
- FOUC dark-mode script (identical to current)
- Two Alpine stores: `darkMode` (existing, keep) + `coachSidebar.collapsed` (new, persisted to localStorage)
- Root `x-data="{ sidebarMobileOpen: false, fabOpen: false }"` on `<html>`
- Desktop sidebar: collapsible `w-60` ↔ `w-[4.5rem]` with `transition-all duration-300`
- Sidebar sections: Principal (Inicio, Clientes, Check-ins, Mensajes) / Trabajo (Tickets, Planes, Kanban, Broadcast) / Insights (Analítica, Notas) / Footer (Mi Marca, Perfil, Cerrar sesión, Colapsar)
- Sidebar badges: live count for Check-ins (`$pendingCheckins`) and Mensajes (`$unreadMessages`) — injected via layout `@php` with lightweight cached query
- Desktop top bar: breadcrumb Coach › Dashboard + bell notification + dark toggle + avatar
- Mobile top bar: COACH NAME (Oswald font-display) + date (JetBrains Mono) + bell + dark toggle
- Mobile bottom nav: 4 tabs (Inicio / Clientes / [FAB spacer] / Check-ins / Mensajes) with badge counts
- FAB button at `absolute left-1/2 -translate-x-1/2 -top-7` inside nav
- FAB bottom sheet: `x-show="fabOpen"` with `animate-slide-up`, 3 action rows
- Impersonation banner: preserved verbatim
- Main wrapper: `lg:ml-60` dynamic with `:class="$store.coachSidebar.collapsed ? 'lg:ml-[4.5rem]' : 'lg:ml-60'"`

**`Livewire/Coach/Dashboard.php`**
- All queries inside single `Cache::remember("coach_dashboard:{$coachId}", 300, ...)`
- Returns plain arrays (no Eloquent models) to avoid deserialization issues after deploys
- 7 loader methods: `loadUrgentClients`, `loadRecentMessages`, `loadTodayActivity`, `loadPendingCheckinsList`, `loadOpenTickets`, `loadSparklines`, (renamed) `loadClientProgressData`, `loadCheckinFrequencyData`
- New public properties: `openTickets`, `urgentClientsCount`, `todayActivity[]`, `pendingCheckinsList[]`, `openTicketsList[]`, `sparklines[]`, `todayDateLabel`

**`dashboard.blade.php`**
- Root `<div>` with `class="space-y-0"` (no gap — partials manage their own spacing)
- Desktop section: `<div class="hidden lg:block px-6 pt-6">` contains alert-bar + hero-today
- Mobile section: `<div class="lg:hidden">` contains hero-today mobile variant
- Mobile quick-actions: horizontal scroll chips strip (mobile only)
- Stats grid: always visible
- Main content grid: `lg:grid lg:grid-cols-12 lg:gap-5` — left col 8, right col 4
- Uses `@include('livewire.coach.partials.X')` for all partials

**`components/coach/stat-card.blade.php`**
Props: `$label`, `$value` (int), `$delta` (string), `$accentColor` (hex string), `$heroClass` (CSS class), `$borderColor` (CSS color string), `$spark` (SVG polyline points), `$delay` (animation delay ms)
- Full-width hero gradient card
- Inline SVG sparkline at top-right (60x24 viewBox, 7 data points)
- Counter animation via `x-init` interval
- Accessible: `aria-hidden` on sparkline SVG, stat value has no `aria-label` needed (label below)

---

## 4. Implementation Phases

### Phase 0 — Setup (30 min)

```bash
git checkout -b feature/coach-dashboard-redesign
mkdir -p resources/views/livewire/coach/partials
mkdir -p resources/views/components/coach
```

- [ ] Create `lang/es/coach_dashboard.php` with all keys listed in Section 2
- [ ] Create `lang/en/coach_dashboard.php` with English equivalents
- [ ] Verify `@keyframes slide-up` exists in `resources/css/app.css` → add if missing
- [ ] Verify `.animate-slide-up`, `.animate-fade-in`, `.pb-safe`, `.no-scrollbar`, `.nav-tap`, `.swipe-item` exist → add if missing
- [ ] Create `resources/js/coach-dashboard.js` (Alpine magic + swipe handlers)
- [ ] Add import or Vite entry for `coach-dashboard.js`

**`lang/es/coach_dashboard.php`:**
```php
<?php
return [
    'alert_urgent'       => ':count clientes necesitan atención urgente hoy',
    'alert_view_all'     => 'Ver todos →',
    'hoy'                => 'HOY',
    'hero_subtitle'      => ':urgent en riesgo · :checkins check-ins pendientes · :messages mensajes sin leer',
    'stat_clients'       => 'CLIENTES ACTIVOS',
    'stat_checkins'      => 'CHECK-INS HOY',
    'stat_messages'      => 'MENSAJES',
    'stat_tickets'       => 'TICKETS ABIERTOS',
    'section_urgent'     => 'ATENCIÓN URGENTE',
    'section_activity'   => 'ACTIVIDAD HOY',
    'section_analysis'   => 'ANÁLISIS DE LA SEMANA',
    'section_messages'   => 'MENSAJES',
    'section_tickets'    => 'TICKETS',
    'section_checkins'   => 'CHECK-INS',
    'btn_respond'        => 'Responder →',
    'btn_review'         => 'Revisar',
    'btn_view_checkins'  => 'Ver check-ins',
    'btn_send_message'   => 'Enviar mensaje',
    'btn_new_client'     => '+ Nuevo cliente',
    'btn_view_all'       => 'Ver todos →',
    'fab_add_client'     => 'Agregar cliente',
    'fab_add_client_sub' => 'Invitar nuevo cliente al programa',
    'fab_broadcast'      => 'Enviar broadcast',
    'fab_broadcast_sub'  => 'Mensaje a todos los clientes',
    'fab_checkins'       => 'Revisar check-ins',
    'fab_checkins_sub'   => ':count check-ins esperan tu respuesta',
    'sidebar_principal'  => 'Principal',
    'sidebar_trabajo'    => 'Trabajo',
    'sidebar_insights'   => 'Insights',
    'sidebar_inicio'     => 'Inicio',
    'sidebar_clientes'   => 'Clientes',
    'sidebar_checkins'   => 'Check-ins',
    'sidebar_mensajes'   => 'Mensajes',
    'sidebar_tickets'    => 'Tickets',
    'sidebar_planes'     => 'Planes',
    'sidebar_kanban'     => 'Kanban',
    'sidebar_broadcast'  => 'Broadcast',
    'sidebar_analitica'  => 'Analítica',
    'sidebar_notas'      => 'Notas',
    'sidebar_marca'      => 'Mi Marca',
    'sidebar_perfil'     => 'Perfil',
    'sidebar_colapsar'   => 'Colapsar',
    'empty_urgent'       => 'Todos los check-ins respondidos',
    'empty_urgent_sub'   => 'Buen trabajo',
    'empty_activity'     => 'Sin actividad en las últimas 24 horas',
    'empty_messages'     => 'Sin mensajes recientes',
    'empty_tickets'      => 'Sin tickets abiertos',
    'empty_checkins'     => 'Sin check-ins pendientes',
    'tag_sin_responder'  => 'SIN RESPONDER',
    'tag_sin_checkin'    => 'SIN CHECK-IN',
    'tag_pago_vencido'   => 'PAGO VENCIDO',
    'activity_checkin'   => ':name envió su check-in semanal',
    'activity_training'  => ':name registró entrenamiento',
    'activity_message'   => 'Nuevo mensaje de :name',
    'activity_ticket'    => 'Ticket abierto por :name',
    'unread_label'       => ':count sin leer',
    'open_label'         => ':count abiertos',
    'review_label'       => ':count por revisar',
    'sem_label'          => 'Sem. :num',
    'ver_todos_messages' => 'Ver todos los mensajes →',
    'ver_todos_tickets'  => 'Ver todos los tickets →',
    'charts_ver'         => 'Ver análisis →',
    'charts_colapsar'    => 'Colapsar',
    'chart_clientes_label' => 'Clientes activos · 7 días',
    'chart_checkins_label' => 'Check-ins · 7 días',
    'day_mon' => 'Lun', 'day_tue' => 'Mar', 'day_wed' => 'Mié',
    'day_thu' => 'Jue', 'day_fri' => 'Vie', 'day_sat' => 'Sáb', 'day_sun' => 'Dom',
];
```

**Additive CSS to verify/add in `app.css`:**
```css
/* Add ONLY if not already present: */
@keyframes slide-up { from { transform:translateY(100%); } to { transform:translateY(0); } }
@keyframes fade-in  { from { opacity:0; } to { opacity:1; } }
.animate-slide-up   { animation: slide-up 0.32s cubic-bezier(0.32,0.72,0,1) both; }
.animate-fade-in    { animation: fade-in 0.2s ease both; }
.pb-safe            { padding-bottom: max(1rem, env(safe-area-inset-bottom)); }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar       { -ms-overflow-style: none; scrollbar-width: none; }
.nav-tap:active     { transform: scale(0.88); transition: transform 0.1s; }
.swipe-item         { transition: transform 0.25s ease; touch-action: pan-y; }
@media(min-width:1024px) {
  .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
  .stat-card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,0.18); }
}
```

### Phase 1 — Layout rewrite: `layouts/coach.blade.php` (1-2 hrs)

**Key structural changes from current:**

1. Add `coachSidebar` Alpine store to the inline script block (before `darkMode`)
2. Change `<html>` x-data to: `x-data="{ sidebarMobileOpen: false, fabOpen: false }"`
3. Sidebar `<aside>`: change from `w-60` fixed to `:class="$store.coachSidebar?.collapsed ? 'w-[4.5rem]' : 'w-60'"` + `class="... transition-all duration-300"`
4. Add sidebar section grouping: Principal / Trabajo / Insights / (footer items)
5. Add live badge spans on Check-ins and Mensajes nav items (use `@php` block to cache-query counts)
6. Add collapse toggle button in sidebar footer
7. Update main wrapper: `:class="$store.coachSidebar?.collapsed ? 'lg:ml-[4.5rem]' : 'lg:ml-60'"`
8. Desktop top bar: add breadcrumb + bell notification button
9. Mobile top bar: add coach name in Oswald + date in JetBrains Mono format
10. Replace `<x-mobile-bottom-nav variant="coach" />` with inline bottom nav
11. Add FAB button at center of bottom nav
12. Add FAB bottom-sheet (`x-show="fabOpen"`) with 3 actions + `animate-slide-up` + backdrop

**Alpine script block (complete, replace existing):**
```javascript
// FOUC: apply dark before Alpine loads
if (localStorage.getItem('darkMode') === 'true') document.documentElement.classList.add('dark');
document.addEventListener('alpine:init', () => {
    Alpine.store('darkMode', {
        on: localStorage.getItem('darkMode') === 'true',
        toggle() {
            this.on = !this.on;
            localStorage.setItem('darkMode', String(this.on));
            document.documentElement.classList.toggle('dark', this.on);
        }
    });
    Alpine.store('coachSidebar', {
        collapsed: localStorage.getItem('coachSidebarCollapsed') === 'true',
        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('coachSidebarCollapsed', String(this.collapsed));
        }
    });
});
document.addEventListener('livewire:navigated', () => {
    document.documentElement.classList.toggle('dark', localStorage.getItem('darkMode') === 'true');
});
```

**Sidebar nav structure (4 sections + footer):**
```
Principal:
  - Inicio         → route('coach.dashboard')
  - Clientes       → route('coach.clients')    [badge: active client count]
  - Check-ins      → route('coach.checkins')   [badge: $pendingCheckins, red]
  - Mensajes       → route('coach.messages')   [badge: $unreadMessages, red + breathe]

Trabajo:
  - Tickets        → route('coach.checkins')   [no dedicated ticket route yet]
  - Planes         → route('coach.plans')
  - Kanban         → route('coach.kanban')
  - Broadcast      → route('coach.broadcast')

Insights:
  - Analítica      → route('coach.analytics')
  - Notas          → route('coach.notes')

Footer (always visible):
  - Mi Marca       → route('coach.brand')
  - Perfil         → route('coach.profile')
  - Cerrar sesión  → POST route('logout')
  - [Collapse toggle button]
```

**Bottom nav HTML (inline replacement for x-mobile-bottom-nav):**
```blade
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 border-t pb-safe"
     style="background:var(--color-wc-bg-secondary); border-color:var(--color-wc-border)"
     aria-label="Navegación principal">
  <div class="flex items-center justify-around h-16 px-2 relative">
    {{-- Inicio --}}
    <a wire:navigate href="{{ route('coach.dashboard') }}"
       class="nav-tap flex flex-col items-center gap-0.5 py-2 px-3 transition-colors {{ request()->routeIs('coach.dashboard') ? 'text-wc-accent' : 'text-wc-text-tertiary' }}"
       aria-label="{{ __('coach_dashboard.sidebar_inicio') }}">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
        <polyline points="9 22 9 12 15 12 15 22"></polyline>
      </svg>
      <span class="text-[9px] font-semibold">{{ __('coach_dashboard.sidebar_inicio') }}</span>
    </a>
    {{-- Clientes --}}
    <a wire:navigate href="{{ route('coach.clients') }}"
       class="nav-tap flex flex-col items-center gap-0.5 py-2 px-3 transition-colors {{ request()->routeIs('coach.clients*') ? 'text-wc-accent' : 'text-wc-text-tertiary' }}"
       aria-label="{{ __('coach_dashboard.sidebar_clientes') }}">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path><circle cx="9" cy="7" r="4"></circle>
        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"></path>
      </svg>
      <span class="text-[9px] font-semibold">{{ __('coach_dashboard.sidebar_clientes') }}</span>
    </a>
    {{-- FAB spacer --}}
    <div class="w-14" aria-hidden="true"></div>
    {{-- Check-ins --}}
    <a wire:navigate href="{{ route('coach.checkins') }}"
       class="nav-tap relative flex flex-col items-center gap-0.5 py-2 px-3 transition-colors {{ request()->routeIs('coach.checkins') ? 'text-wc-accent' : 'text-wc-text-tertiary' }}"
       aria-label="{{ __('coach_dashboard.sidebar_checkins') }}">
      <div class="relative">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
          <rect x="9" y="11" width="13" height="13" rx="2"></rect>
          <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"></path>
        </svg>
        @if(isset($pendingCheckins) && $pendingCheckins > 0)
          <span class="absolute -top-1 -right-2 w-4 h-4 rounded-full bg-wc-accent flex items-center justify-center text-[8px] font-bold text-white" aria-label="{{ $pendingCheckins }} pendientes">{{ $pendingCheckins }}</span>
        @endif
      </div>
      <span class="text-[9px] font-semibold">Check-ins</span>
    </a>
    {{-- Mensajes --}}
    <a wire:navigate href="{{ route('coach.messages') }}"
       class="nav-tap relative flex flex-col items-center gap-0.5 py-2 px-3 transition-colors {{ request()->routeIs('coach.messages') ? 'text-wc-accent' : 'text-wc-text-tertiary' }}"
       aria-label="{{ __('coach_dashboard.sidebar_mensajes') }}">
      <div class="relative">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
          <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"></path>
        </svg>
        @if(isset($unreadMessages) && $unreadMessages > 0)
          <span class="absolute -top-1 -right-2 w-4 h-4 rounded-full bg-wc-accent flex items-center justify-center text-[8px] font-bold text-white animate-wc-breathe" aria-label="{{ $unreadMessages }} sin leer">{{ $unreadMessages }}</span>
        @endif
      </div>
      <span class="text-[9px] font-semibold">Mensajes</span>
    </a>
  </div>
  {{-- Centered FAB button --}}
  <div class="absolute left-1/2 -translate-x-1/2 -top-7">
    <button x-on:click="fabOpen=!fabOpen"
            class="w-14 h-14 rounded-full bg-wc-accent flex items-center justify-center text-white transition-all duration-200 active:scale-90"
            style="box-shadow:0 4px 20px rgba(220,38,38,0.45)"
            aria-label="Acciones rápidas"
            :aria-expanded="fabOpen">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
           :class="fabOpen ? 'rotate-45' : ''" class="transition-transform duration-200" aria-hidden="true">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
      </svg>
    </button>
  </div>
</nav>
{{-- FAB backdrop --}}
<div x-show="fabOpen"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-on:click="fabOpen=false"
     class="lg:hidden fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"
     x-cloak aria-hidden="true"></div>
{{-- FAB bottom sheet --}}
<div x-show="fabOpen"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="transform translate-y-full opacity-0"
     x-transition:enter-end="transform translate-y-0 opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="transform translate-y-0 opacity-100"
     x-transition:leave-end="transform translate-y-full opacity-0"
     class="lg:hidden fixed bottom-0 inset-x-0 z-50 rounded-t-2xl border-t p-6 pb-safe space-y-3"
     style="background:var(--color-wc-bg-secondary); border-color:var(--color-wc-border)"
     role="dialog" aria-modal="true" aria-label="Acciones rápidas"
     x-cloak>
  <div class="w-10 h-1 rounded-full mx-auto mb-4" style="background:var(--color-wc-border)" aria-hidden="true"></div>
  <a wire:navigate href="{{ route('coach.clients') }}" x-on:click="fabOpen=false"
     class="flex items-center gap-4 w-full px-4 py-3.5 rounded-xl hover:bg-wc-bg-tertiary transition-colors"
     style="border:1px solid var(--color-wc-border)">
    <div class="w-10 h-10 rounded-xl bg-wc-accent/15 flex items-center justify-center shrink-0" aria-hidden="true">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2">
        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path><circle cx="9" cy="7" r="4"></circle>
        <line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line>
      </svg>
    </div>
    <div class="text-left min-w-0">
      <p class="text-sm font-semibold text-wc-text">{{ __('coach_dashboard.fab_add_client') }}</p>
      <p class="text-xs text-wc-text-secondary truncate">{{ __('coach_dashboard.fab_add_client_sub') }}</p>
    </div>
  </a>
  <a wire:navigate href="{{ route('coach.broadcast') }}" x-on:click="fabOpen=false"
     class="flex items-center gap-4 w-full px-4 py-3.5 rounded-xl hover:bg-wc-bg-tertiary transition-colors"
     style="border:1px solid var(--color-wc-border)">
    <div class="w-10 h-10 rounded-xl bg-wc-info/15 flex items-center justify-center shrink-0" aria-hidden="true">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2">
        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"></path>
      </svg>
    </div>
    <div class="text-left min-w-0">
      <p class="text-sm font-semibold text-wc-text">{{ __('coach_dashboard.fab_broadcast') }}</p>
      <p class="text-xs text-wc-text-secondary truncate">{{ __('coach_dashboard.fab_broadcast_sub') }}</p>
    </div>
  </a>
  <a wire:navigate href="{{ route('coach.checkins') }}" x-on:click="fabOpen=false"
     class="flex items-center gap-4 w-full px-4 py-3.5 rounded-xl hover:bg-wc-bg-tertiary transition-colors"
     style="border:1px solid var(--color-wc-border)">
    <div class="w-10 h-10 rounded-xl bg-wc-success/15 flex items-center justify-center shrink-0" aria-hidden="true">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2">
        <rect x="9" y="11" width="13" height="13" rx="2"></rect>
        <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"></path>
      </svg>
    </div>
    <div class="text-left min-w-0">
      <p class="text-sm font-semibold text-wc-text">{{ __('coach_dashboard.fab_checkins') }}</p>
      @isset($pendingCheckins)
        <p class="text-xs text-wc-text-secondary truncate">{{ __('coach_dashboard.fab_checkins_sub', ['count' => $pendingCheckins]) }}</p>
      @endisset
    </div>
  </a>
</div>
```

**Note on badge counts in layout:** The layout cannot directly access Livewire component properties. Use `@php` block after the `$coach` query to run two lightweight cached queries:
```php
@php
    $coach = auth('wellcore')->user();
    $coachId = $coach?->id;
    [$layoutPendingCheckins, $layoutUnreadMessages] = cache()->remember("coach_layout_counts:{$coachId}", 120, function() use ($coachId) {
        $clientIds = \App\Models\AssignedPlan::where('assigned_by', $coachId)->pluck('client_id')->unique();
        return [
            \App\Models\Checkin::whereIn('client_id', $clientIds)->whereNull('coach_reply')->count(),
            \App\Models\CoachMessage::where('coach_id', $coachId)->where('direction','client_to_coach')->whereNull('read_at')->count(),
        ];
    });
@endphp
```
Then use `$layoutPendingCheckins` and `$layoutUnreadMessages` in the bottom nav badges.

### Phase 2 — Data layer (1-2 hrs)

**Complete rewrite of `app/Livewire/Coach/Dashboard.php`:**

Properties (new + preserved):
```php
// Identity
public string $greeting = '';
public string $coachName = '';
public string $todayDateLabel = '';

// KPI scalars
public int $activeClients = 0;
public int $pendingCheckins = 0;
public int $unreadMessages = 0;
public int $plansThisMonth = 0;
public int $openTickets = 0;
public int $urgentClientsCount = 0;

// List data
public array $attentionClients = [];    // urgent clients (>48h no reply)
public array $recentMessages = [];
public array $todayActivity = [];
public array $pendingCheckinsList = [];
public array $openTicketsList = [];

// Chart data
public array $sparklines = [];          // ['clients'=>'...', 'checkins'=>'...', ...]
public array $clientProgressData = [];
public array $checkinFrequencyData = [];
```

Complete `mount()` with cache:
```php
public function mount(): void
{
    $coach   = auth('wellcore')->user();
    $coachId = $coach->id;

    $hour = (int) now()->format('H');
    $this->greeting = match(true) {
        $hour < 12 => 'Buenos días',
        $hour < 18 => 'Buenas tardes',
        default    => 'Buenas noches',
    };
    $this->coachName      = explode(' ', $coach->name ?? 'Coach')[0];
    $this->todayDateLabel = now()->translatedFormat('l · j M Y');

    $cached = Cache::remember("coach_dashboard:{$coachId}", 300, function () use ($coachId) {
        $clientIds = AssignedPlan::where('assigned_by', $coachId)
            ->pluck('client_id')
            ->unique();

        $activeClients   = Client::whereIn('id', $clientIds)->where('status', 'activo')->count();
        $pendingCheckins = Checkin::whereIn('client_id', $clientIds)->whereNull('coach_reply')->count();
        $unreadMessages  = CoachMessage::where('coach_id', $coachId)
            ->where('direction', 'client_to_coach')
            ->whereNull('read_at')
            ->count();
        $plansThisMonth  = AssignedPlan::where('assigned_by', $coachId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $openTickets     = Ticket::where('coach_id', $coachId)
            ->whereIn('status', ['open', 'pending'])
            ->count();

        $attentionClients = $this->loadUrgentClients($clientIds);

        return [
            'activeClients'       => $activeClients,
            'pendingCheckins'     => $pendingCheckins,
            'unreadMessages'      => $unreadMessages,
            'plansThisMonth'      => $plansThisMonth,
            'openTickets'         => $openTickets,
            'urgentClientsCount'  => count($attentionClients),
            'attentionClients'    => $attentionClients,
            'recentMessages'      => $this->loadRecentMessages($coachId),
            'todayActivity'       => $this->loadTodayActivity($clientIds, $coachId),
            'pendingCheckinsList' => $this->loadPendingCheckinsList($clientIds),
            'openTicketsList'     => $this->loadOpenTickets($coachId),
            'sparklines'          => $this->loadSparklines($clientIds, $coachId),
            'clientProgressData'  => $this->loadClientProgressData($clientIds),
            'checkinFrequencyData'=> $this->loadCheckinFrequencyData($clientIds),
        ];
    });

    foreach ($cached as $key => $value) {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        }
    }
}
```

**`loadUrgentClients` method:**
```php
protected function loadUrgentClients(Collection $clientIds): array
{
    $threshold = now()->subHours(48)->toDateString();

    $pending = Checkin::whereIn('client_id', $clientIds)
        ->whereNull('coach_reply')
        ->selectRaw('client_id, COUNT(*) as pending_count, MAX(checkin_date) as latest_checkin')
        ->groupBy('client_id')
        ->having('latest_checkin', '<=', $threshold)
        ->orderBy('latest_checkin')
        ->limit(5)
        ->get();

    if ($pending->isEmpty()) return [];

    $pIds     = $pending->pluck('client_id');
    $clients  = Client::whereIn('id', $pIds)->get()->keyBy('id');

    return $pending->map(function ($row) use ($clients) {
        $client = $clients->get($row->client_id);
        if (!$client) return null;
        $name   = $client->name ?? 'Cliente';
        $parts  = explode(' ', $name);
        $initials = strtoupper(
            substr($parts[0] ?? '', 0, 1) .
            substr($parts[1] ?? '', 0, 1)
        );
        $days = (int) Carbon::parse($row->latest_checkin)->diffInDays(now());
        $isMissingCheckin = $days > 14;

        return [
            'id'              => $client->id,
            'name'            => $name,
            'initials'        => $initials,
            'tag'             => $isMissingCheckin ? 'SIN CHECK-IN' : 'SIN RESPONDER',
            'tag_class'       => $isMissingCheckin ? 'bg-amber-500/20 text-amber-400' : 'bg-red-500/20 text-red-400',
            'reason'          => "{$row->pending_count} check-in(s) pendiente(s) · hace {$days} días",
            'last_seen'       => 'Última actividad: ' . Carbon::parse($row->latest_checkin)->translatedFormat('D j M'),
            'oldest_checkin'  => Carbon::parse($row->latest_checkin)->diffForHumans(),
            'pending_checkins'=> $row->pending_count,
            'avatar_a'        => $isMissingCheckin ? '#fbbf24' : '#f87171',
            'avatar_b'        => $isMissingCheckin ? '#b45309' : '#DC2626',
        ];
    })->filter()->values()->toArray();
}
```

**`loadTodayActivity` method:**
```php
protected function loadTodayActivity(Collection $clientIds, int $coachId): array
{
    $since = now()->subHours(24)->toDateTimeString();
    $activity = collect();

    // Check-ins submitted in last 24h
    $checkins = Checkin::whereIn('client_id', $clientIds)
        ->where('created_at', '>=', $since)
        ->orderByDesc('created_at')
        ->limit(8)
        ->get();
    $ciClientIds = $checkins->pluck('client_id')->unique();
    $ciClients   = Client::whereIn('id', $ciClientIds)->pluck('name', 'id');
    foreach ($checkins as $ci) {
        $activity->push([
            'type'  => 'checkin',
            'color' => '#10B981',
            'text'  => ($ciClients->get($ci->client_id) ?? 'Cliente') . ' envió su check-in semanal',
            'sub'   => null,
            'time'  => Carbon::parse($ci->created_at)->format('H:i'),
            'ts'    => $ci->created_at->timestamp,
        ]);
    }

    // Training completed today
    $trainings = TrainingLog::whereIn('client_id', $clientIds)
        ->where('log_date', now()->toDateString())
        ->where('completed', true)
        ->orderByDesc('created_at')
        ->limit(6)
        ->get();
    $trClientIds = $trainings->pluck('client_id')->unique();
    $trClients   = Client::whereIn('id', $trClientIds)->pluck('name', 'id');
    foreach ($trainings as $t) {
        $activity->push([
            'type'  => 'training',
            'color' => '#3B82F6',
            'text'  => ($trClients->get($t->client_id) ?? 'Cliente') . ' registró entrenamiento',
            'sub'   => null,
            'time'  => Carbon::parse($t->created_at)->format('H:i'),
            'ts'    => $t->created_at->timestamp,
        ]);
    }

    // Messages today
    $messages = CoachMessage::where('coach_id', $coachId)
        ->where('direction', 'client_to_coach')
        ->where('created_at', '>=', $since)
        ->orderByDesc('created_at')
        ->limit(5)
        ->get();
    $msgClientIds = $messages->pluck('client_id')->unique();
    $msgClients   = Client::whereIn('id', $msgClientIds)->pluck('name', 'id');
    foreach ($messages as $m) {
        $activity->push([
            'type'  => 'message',
            'color' => '#F59E0B',
            'text'  => 'Nuevo mensaje de ' . ($msgClients->get($m->client_id) ?? 'Cliente'),
            'sub'   => null,
            'time'  => Carbon::parse($m->created_at)->format('H:i'),
            'ts'    => $m->created_at->timestamp,
        ]);
    }

    // Tickets opened today
    $tickets = Ticket::where('coach_id', $coachId)
        ->where('created_at', '>=', $since)
        ->orderByDesc('created_at')
        ->limit(4)
        ->get();
    foreach ($tickets as $t) {
        $activity->push([
            'type'  => 'ticket',
            'color' => '#DC2626',
            'text'  => 'Ticket abierto por ' . ($t->client_name ?? 'Cliente'),
            'sub'   => ($t->priority?->label() ?? '') . ' · Sin asignar',
            'time'  => Carbon::parse($t->created_at)->format('H:i'),
            'ts'    => $t->created_at->timestamp,
        ]);
    }

    return $activity
        ->sortByDesc('ts')
        ->take(8)
        ->map(fn ($e) => collect($e)->except('ts')->toArray())
        ->values()
        ->toArray();
}
```

**`loadPendingCheckinsList` method:**
```php
protected function loadPendingCheckinsList(Collection $clientIds): array
{
    $pending = Checkin::whereIn('client_id', $clientIds)
        ->whereNull('coach_reply')
        ->orderBy('checkin_date')
        ->limit(8)
        ->get();

    if ($pending->isEmpty()) return [];

    $pClientIds = $pending->pluck('client_id')->unique();
    $clients    = Client::whereIn('id', $pClientIds)->pluck('name', 'id');

    return $pending->map(function ($ci) use ($clients) {
        $name     = $clients->get($ci->client_id) ?? 'Cliente';
        $parts    = explode(' ', $name);
        $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
        $weekNum  = Carbon::parse($ci->checkin_date)->isoWeek();
        $sentAgo  = Carbon::parse($ci->created_at)->diffForHumans();

        return [
            'id'        => $ci->id,
            'client_id' => $ci->client_id,
            'name'      => $name,
            'initials'  => $initials,
            'week'      => "Semana {$weekNum} · enviado {$sentAgo}",
            'avatar_a'  => '#' . substr(md5($name), 0, 6),
            'avatar_b'  => '#' . substr(md5(strrev($name)), 0, 6),
        ];
    })->toArray();
}
```

**`loadOpenTickets` method:**
```php
protected function loadOpenTickets(int $coachId): array
{
    $tickets = Ticket::where('coach_id', $coachId)
        ->whereIn('status', ['open', 'pending'])
        ->orderByRaw("FIELD(priority, 'urgent','high','normal','low')")
        ->limit(5)
        ->get();

    return $tickets->map(function ($t) {
        $priorityVal = $t->priority?->value ?? 'normal';
        return [
            'id'           => $t->id,
            'subject'      => str()->limit($t->description ?? 'Sin descripción', 60),
            'from'         => $t->client_name ?? 'Cliente',
            'priority'     => $t->priority?->label() ?? 'Normal',
            'priority_raw' => $priorityVal,
            'color'        => match($priorityVal) {
                'urgent', 'high' => '#DC2626',
                'normal'         => '#F59E0B',
                default          => '#10B981',
            },
            'badge_class'  => match($priorityVal) {
                'urgent', 'high' => 'bg-red-500/20 text-red-400',
                'normal'         => 'bg-amber-500/20 text-amber-400',
                default          => 'bg-emerald-500/20 text-emerald-400',
            },
        ];
    })->toArray();
}
```

**`loadSparklines` method:**
```php
protected function loadSparklines(Collection $clientIds, int $coachId): array
{
    $days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->toDateString());
    $firstDay = $days->first();

    $trainingByDay = TrainingLog::whereIn('client_id', $clientIds)
        ->where('completed', true)
        ->where('log_date', '>=', $firstDay)
        ->selectRaw('log_date as d, COUNT(*) as cnt')
        ->groupBy('log_date')
        ->pluck('cnt', 'd');

    $checkinsByDay = Checkin::whereIn('client_id', $clientIds)
        ->where('created_at', '>=', $firstDay . ' 00:00:00')
        ->selectRaw('DATE(created_at) as d, COUNT(*) as cnt')
        ->groupBy('d')
        ->pluck('cnt', 'd');

    $messagesByDay = CoachMessage::where('coach_id', $coachId)
        ->where('direction', 'client_to_coach')
        ->where('created_at', '>=', $firstDay . ' 00:00:00')
        ->selectRaw('DATE(created_at) as d, COUNT(*) as cnt')
        ->groupBy('d')
        ->pluck('cnt', 'd');

    $ticketsByDay = Ticket::where('coach_id', $coachId)
        ->where('created_at', '>=', $firstDay . ' 00:00:00')
        ->selectRaw('DATE(created_at) as d, COUNT(*) as cnt')
        ->groupBy('d')
        ->pluck('cnt', 'd');

    $toSpark = function ($byDay) use ($days): string {
        $vals = $days->map(fn($d) => (int)($byDay->get($d) ?? 0))->toArray();
        $max  = max(max($vals), 1);
        $pts  = [];
        foreach ($vals as $i => $v) {
            $x    = $i * 10;
            $y    = (int) round(22 - ($v / $max) * 20);
            $pts[] = "{$x},{$y}";
        }
        return implode(' ', $pts);
    };

    return [
        'clients'  => $toSpark($trainingByDay),
        'checkins' => $toSpark($checkinsByDay),
        'messages' => $toSpark($messagesByDay),
        'tickets'  => $toSpark($ticketsByDay),
    ];
}
```

**Renamed legacy chart loaders** (keep compatible):
```php
protected function loadClientProgressData(Collection $clientIds): array
{
    return TrainingLog::whereIn('client_id', $clientIds)
        ->where('completed', true)
        ->where('log_date', '>=', now()->subWeeks(4)->toDateString())
        ->join('clients', 'training_logs.client_id', '=', 'clients.id')
        ->selectRaw('clients.name, COUNT(*) as sessions')
        ->groupBy('clients.name', 'training_logs.client_id')
        ->orderByDesc('sessions')
        ->limit(10)
        ->get()
        ->map(fn ($row) => [
            'name'     => explode(' ', $row->name)[0],
            'sessions' => (int) $row->sessions,
        ])
        ->toArray();
}

protected function loadCheckinFrequencyData(Collection $clientIds): array
{
    return Checkin::whereIn('client_id', $clientIds)
        ->where('checkin_date', '>=', now()->subWeeks(8)->toDateString())
        ->selectRaw("YEARWEEK(checkin_date, 1) as yw, COUNT(*) as count")
        ->groupBy('yw')
        ->orderBy('yw')
        ->get()
        ->map(function ($row) {
            $week = substr($row->yw, 4);
            return ['week' => 'Sem ' . $week, 'count' => (int) $row->count];
        })
        ->toArray();
}
```

### Phase 3 — Partials (2-3 hrs)

**`dashboard.blade.php` — the new thin orchestrator:**
```blade
<div>
  {{-- Desktop: alert + hero --}}
  <div class="hidden lg:block px-6 pt-6">
    @include('livewire.coach.partials.alert-bar')
    @include('livewire.coach.partials.hero-today', ['variant' => 'desktop'])
  </div>

  {{-- Mobile: hero + quick actions --}}
  <div class="lg:hidden px-4 pt-3 pb-2">
    @include('livewire.coach.partials.hero-today', ['variant' => 'mobile'])
  </div>
  <div class="lg:hidden px-4 pb-2">
    @include('livewire.coach.partials.quick-actions-mobile')
  </div>

  {{-- KPI stats (always visible) --}}
  <div class="px-4 lg:px-6 py-2 lg:py-4">
    @include('livewire.coach.partials.kpi-cards')
  </div>

  {{-- Main grid --}}
  <div class="px-4 lg:px-6 mt-2 lg:grid lg:grid-cols-12 lg:gap-5">
    {{-- Left 8 cols --}}
    <div class="lg:col-span-8 space-y-4">
      @include('livewire.coach.partials.urgent-clients')
      @include('livewire.coach.partials.today-activity')
      @include('livewire.coach.partials.charts-section')
    </div>
    {{-- Right 4 cols --}}
    <div class="lg:col-span-4 space-y-4 mt-4 lg:mt-0">
      @include('livewire.coach.partials.recent-messages')
      @include('livewire.coach.partials.tickets')
      @include('livewire.coach.partials.pending-checkins')
    </div>
  </div>
</div>
```

**`partials/alert-bar.blade.php`:**
```blade
@if($urgentClientsCount > 0)
<div class="mb-4 rounded-xl border-l-4 px-4 py-3 flex items-center justify-between animate-wc-rise"
     style="background:rgba(220,38,38,0.08); border-left-color:#DC2626"
     role="alert">
  <div class="flex items-center gap-3">
    <span class="animate-wc-breathe" aria-hidden="true">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
      </svg>
    </span>
    <span class="text-sm font-semibold text-wc-text">
      <span class="text-wc-accent">{{ $urgentClientsCount }} {{ $urgentClientsCount === 1 ? 'cliente' : 'clientes' }}</span>
      necesita{{ $urgentClientsCount === 1 ? '' : 'n' }} atención urgente hoy
    </span>
  </div>
  <a href="{{ route('coach.checkins') }}" wire:navigate
     class="text-xs text-wc-accent font-semibold hover:underline shrink-0">
    {{ __('coach_dashboard.alert_view_all') }}
  </a>
</div>
@endif
```

**`partials/hero-today.blade.php` (handles both variants):**
```blade
@php $variant = $variant ?? 'desktop'; @endphp

{{-- Desktop --}}
@if($variant === 'desktop')
<div class="flex items-end justify-between mb-6">
  <div>
    <p class="font-mono text-[11px] text-wc-text-tertiary uppercase tracking-widest mb-1">
      {{ now()->translatedFormat('l · j M Y') }}
    </p>
    <h1 class="font-display text-4xl font-bold uppercase tracking-wide text-wc-text">
      {{ __('coach_dashboard.hoy') }}
    </h1>
    <p class="text-sm text-wc-text-secondary mt-0.5">
      {{ $urgentClientsCount }} en riesgo · {{ $pendingCheckins }} check-ins pendientes · {{ $unreadMessages }} mensajes sin leer
    </p>
  </div>
  <div class="flex items-center gap-2">
    <a wire:navigate href="{{ route('coach.checkins') }}"
       class="h-9 px-4 rounded-lg border text-sm font-semibold text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary transition-colors"
       style="border-color:var(--color-wc-border)">
      {{ __('coach_dashboard.btn_view_checkins') }}
    </a>
    <a wire:navigate href="{{ route('coach.messages') }}"
       class="h-9 px-4 rounded-lg border text-sm font-semibold text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary transition-colors"
       style="border-color:var(--color-wc-border)">
      {{ __('coach_dashboard.btn_send_message') }}
    </a>
    <a wire:navigate href="{{ route('coach.clients') }}"
       class="h-9 px-4 rounded-lg bg-wc-accent hover:bg-wc-accent-hover text-white text-sm font-semibold transition-colors">
      {{ __('coach_dashboard.btn_new_client') }}
    </a>
  </div>
</div>

{{-- Mobile --}}
@else
<div class="wc-hero-accent rounded-xl border border-l-[3px] p-4 relative overflow-hidden"
     style="border-color:rgba(220,38,38,0.25); border-left-color:#DC2626">
  <p class="font-mono text-[10px] text-wc-text-tertiary uppercase tracking-widest">
    {{ now()->translatedFormat('D · j M Y') }}
  </p>
  @if($urgentClientsCount > 0)
    <h2 class="font-display text-xl font-bold uppercase tracking-wide text-wc-text mt-0.5">
      {{ $urgentClientsCount }} {{ $urgentClientsCount === 1 ? 'CLIENTE NECESITA' : 'CLIENTES NECESITAN' }} ATENCIÓN
    </h2>
    @if(count($attentionClients) > 0)
      <p class="text-xs text-wc-text-secondary mt-1">
        {{ collect($attentionClients)->take(3)->pluck('name')->map(fn($n) => explode(' ', $n)[0])->join(', ') }}
        {{ $urgentClientsCount > 3 ? ' y ' . ($urgentClientsCount - 3) . ' más' : '' }}
      </p>
    @endif
  @else
    <h2 class="font-display text-xl font-bold uppercase tracking-wide text-wc-text mt-0.5">
      {{ $greeting }}, {{ $coachName }}
    </h2>
    <p class="text-xs text-wc-text-secondary mt-1">Todo al día</p>
  @endif
  {{-- Today progress bar --}}
  @php
    $totalTasks = 6;
    $doneTasks = min(2, $totalTasks); // placeholder: improve with real task tracking
    $pct = ($totalTasks > 0) ? round($doneTasks / $totalTasks * 100) : 0;
  @endphp
  <div class="mt-3">
    <div class="flex items-center justify-between mb-1">
      <span class="text-[10px] text-wc-text-tertiary font-mono uppercase">TAREAS HOY</span>
      <span class="text-[10px] text-wc-text-secondary font-mono font-bold">{{ $doneTasks }}/{{ $totalTasks }} completadas</span>
    </div>
    <div class="h-1.5 rounded-full" style="background:rgba(255,255,255,0.1)">
      <div class="h-full rounded-full bg-wc-accent transition-all duration-500" style="width:{{ $pct }}%"></div>
    </div>
  </div>
</div>
@endif
```

**`components/coach/stat-card.blade.php`:**
```blade
@props([
    'label'       => '',
    'value'       => 0,
    'delta'       => '',
    'accentColor' => '#DC2626',
    'heroClass'   => 'wc-hero-accent',
    'borderColor' => 'rgba(220,38,38,0.25)',
    'spark'       => '0,22 10,18 20,16 30,16 40,10 50,10 60,2',
    'delay'       => '100',
])
<div class="stat-card {{ $heroClass }} rounded-xl p-4 border relative overflow-hidden cursor-pointer animate-wc-rise delay-{{ $delay }}"
     style="border-color:{{ $borderColor }}">
  {{-- SVG sparkline --}}
  <div class="absolute top-3 right-3 opacity-60" aria-hidden="true">
    <svg width="60" height="24" viewBox="0 0 60 24" fill="none">
      <polyline points="{{ $spark }}"
                stroke="{{ $accentColor }}"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"/>
    </svg>
  </div>
  {{-- Colored icon dot --}}
  <div class="w-7 h-7 rounded-lg flex items-center justify-center mb-2"
       style="background:{{ $accentColor }}22">
    <div class="w-3 h-3 rounded-full" style="background:{{ $accentColor }}"></div>
  </div>
  {{-- Counter value --}}
  <div class="font-display text-4xl font-bold tracking-tight leading-none font-data mt-1"
       style="color:{{ $accentColor }}"
       x-data
       x-init="
         const end = {{ (int) $value }};
         if (end === 0) { return; }
         let cur = 0;
         const step = Math.max(Math.floor(600 / end), 16);
         const t = setInterval(() => {
           cur = Math.min(cur + 1, end);
           $el.textContent = cur;
           if (cur >= end) clearInterval(t);
         }, step);
       ">{{ (int) $value }}</div>
  {{-- Label --}}
  <p class="text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary mt-1">
    {{ $label }}
  </p>
  {{-- Delta --}}
  @if($delta)
  <p class="text-[10px] text-wc-text-secondary mt-0.5 font-mono">{{ $delta }}</p>
  @endif
</div>
```

**`components/coach/urgent-client-row.blade.php`:**
```blade
@props(['client'])
<div class="swipe-item flex items-center gap-3 px-4 py-3.5 border-l-[3px] hover:bg-wc-bg-tertiary/50 transition-colors"
     style="border-left-color:#DC2626">
  {{-- Avatar --}}
  <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold font-display shrink-0"
       style="background:linear-gradient(135deg, {{ $client['avatar_a'] ?? '#f87171' }}, {{ $client['avatar_b'] ?? '#DC2626' }})">
    {{ $client['initials'] ?? strtoupper(substr($client['name'], 0, 1)) }}
  </div>
  {{-- Info --}}
  <div class="flex-1 min-w-0">
    <div class="flex items-center gap-2">
      <p class="text-sm font-semibold text-wc-text">{{ $client['name'] }}</p>
      <span class="text-[9px] font-bold uppercase rounded-full px-1.5 py-0.5 shrink-0 {{ $client['tag_class'] ?? 'bg-red-500/20 text-red-400' }}">
        {{ $client['tag'] ?? 'URGENTE' }}
      </span>
    </div>
    <p class="text-xs text-wc-text-tertiary truncate mt-0.5">{{ $client['reason'] ?? '' }}</p>
    <p class="text-[10px] font-mono text-wc-text-tertiary mt-0.5">{{ $client['last_seen'] ?? '' }}</p>
  </div>
  {{-- Action --}}
  <a href="{{ route('coach.checkins') }}" wire:navigate
     class="shrink-0 h-8 px-3 rounded-lg bg-wc-accent hover:bg-wc-accent-hover text-white text-xs font-semibold transition-colors">
    {{ __('coach_dashboard.btn_respond') }}
  </a>
</div>
```

**`partials/urgent-clients.blade.php`:**
```blade
<div class="rounded-xl border overflow-hidden" style="border-color:var(--color-wc-border); background:var(--color-wc-bg-secondary)">
  <div class="flex items-center justify-between px-4 py-3 border-b" style="border-color:var(--color-wc-border)">
    <div class="flex items-center gap-2">
      @if($urgentClientsCount > 0)
        <span class="w-2 h-2 rounded-full bg-wc-accent animate-wc-breathe" aria-hidden="true"></span>
      @endif
      <h3 class="text-sm font-bold uppercase tracking-widest font-display text-wc-text">
        {{ __('coach_dashboard.section_urgent') }}
      </h3>
    </div>
    <a href="{{ route('coach.checkins') }}" wire:navigate
       class="text-xs text-wc-accent font-semibold hover:underline">
      {{ __('coach_dashboard.btn_view_all') }}
    </a>
  </div>
  <div class="divide-y" style="border-color:var(--color-wc-border)">
    @forelse($attentionClients as $client)
      <x-coach.urgent-client-row :client="$client" />
    @empty
      <div class="flex flex-col items-center py-8 text-center">
        <svg class="h-10 w-10 text-wc-success/40 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
        </svg>
        <p class="text-sm text-wc-text-tertiary">{{ __('coach_dashboard.empty_urgent') }}</p>
        <p class="text-xs text-wc-text-tertiary mt-0.5">{{ __('coach_dashboard.empty_urgent_sub') }}</p>
      </div>
    @endforelse
  </div>
</div>
```

**`partials/today-activity.blade.php`:**
```blade
<div class="rounded-xl border overflow-hidden" style="border-color:var(--color-wc-border); background:var(--color-wc-bg-secondary)">
  <div class="flex items-center justify-between px-4 py-3 border-b" style="border-color:var(--color-wc-border)">
    <h3 class="text-sm font-bold uppercase tracking-widest font-display text-wc-text">
      {{ __('coach_dashboard.section_activity') }}
    </h3>
    <span class="font-mono text-[10px] text-wc-text-tertiary">Últimas 24h</span>
  </div>
  <div class="px-4 py-3 space-y-0">
    @forelse($todayActivity as $index => $event)
      <div class="flex gap-3 py-2.5 relative">
        <div class="relative flex flex-col items-center">
          <div class="w-2.5 h-2.5 rounded-full mt-0.5 shrink-0"
               style="background:{{ $event['color'] }}"
               aria-hidden="true"></div>
          @if(!$loop->last)
            <div class="w-px flex-1 mt-1" style="background:var(--color-wc-border); min-height:12px" aria-hidden="true"></div>
          @endif
        </div>
        <div class="flex-1 pb-1 min-w-0">
          <div class="flex items-start justify-between gap-2">
            <p class="text-xs font-medium text-wc-text leading-snug">{{ $event['text'] }}</p>
            <span class="font-mono text-[10px] text-wc-text-tertiary shrink-0">{{ $event['time'] }}</span>
          </div>
          @if(!empty($event['sub']))
            <p class="text-[10px] text-wc-text-tertiary mt-0.5">{{ $event['sub'] }}</p>
          @endif
        </div>
      </div>
    @empty
      <div class="flex flex-col items-center py-6 text-center">
        <p class="text-sm text-wc-text-tertiary">{{ __('coach_dashboard.empty_activity') }}</p>
      </div>
    @endforelse
  </div>
</div>
```

**`partials/recent-messages.blade.php`:**
```blade
<div class="rounded-xl border overflow-hidden" style="border-color:var(--color-wc-border); background:var(--color-wc-bg-secondary)">
  <div class="flex items-center justify-between px-4 py-3 border-b" style="border-color:var(--color-wc-border)">
    <h3 class="text-sm font-bold uppercase tracking-widest font-display text-wc-text">
      {{ __('coach_dashboard.section_messages') }}
    </h3>
    @if($unreadMessages > 0)
      <span class="text-[10px] font-bold bg-wc-accent/20 text-wc-accent rounded-full px-2 py-0.5 font-mono animate-wc-breathe"
            aria-live="polite">
        {{ __('coach_dashboard.unread_label', ['count' => $unreadMessages]) }}
      </span>
    @endif
  </div>
  <div class="divide-y" style="border-color:var(--color-wc-border)">
    @forelse($recentMessages as $msg)
      <div class="flex items-start gap-3 px-4 py-3 hover:bg-wc-bg-tertiary/50 transition-colors cursor-pointer">
        <div class="relative shrink-0">
          <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold font-display"
               style="background:linear-gradient(135deg, {{ '#' . substr(md5($msg['client_name']), 0, 6) }}, {{ '#' . substr(md5(strrev($msg['client_name'])), 0, 6) }})">
            {{ strtoupper(substr($msg['client_name'], 0, 1)) }}{{ strtoupper(substr(strstr($msg['client_name'], ' ') ?: '', 1, 1)) }}
          </div>
          @if(!$msg['is_read'])
            <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-wc-accent border-2"
                  style="border-color:var(--color-wc-bg-secondary)"
                  aria-label="No leído"></span>
          @endif
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between">
            <p class="text-xs font-semibold text-wc-text">{{ $msg['client_name'] }}</p>
            <span class="font-mono text-[9px] text-wc-text-tertiary shrink-0">{{ $msg['time_ago'] }}</span>
          </div>
          <p class="text-[11px] text-wc-text-secondary truncate mt-0.5">{{ $msg['message'] }}</p>
        </div>
      </div>
    @empty
      <div class="px-4 py-6 text-center">
        <p class="text-sm text-wc-text-tertiary">{{ __('coach_dashboard.empty_messages') }}</p>
      </div>
    @endforelse
    @if(count($recentMessages) > 0)
      <div class="px-4 py-2.5 text-center">
        <a wire:navigate href="{{ route('coach.messages') }}"
           class="text-xs text-wc-accent font-semibold hover:underline">
          {{ __('coach_dashboard.ver_todos_messages') }}
        </a>
      </div>
    @endif
  </div>
</div>
```

**`partials/tickets.blade.php`:**
```blade
<div class="rounded-xl border overflow-hidden" style="border-color:var(--color-wc-border); background:var(--color-wc-bg-secondary)">
  <div class="flex items-center justify-between px-4 py-3 border-b" style="border-color:var(--color-wc-border)">
    <h3 class="text-sm font-bold uppercase tracking-widest font-display text-wc-text">
      {{ __('coach_dashboard.section_tickets') }}
    </h3>
    @if($openTickets > 0)
      <span class="text-[10px] font-bold bg-wc-warning/20 text-wc-warning rounded-full px-2 py-0.5 font-mono">
        {{ __('coach_dashboard.open_label', ['count' => $openTickets]) }}
      </span>
    @endif
  </div>
  <div class="divide-y" style="border-color:var(--color-wc-border)">
    @forelse($openTicketsList as $ticket)
      <div class="flex items-center gap-3 px-4 py-3 hover:bg-wc-bg-tertiary/50 transition-colors cursor-pointer">
        <div class="w-1.5 h-1.5 rounded-full shrink-0"
             style="background:{{ $ticket['color'] }}"
             aria-hidden="true"></div>
        <div class="flex-1 min-w-0">
          <p class="text-xs font-medium text-wc-text truncate">{{ $ticket['subject'] }}</p>
          <p class="text-[10px] text-wc-text-tertiary font-mono">{{ $ticket['from'] }}</p>
        </div>
        <span class="text-[9px] font-bold uppercase rounded px-1.5 py-0.5 shrink-0 {{ $ticket['badge_class'] }}">
          {{ $ticket['priority'] }}
        </span>
      </div>
    @empty
      <div class="px-4 py-6 text-center">
        <p class="text-sm text-wc-text-tertiary">{{ __('coach_dashboard.empty_tickets') }}</p>
      </div>
    @endforelse
    @if(count($openTicketsList) > 0)
      <div class="px-4 py-2.5 text-center">
        <button class="text-xs text-wc-accent font-semibold hover:underline">
          {{ __('coach_dashboard.ver_todos_tickets') }}
        </button>
      </div>
    @endif
  </div>
</div>
```

**`partials/pending-checkins.blade.php`:**
```blade
<div class="rounded-xl border overflow-hidden" style="border-color:var(--color-wc-border); background:var(--color-wc-bg-secondary)">
  <div class="flex items-center justify-between px-4 py-3 border-b" style="border-color:var(--color-wc-border)">
    <h3 class="text-sm font-bold uppercase tracking-widest font-display text-wc-text">
      {{ __('coach_dashboard.section_checkins') }}
    </h3>
    @if($pendingCheckins > 0)
      <span class="text-[10px] font-bold bg-wc-accent/20 text-wc-accent rounded-full px-2 py-0.5 font-mono">
        {{ __('coach_dashboard.review_label', ['count' => $pendingCheckins]) }}
      </span>
    @endif
  </div>
  <div class="divide-y" style="border-color:var(--color-wc-border)">
    @forelse($pendingCheckinsList as $ci)
      <div class="flex items-center gap-3 px-4 py-3 hover:bg-wc-bg-tertiary/50 transition-colors">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold font-display shrink-0"
             style="background:linear-gradient(135deg, {{ $ci['avatar_a'] }}, {{ $ci['avatar_b'] }})">
          {{ $ci['initials'] }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-xs font-semibold text-wc-text">{{ $ci['name'] }}</p>
          <p class="text-[10px] text-wc-text-tertiary font-mono">{{ $ci['week'] }}</p>
        </div>
        <a wire:navigate href="{{ route('coach.checkins') }}"
           class="shrink-0 h-7 px-2.5 rounded-lg bg-wc-success/15 text-wc-success text-[10px] font-semibold hover:bg-wc-success/25 transition-colors">
          {{ __('coach_dashboard.btn_review') }}
        </a>
      </div>
    @empty
      <div class="px-4 py-6 text-center">
        <p class="text-sm text-wc-text-tertiary">{{ __('coach_dashboard.empty_checkins') }}</p>
      </div>
    @endforelse
  </div>
</div>
```

**`partials/charts-section.blade.php`:**
```blade
<div class="rounded-xl border overflow-hidden" style="border-color:var(--color-wc-border); background:var(--color-wc-bg-secondary)"
     x-data="{ open: false }">
  <button x-on:click="open=!open"
          class="w-full flex items-center justify-between px-4 py-3 border-b hover:bg-wc-bg-tertiary/30 transition-colors"
          style="border-color:var(--color-wc-border)"
          :aria-expanded="open">
    <h3 class="text-sm font-bold uppercase tracking-widest font-display text-wc-text">
      {{ __('coach_dashboard.section_analysis') }}
    </h3>
    <div class="flex items-center gap-2 text-xs text-wc-accent font-semibold">
      <span x-text="open ? '{{ __('coach_dashboard.charts_colapsar') }}' : '{{ __('coach_dashboard.charts_ver') }}'"></span>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
           :class="open ? 'rotate-180' : ''" class="transition-transform" aria-hidden="true">
        <polyline points="6 9 12 15 18 9"></polyline>
      </svg>
    </div>
  </button>
  <div x-show="open" x-transition class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- Clients active area chart --}}
    <div>
      <p class="text-[10px] uppercase tracking-widest font-semibold text-wc-text-tertiary mb-2">
        {{ __('coach_dashboard.chart_clientes_label') }}
      </p>
      @php
        $progData = $clientProgressData;
        $maxSessions = max(array_column($progData ?: [['sessions' => 1]], 'sessions'));
      @endphp
      @if(count($clientProgressData) > 0)
        <svg viewBox="0 0 240 80" class="w-full" style="height:80px" aria-label="Gráfico de sesiones por cliente">
          @foreach($clientProgressData as $idx => $point)
            @php
              $x = 2 + $idx * (236 / max(count($clientProgressData) - 1, 1));
              $y = 80 - round(($point['sessions'] / $maxSessions) * 70) - 4;
            @endphp
            <circle cx="{{ $x }}" cy="{{ $y }}" r="3" fill="#3B82F6"/>
          @endforeach
          <defs>
            <linearGradient id="grad-clients" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#3B82F6" stop-opacity="0.3"/>
              <stop offset="100%" stop-color="#3B82F6" stop-opacity="0"/>
            </linearGradient>
          </defs>
          {{-- Build path from data --}}
        </svg>
      @else
        <div class="flex items-center justify-center h-20 text-wc-text-tertiary/40">
          <p class="text-xs">Sin datos</p>
        </div>
      @endif
    </div>
    {{-- Check-ins frequency bar chart --}}
    <div>
      <p class="text-[10px] uppercase tracking-widest font-semibold text-wc-text-tertiary mb-2">
        {{ __('coach_dashboard.chart_checkins_label') }}
      </p>
      @if(count($checkinFrequencyData) > 0)
        @php $maxCount = max(array_column($checkinFrequencyData, 'count')); @endphp
        <svg viewBox="0 0 240 80" class="w-full" style="height:80px" aria-label="Frecuencia de check-ins">
          @foreach($checkinFrequencyData as $idx => $point)
            @php
              $barW = max(4, floor(236 / count($checkinFrequencyData)) - 4);
              $x = 2 + $idx * (240 / count($checkinFrequencyData));
              $h = max(4, round(($point['count'] / $maxCount) * 70));
              $y = 80 - $h;
            @endphp
            <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barW }}" height="{{ $h }}"
                  rx="4" fill="#10B981" fill-opacity="0.7"/>
          @endforeach
        </svg>
        <div class="flex justify-between text-[9px] font-mono text-wc-text-tertiary mt-1">
          @foreach($checkinFrequencyData as $point)
            <span>{{ $point['week'] }}</span>
          @endforeach
        </div>
      @else
        <div class="flex items-center justify-center h-20 text-wc-text-tertiary/40">
          <p class="text-xs">Sin datos</p>
        </div>
      @endif
    </div>
  </div>
</div>
```

**`partials/kpi-cards.blade.php`:**
```blade
<div class="grid grid-cols-2 md:grid-cols-4 gap-3">
  <x-coach.stat-card
    :label="__('coach_dashboard.stat_clients')"
    :value="$activeClients"
    :delta="'↑ +' . max(0, $activeClients - 20) . ' esta semana'"
    accentColor="#3B82F6"
    heroClass="wc-hero-blue"
    borderColor="rgba(59,130,246,0.25)"
    :spark="$sparklines['clients'] ?? '0,22 10,18 20,16 30,16 40,10 50,10 60,2'"
    delay="100"
  />
  <x-coach.stat-card
    :label="__('coach_dashboard.stat_checkins')"
    :value="$pendingCheckins"
    :delta="$pendingCheckins > 0 ? $pendingCheckins . ' sin responder' : 'Todo respondido'"
    accentColor="#F59E0B"
    heroClass="wc-hero-amber"
    borderColor="rgba(245,158,11,0.25)"
    :spark="$sparklines['checkins'] ?? '0,18 10,12 20,8 30,20 40,14 50,6 60,0'"
    delay="200"
  />
  <x-coach.stat-card
    :label="__('coach_dashboard.stat_messages')"
    :value="$unreadMessages"
    :delta="$unreadMessages > 0 ? $unreadMessages . ' sin leer' : 'Sin mensajes nuevos'"
    accentColor="#DC2626"
    heroClass="wc-hero-accent"
    borderColor="rgba(220,38,38,0.25)"
    :spark="$sparklines['messages'] ?? '0,20 10,16 20,22 30,10 40,14 50,8 60,4'"
    delay="300"
  />
  <x-coach.stat-card
    :label="__('coach_dashboard.stat_tickets')"
    :value="$openTickets"
    :delta="$openTickets > 0 ? $openTickets . ' abiertos' : 'Sin tickets'"
    accentColor="#10B981"
    heroClass="wc-hero-emerald"
    borderColor="rgba(16,185,129,0.25)"
    :spark="$sparklines['tickets'] ?? '0,10 10,14 20,18 30,12 40,18 50,22 60,18'"
    delay="400"
  />
</div>
```

### Phase 4 — Interactivity (1-2 hrs)

**`resources/js/coach-dashboard.js`:**
```javascript
/**
 * WellCore Coach Dashboard — Alpine interactivity
 * Swipe handlers for urgent client rows
 */

function initCoachSwipe() {
    const rows = document.querySelectorAll('.swipe-item');
    rows.forEach(row => {
        if (row._swipeAttached) return;
        row._swipeAttached = true;

        let startX = 0;
        let startY = 0;
        let isHorizontal = null;

        row.addEventListener('touchstart', e => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            isHorizontal = null;
        }, { passive: true });

        row.addEventListener('touchmove', e => {
            const diffX = e.touches[0].clientX - startX;
            const diffY = e.touches[0].clientY - startY;

            if (isHorizontal === null) {
                isHorizontal = Math.abs(diffX) > Math.abs(diffY);
            }
            if (!isHorizontal) return;

            if (diffX < 0) {
                row.style.transform = `translateX(${Math.max(-80, diffX * 0.6)}px)`;
            }
        }, { passive: true });

        row.addEventListener('touchend', e => {
            const diffX = e.changedTouches[0].clientX - startX;
            if (isHorizontal && diffX < -60) {
                row.style.transform = 'translateX(-72px)';
                // Reveal hidden action, then snap back after 2s
                setTimeout(() => {
                    row.style.transform = '';
                }, 2000);
            } else {
                row.style.transform = '';
            }
            isHorizontal = null;
        });
    });
}

// Re-attach after Livewire navigation
document.addEventListener('DOMContentLoaded', initCoachSwipe);
document.addEventListener('livewire:navigated', initCoachSwipe);
```

Import this in `resources/js/app.js` after the existing imports:
```javascript
// Add at end of app.js, guarded by route:
if (window.location.pathname.startsWith('/coach')) {
    import('./coach-dashboard.js');
}
```

### Phase 5 — Responsive pass + optimization (1 hr)

**Verify breakpoint contract:**

Mobile 390px:
- Body has `overflow-x: hidden`
- Stats grid: `grid-cols-2` (base) — 2x2 layout
- Hero card: full width, `rounded-xl`, no horizontal overflow
- Bottom nav height: `h-16` + `pb-safe`
- FAB: `absolute left-1/2 -translate-x-1/2 -top-7` correctly centers

Tablet 768px:
- Stats grid: `md:grid-cols-4` — 4x1 row
- Bottom nav still visible (`lg:hidden`)
- No sidebar

Desktop 1024px+:
- Sidebar visible, `w-60` or `w-[4.5rem]` when collapsed
- Bottom nav hidden (`lg:hidden`)
- Main content grid: `lg:grid lg:grid-cols-12 lg:gap-5`
- Stat cards: `md:grid-cols-4` remains

**Performance checklist:**
- All avatar avatars use `loading="lazy" decoding="async"` if `<img>` tags are used (prefer initials + gradient instead — no images)
- `wire:key` on all `@forelse` loops: `wire:key="urgent-{{ $client['id'] }}"`, `wire:key="msg-{{ $loop->index }}"`, etc.
- Charts section: lazy via `x-show="open"` + `x-transition` — SVG only renders when expanded
- Redis TTL: `Cache::remember("coach_dashboard:{$coachId}", 300, ...)` — 5 min

### Phase 6 — Testing + Lighthouse (1 hr)

**Chrome DevTools checklist:**

Desktop 1440x900:
- [ ] Sidebar: 240px wide, 4 sections, collapse works, icon-only mode at 72px
- [ ] Top bar: breadcrumb + bell + dark toggle + avatar
- [ ] Alert bar: visible when urgentClientsCount > 0, hidden otherwise
- [ ] "HOY" H1 in Oswald, 4xl, uppercase
- [ ] 4 KPI cards: hero gradients visible, sparklines at top-right, counter animates
- [ ] Main grid: 8/4 col split
- [ ] Urgent clients: left border, initials, tag badge, Responder button
- [ ] Activity timeline: colored dots + vertical connectors
- [ ] Charts: collapsed by default, expand on click
- [ ] Messages/Tickets/Checkins in right column

Mobile 390x844 (iPhone 14):
- [ ] No horizontal scroll
- [ ] Hero card visible, progress bar
- [ ] Quick actions: horizontal chip scroll
- [ ] Stats 2x2
- [ ] Bottom nav: 4 items + FAB centered at top
- [ ] FAB: tap opens sheet, backdrop blurs content
- [ ] Sheet: 3 action items with icon + title + subtitle

Dark mode (both):
- [ ] Background #09090B, cards #18181B, text #FAFAFA
- [ ] No hardcoded light colors bleeding through
- [ ] Dark toggle persists on F5

Empty state:
- [ ] 0 urgent clients: no alert bar, hero shows greeting
- [ ] 0 messages: empty state message
- [ ] 0 tickets: empty state message
- [ ] 0 check-ins: empty state message

Lighthouse on Desktop 1440:
- [ ] Performance >= 90
- [ ] Accessibility >= 95
- Fixes required: `aria-label` on FAB button, `aria-hidden` on all decorative SVGs, `aria-live` on unread message badge, `role="alert"` on alert bar, `role="dialog"` on FAB bottom sheet

### Phase 7 — Deploy

```bash
git add -A
git commit -m "feat(coach-dashboard): full redesign phases 0-6 complete"
git push origin feature/coach-dashboard-redesign
```

Then open PR → merge to main → gitpull-load in EasyPanel (NO rebuild Docker).

---

## 5. Critical Code Snippets (summary)

All critical code is embedded within the phase sections above. Key file locations:

- `/app/Livewire/Coach/Dashboard.php` — mount() cache block, all 7 loader methods
- `/resources/views/layouts/coach.blade.php` — Alpine stores inline script, sidebar HTML, bottom nav HTML, FAB sheet HTML
- `/resources/views/components/coach/stat-card.blade.php` — counter animation x-init, sparkline SVG
- `/resources/views/components/coach/urgent-client-row.blade.php` — swipe-item class, gradient avatar
- `/resources/views/livewire/coach/partials/*.blade.php` — all 9 partial files with exact blade syntax

---

## 6. Performance Considerations

| Concern | Solution |
|---|---|
| N+1 on client names | All loaders use `Client::whereIn()->pluck('name','id')` then in-memory lookup |
| N+1 on checkins | `Checkin::whereIn('client_id', $clientIds)->whereNull('coach_reply')->...->get()` — single query |
| Heavy mount() on every render | `Cache::remember("coach_dashboard:{$coachId}", 300, ...)` — 5-min TTL |
| Chart.js 80KB on dashboard | Replaced with inline SVG — zero JS library overhead for charts on dashboard |
| Alpine stores on navigation | `$store.coachSidebar` persists via localStorage, survives wire:navigate |
| SVG sparklines | Pre-computed server-side as polyline point strings — zero client-side math |
| Layout badge counts | Separate `Cache::remember("coach_layout_counts:{$coachId}", 120, ...)` — 2-min TTL |
| Livewire re-renders | `wire:key` on all loops prevents unnecessary DOM diffing |

**Cache invalidation:** When `CheckinReview` component saves a reply, it should dispatch `Cache::forget("coach_dashboard:{$coachId}")`. Add this to `CheckinReview.php`'s save method (Phase 2 extension).

---

## 7. Integrity and Testing

**Do NOT touch these files under any circumstances:**
- `app/Livewire/Coach/ClientList.php`
- `app/Livewire/Coach/CheckinReview.php`
- `app/Livewire/Coach/MessageCenter.php`
- `app/Livewire/Coach/Analytics.php`
- `app/Auth/WellCoreGuard.php`
- `routes/web.php`
- Any file under `resources/views/livewire/client/`
- Any migration in `database/migrations/`
- `wellcorefitness` vanilla PHP app

**Feature tests to write:**
File: `tests/Feature/Coach/DashboardTest.php`

```php
<?php

use App\Livewire\Coach\Dashboard;
use App\Models\Admin;
use Livewire\Livewire;

test('coach dashboard mounts without errors', function () {
    $coach = Admin::where('role', 'coach')->first();
    if (!$coach) {
        $this->markTestSkipped('No coach in test DB');
    }
    $this->actingAs($coach, 'wellcore');

    Livewire::test(Dashboard::class)
        ->assertStatus(200)
        ->assertSee('HOY');
});

test('coach dashboard shows human empty states with zero data', function () {
    // Coach with no assigned clients
    $coach = Admin::factory()->create(['role' => 'coach']);
    $this->actingAs($coach, 'wellcore');

    Livewire::test(Dashboard::class)
        ->assertSee('Todos los check-ins respondidos')
        ->assertDontSee('undefined');
});
```

**Manual validation checklist:**
- [ ] Superadmin impersonation banner shows in violet at top of page
- [ ] `← Volver al Admin` form works on POST
- [ ] Logout form in sidebar footer works (POST to `route('logout')`)
- [ ] Dark/light toggle button in top bar persists class on `<html>` after reload
- [ ] `sidebarCollapsed` state persists in localStorage after collapse and page reload
- [ ] `wire:navigate` on sidebar links preserves Livewire SPA navigation

---

## 8. Risks and Mitigations

| Risk | Probability | Mitigation |
|---|---|---|
| Breaking vanilla PHP auth | Low | Zero DB schema changes. WellCoreGuard reads `auth_tokens` table unmodified. |
| Mobile sidebar overlay `z-index` conflict | Medium | Sidebar z-50, overlay z-40, bottom-nav z-30, FAB bottom-sheet z-50. All correct. |
| Ticket model missing `coach_id` column | Low | Confirmed in `app/Models/Ticket.php` — `coach_id` is in `#[Fillable]` list. |
| Alpine stores collision | Low | `coachSidebar` store name is unique. `darkMode` store kept identical. Root `x-data` only declares `sidebarMobileOpen` and `fabOpen`. |
| `Cache::remember` closure captures `$this` | Medium | All loader methods are `protected` methods called explicitly. Pass `$clientIds` and `$coachId` as params, never reference `$this` inside the closure. |
| Chart.js still loaded but unused | Low | Remove `@if(count($clientProgressData) > 0) new Chart(...)` blocks from dashboard. Chart.js can remain for Analytics component. |
| Translation key missing | Low | All strings in `lang/es/coach_dashboard.php` — complete key list in Phase 0. |
| Empty `$sparklines` array on first load | Low | Each `stat-card` has a safe default spark string fallback via `?? '0,22 10,..'. |

---

## 9. Pre-merge Checklist

- [ ] Dashboard loads in <2s with real data (check with `php artisan tinker` + timing)
- [ ] Mobile 390px: no horizontal scroll (Chrome DevTools mobile emulation)
- [ ] Dark/light toggle works and persists on reload
- [ ] Sidebar collapse works and persists on reload
- [ ] Counter animation fires on all 4 KPI cards at first render
- [ ] FAB button opens bottom sheet with slide-up animation
- [ ] Swipe left on urgent client row moves row left
- [ ] Empty states: no bare "0" or empty elements
- [ ] All visible strings go through `__('coach_dashboard.*')` helpers
- [ ] Lighthouse Performance >= 90 on desktop
- [ ] Lighthouse Accessibility >= 95
- [ ] Superadmin impersonation banner still works
- [ ] Logout button still works
- [ ] No `console.error` on page load
- [ ] `php artisan test --filter=CoachDashboard` passes
- [ ] Responsive at 390px, 768px, 1024px, 1440px all look correct