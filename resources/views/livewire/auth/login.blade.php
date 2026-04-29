<div
    x-data="{
        showPassword: false,
        sheetOpen: false,
        toggleSheet(open) {
            this.sheetOpen = open;
            document.body.style.overflow = open ? 'hidden' : '';
        },
        init() {
            // Listen for Livewire success → write 6 SPA keys to localStorage (vanilla compat) → redirect
            Livewire.on('login-success', (params) => {
                const data = Array.isArray(params) ? params[0] : params;
                const token = data?.token;
                const userType = data?.userType;
                const userId = data?.userId;
                const userName = data?.userName;
                const redirectUrl = data?.redirectUrl;
                const userPortal = data?.userPortal;
                const forcePasswordChange = data?.forcePasswordChange;

                if (token) {
                    localStorage.setItem('wc_token', token);
                    localStorage.setItem('wc_user_type', userType || '');
                    if (userId !== undefined && userId !== null) {
                        localStorage.setItem('wc_user_id', String(userId));
                    }
                    if (userName) {
                        localStorage.setItem('wc_user_name', userName);
                    }
                    if (userPortal) {
                        localStorage.setItem('wc_user_portal', userPortal);
                    }
                    if (forcePasswordChange) {
                        localStorage.setItem('wc_force_password_change', 'true');
                    } else {
                        localStorage.removeItem('wc_force_password_change');
                    }
                }
                setTimeout(() => { window.location.href = redirectUrl || '/client'; }, 600);
            });

            // ESC closes sheet
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.sheetOpen) this.toggleSheet(false);
            });
        }
    }"
    class="auth-page-root"
>

{{-- ════════════════════════════════════════════════════════════════
     iOS-feel auth styles — inline para evitar npm build (Sprint 4 noche).
     Migrar a resources/css/auth.css + @import en app.css cuando se compile.
     ════════════════════════════════════════════════════════════════ --}}
<style>
.auth-page-root {
    --auth-bg:        #0a0a0a;
    --auth-bg-2:      #111111;
    --auth-bg-3:      #1a1a1a;
    --auth-text:      #FAFAFA;
    --auth-text-2:    #A3A3A3;
    --auth-text-3:    #737373;
    --auth-text-4:    #525252;
    --auth-border:    rgba(255,255,255,0.07);
    --auth-border-2:  rgba(255,255,255,0.12);
    --auth-red:       #DC2626;
    --auth-red-hover: #B91C1C;
    --auth-red-text:  #F87171;
    --auth-green:     #10B981;
    --auth-green-text:#34D399;
    --auth-gold:      #D4A04C;
    --auth-ios-blue:  #0A84FF;
    --auth-ease-out:  cubic-bezier(.22,1,.36,1);
    --auth-ease-ios:  cubic-bezier(.32,.72,0,1);

    position: relative;
    min-height: calc(100vh - 64px);
    min-height: calc(100dvh - 64px);
    background: var(--auth-bg);
    color: var(--auth-text);
    overflow-x: hidden;
    isolation: isolate;
}

/* Atmosphere + grain (scoped, no fixed → no clash con nav sticky del layout) */
.auth-page-root::before {
    content: '';
    position: absolute; inset: 0;
    pointer-events: none; z-index: 0;
    background:
        radial-gradient(ellipse 70% 40% at 0% -10%, rgba(220,38,38,0.10), transparent 55%),
        radial-gradient(ellipse 50% 30% at 110% 10%, rgba(220,38,38,0.05), transparent 50%),
        radial-gradient(ellipse 80% 50% at 50% 110%, rgba(220,38,38,0.05), transparent 60%);
}
.auth-page-root::after {
    content: '';
    position: absolute; inset: 0;
    pointer-events: none; z-index: 0;
    opacity: 0.025;
    mix-blend-mode: overlay;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    background-size: 220px;
}

.auth-shell {
    position: relative; z-index: 1;
    min-height: calc(100vh - 64px);
    min-height: calc(100dvh - 64px);
    padding-left: env(safe-area-inset-left);
    padding-right: env(safe-area-inset-right);
    display: flex; flex-direction: column;
}

/* ── Hero large title ──────────────────────────────────────── */
.auth-hero { padding: 28px 24px 8px; }
.auth-hero-eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 11px; letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--auth-text-3);
    margin-bottom: 10px;
}
.auth-hero-eyebrow::before {
    content: ''; width: 6px; height: 6px; border-radius: 50%;
    background: var(--auth-red);
    box-shadow: 0 0 0 3px rgba(220,38,38,0.18);
}
.auth-hero-title {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: clamp(48px, 13.5vw, 64px);
    line-height: 0.92;
    letter-spacing: 0.005em;
    color: var(--auth-text);
    text-transform: uppercase;
    margin-bottom: 12px;
}
.auth-hero-title em {
    font-style: normal;
    color: var(--auth-red);
}
.auth-hero-sub {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-weight: 400;
    font-size: 17px;
    line-height: 1.35;
    color: var(--auth-gold);
    max-width: 28ch;
}

/* ── Form stack ─────────────────────────────────────────────── */
.auth-form-stack {
    padding: 28px 20px 24px;
    display: flex; flex-direction: column;
    gap: 16px;
}

/* iOS card */
.auth-card {
    position: relative;
    background: rgba(28, 28, 30, 0.62);
    -webkit-backdrop-filter: blur(40px) saturate(180%);
    backdrop-filter: blur(40px) saturate(180%);
    border: 0.5px solid rgba(255, 255, 255, 0.10);
    border-radius: 18px;
    overflow: hidden;
    box-shadow:
        0 1px 0 rgba(255,255,255,0.04) inset,
        0 12px 40px -12px rgba(0,0,0,0.55);
}
.auth-card.shake { animation: auth-shake .42s var(--auth-ease-out); }
@keyframes auth-shake {
    0%,100% { transform: translateX(0); }
    20% { transform: translateX(-8px); }
    40% { transform: translateX(7px); }
    60% { transform: translateX(-5px); }
    80% { transform: translateX(3px); }
}

.auth-group-caption {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 11px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--auth-text-3);
    padding: 0 6px 8px;
}
.auth-group-footnote {
    font-size: 12px;
    color: var(--auth-text-3);
    padding: 8px 6px 0;
    line-height: 1.45;
}

/* iOS row */
.auth-row {
    display: flex; align-items: center;
    gap: 12px;
    padding: 14px 16px;
    min-height: 54px;
    position: relative;
}
.auth-row + .auth-row::before {
    content: '';
    position: absolute; top: 0; left: 16px; right: 0;
    height: 0.5px;
    background: rgba(255,255,255,0.10);
}
.auth-row-label {
    flex: 0 0 auto;
    width: 110px;
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 15px;
    font-weight: 500;
    color: var(--auth-text);
    letter-spacing: -0.01em;
}
.auth-row-input {
    flex: 1 1 auto;
    background: transparent; border: none; outline: none;
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 15px;
    color: var(--auth-text);
    text-align: right;
    letter-spacing: -0.005em;
    min-width: 0;
    padding: 4px 0;
    -webkit-tap-highlight-color: transparent;
}
.auth-row-input::placeholder { color: var(--auth-text-3); }
.auth-row-input:-webkit-autofill,
.auth-row-input:-webkit-autofill:hover,
.auth-row-input:-webkit-autofill:focus {
    -webkit-text-fill-color: var(--auth-text);
    -webkit-box-shadow: 0 0 0px 1000px rgba(28,28,30,0.62) inset;
    transition: background-color 9999s ease-in-out 0s;
}
.auth-row.is-focused::after {
    content: '';
    position: absolute; left: 16px; right: 16px; bottom: 0;
    height: 1.5px;
    background: var(--auth-ios-blue);
    border-radius: 2px;
    opacity: 0.85;
}
.auth-row.has-error::after {
    background: var(--auth-red);
    opacity: 1;
}
.auth-row .row-eye {
    background: none; border: none;
    width: 28px; height: 28px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 8px;
    color: var(--auth-text-3);
    cursor: pointer;
    transition: color .15s, transform .1s var(--auth-ease-out);
    -webkit-tap-highlight-color: transparent;
}
.auth-row .row-eye:active { transform: scale(0.9); color: var(--auth-text); }
.auth-row .row-eye.is-on { color: var(--auth-red-text); }

.auth-row-error {
    display: block;
    padding: 8px 16px 0;
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 13px;
    color: var(--auth-red-text);
    letter-spacing: -0.005em;
}

/* Toggle row (iOS switch) */
.auth-toggle-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px;
    min-height: 54px;
}
.auth-toggle-row .label {
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 15px; font-weight: 500;
    color: var(--auth-text);
}
.auth-toggle-row .sub {
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 12px; color: var(--auth-text-3);
    margin-top: 2px;
    max-width: 28ch;
}
.auth-switch {
    position: relative;
    width: 51px; height: 31px;
    border-radius: 999px;
    background: rgba(120, 120, 128, 0.32);
    transition: background .25s var(--auth-ease-ios);
    flex-shrink: 0;
    border: none;
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
}
.auth-switch.is-on { background: var(--auth-green); }
.auth-switch::after {
    content: '';
    position: absolute; top: 2px; left: 2px;
    width: 27px; height: 27px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 3px 8px rgba(0,0,0,0.35), 0 1px 1px rgba(0,0,0,0.2);
    transition: transform .25s var(--auth-ease-ios);
}
.auth-switch.is-on::after { transform: translateX(20px); }

.auth-forgot-link {
    background: none; border: none;
    display: inline-flex; align-items: center; gap: 4px;
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 14px; font-weight: 500;
    color: var(--auth-red-text);
    padding: 6px 4px;
    align-self: flex-end;
    cursor: pointer;
    margin-left: auto;
    -webkit-tap-highlight-color: transparent;
}
.auth-forgot-link:active { opacity: 0.6; }

/* Submit button pill */
.auth-submit {
    position: relative;
    width: 100%;
    background: var(--auth-red);
    border: none;
    border-radius: 999px;
    padding: 18px 24px;
    min-height: 54px;
    font-family: 'Oswald', Impact, sans-serif;
    font-size: 18px; letter-spacing: 0.18em; font-weight: 600;
    text-transform: uppercase;
    color: #fff;
    white-space: nowrap;
    display: inline-flex; align-items: center; justify-content: center;
    gap: 10px;
    transition: transform .12s var(--auth-ease-out), background .15s, box-shadow .2s, opacity .2s;
    box-shadow:
        0 12px 32px -10px rgba(220,38,38,0.55),
        0 2px 0 rgba(0,0,0,0.15) inset,
        0 1px 0 rgba(255,255,255,0.2) inset;
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
}
.auth-submit:active:not(:disabled) { transform: scale(0.97); background: var(--auth-red-hover); }
.auth-submit:disabled { opacity: 0.7; cursor: not-allowed; }
.auth-submit.is-success {
    background: var(--auth-green);
    box-shadow: 0 12px 32px -10px rgba(16,185,129,0.55);
}
.auth-submit .spinner {
    width: 18px; height: 18px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: auth-spin .7s linear infinite;
}
@keyframes auth-spin { to { transform: rotate(360deg); } }

/* Trust strip */
.auth-trust-strip {
    display: flex; align-items: center; justify-content: center; gap: 14px;
    padding: 8px 24px 0;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 10.5px; letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--auth-text-4);
    flex-wrap: wrap;
}
.auth-trust-strip .dot {
    width: 4px; height: 4px; border-radius: 50%;
    background: var(--auth-text-4);
}

/* Bottom actions */
.auth-bottom-actions {
    margin-top: auto;
    padding: 28px 24px 24px;
    text-align: center;
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 14px;
    color: var(--auth-text-2);
}
.auth-bottom-actions a {
    color: var(--auth-text);
    font-weight: 600;
    margin-left: 6px;
    letter-spacing: -0.005em;
    text-decoration: none;
    border-bottom: 1px solid rgba(255,255,255,0.18);
    padding-bottom: 1px;
    transition: color .2s, border-color .2s;
}
.auth-bottom-actions a:hover { color: var(--auth-red-text); border-color: var(--auth-red-text); }

/* No-access WhatsApp */
.auth-no-access {
    text-align: center;
    padding: 6px 24px 0;
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 13px;
    color: var(--auth-text-3);
}
.auth-no-access a {
    color: var(--auth-text-2);
    text-decoration: underline;
    text-underline-offset: 3px;
    text-decoration-color: rgba(255,255,255,0.20);
}
.auth-no-access a:hover { color: var(--auth-green-text); text-decoration-color: var(--auth-green-text); }

/* ── Bottom sheet (forgot password) ─────────────────────────── */
.auth-sheet-backdrop {
    position: fixed; inset: 0; z-index: 90;
    background: rgba(0,0,0,0);
    -webkit-backdrop-filter: blur(0px);
    backdrop-filter: blur(0px);
    pointer-events: none;
    transition: background .35s var(--auth-ease-ios), backdrop-filter .35s var(--auth-ease-ios);
}
.auth-sheet-backdrop.is-open {
    background: rgba(0,0,0,0.55);
    -webkit-backdrop-filter: blur(8px);
    backdrop-filter: blur(8px);
    pointer-events: auto;
}
.auth-sheet {
    position: fixed; left: 0; right: 0; bottom: 0;
    z-index: 100;
    background: rgba(28,28,30,0.95);
    -webkit-backdrop-filter: blur(60px) saturate(180%);
    backdrop-filter: blur(60px) saturate(180%);
    border-top: 0.5px solid rgba(255,255,255,0.12);
    border-radius: 28px 28px 0 0;
    padding: 12px 24px calc(env(safe-area-inset-bottom) + 28px);
    transform: translateY(110%);
    transition: transform .42s var(--auth-ease-ios);
    box-shadow: 0 -20px 60px -20px rgba(0,0,0,0.6);
    max-height: 92vh;
    overflow-y: auto;
}
.auth-sheet.is-open { transform: translateY(0); }
.auth-sheet-handle {
    width: 36px; height: 5px;
    background: rgba(255,255,255,0.28);
    border-radius: 999px;
    margin: 8px auto 18px;
}
.auth-sheet-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 12px;
}
.auth-sheet-title {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 24px; letter-spacing: 0.02em;
    text-transform: uppercase;
    white-space: nowrap;
}
.auth-sheet-cancel {
    background: none; border: none; cursor: pointer;
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 15px; color: var(--auth-red-text); font-weight: 500;
    padding: 6px 4px;
    -webkit-tap-highlight-color: transparent;
}
.auth-sheet-cancel:active { opacity: 0.5; }
.auth-sheet-sub {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    color: var(--auth-text-2);
    font-size: 15px;
    line-height: 1.4;
    margin-bottom: 18px;
    max-width: 38ch;
}

/* Inline error banner (Livewire validation) */
.auth-error-banner {
    background: rgba(220,38,38,0.08);
    border: 1px solid rgba(220,38,38,0.22);
    border-radius: 14px;
    padding: 12px 14px;
    display: flex; align-items: flex-start; gap: 10px;
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 13.5px;
    color: var(--auth-red-text);
    line-height: 1.4;
}
.auth-error-banner svg { flex-shrink: 0; margin-top: 1px; }

/* ── Desktop (≥1024px) split 50/50 ──────────────────────────── */
@media (min-width: 1024px) {
    .auth-shell {
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: stretch;
    }
    .auth-aside {
        position: relative;
        display: flex; flex-direction: column;
        padding: 56px 56px 48px;
        border-right: 1px solid var(--auth-border);
        background:
            linear-gradient(180deg, rgba(10,10,10,0) 0%, rgba(10,10,10,0.5) 100%),
            radial-gradient(ellipse 70% 50% at 20% 110%, rgba(220,38,38,0.18), transparent 65%),
            linear-gradient(135deg, #1a1a1a 0%, #0a0a0a 70%);
        overflow: hidden;
        min-height: calc(100vh - 64px);
    }
    .auth-aside::after {
        content: '';
        position: absolute; inset: 0;
        pointer-events: none;
        background-image:
            repeating-linear-gradient(135deg, rgba(255,255,255,0.018) 0 1px, transparent 1px 28px),
            repeating-linear-gradient(45deg, rgba(255,255,255,0.014) 0 1px, transparent 1px 24px);
    }
    .auth-aside-head {
        display: flex; align-items: center; gap: 12px;
        font-family: 'JetBrains Mono', 'SF Mono', monospace;
        font-size: 11px; letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--auth-text-3);
        z-index: 1;
    }
    .auth-aside-mark {
        width: 32px; height: 32px;
        border-radius: 8px;
        background: linear-gradient(135deg, #DC2626 0%, #991B1B 100%);
        display: flex; align-items: center; justify-content: center;
        font-family: 'Oswald', Impact, sans-serif;
        font-weight: 600;
        font-size: 14px; letter-spacing: 0.04em;
        color: #fff;
        box-shadow: 0 4px 12px rgba(220,38,38,0.3);
    }
    .auth-aside-quote {
        margin-top: auto;
        z-index: 1;
    }
    .auth-aside-quote .pull {
        font-family: 'Oswald', Impact, sans-serif;
        font-weight: 600;
        font-size: clamp(56px, 6vw, 92px);
        line-height: 0.95;
        letter-spacing: 0.005em;
        text-transform: uppercase;
        color: var(--auth-text);
    }
    .auth-aside-quote .pull em {
        font-style: normal;
        color: var(--auth-red);
    }
    .auth-aside-quote .pull u {
        text-decoration: underline;
        text-decoration-color: var(--auth-red);
        text-underline-offset: 8px;
        text-decoration-thickness: 6px;
    }
    .auth-aside-quote .pull-cite {
        margin-top: 22px;
        font-family: 'Fraunces', Georgia, serif;
        font-style: italic;
        color: var(--auth-gold);
        font-size: 17px;
        max-width: 36ch;
    }
    .auth-form-col {
        display: flex; flex-direction: column;
        justify-content: center;
        padding: 56px;
        max-width: 560px;
        width: 100%;
        margin: 0 auto;
    }
    .auth-hero { padding: 0; margin-bottom: 8px; }
    .auth-hero-title { font-size: 72px; line-height: 0.92; }
    .auth-form-stack { padding: 28px 0 0; }
    .auth-bottom-actions { padding: 0; margin-top: 36px; text-align: left; }
    .auth-no-access { padding: 12px 0 0; text-align: left; }

    .auth-sheet {
        left: 50%; right: auto;
        transform: translate(-50%, 110%);
        width: min(520px, calc(100% - 48px));
        border-radius: 28px;
        bottom: 32px;
        border: 0.5px solid rgba(255,255,255,0.12);
    }
    .auth-sheet.is-open { transform: translate(-50%, 0); }
}
@media (max-width: 1023px) {
    .auth-aside { display: none; }
    .auth-form-col { padding: 0; max-width: 100%; }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .auth-shell *, .auth-shell *::before, .auth-shell *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>

<div class="auth-shell">

    {{-- ── Desktop Aside (≥1024) ───────────────────────────────── --}}
    <aside class="auth-aside" aria-hidden="true">
        <div class="auth-aside-head">
            <div class="auth-aside-mark">W</div>
            <span>WellCore · Acceso · {{ now()->format('m.Y') }}</span>
        </div>
        <div class="auth-aside-quote">
            <div class="pull">
                Sin <em>milagros</em>,<br>
                <u>ciencia</u>.
            </div>
            <div class="pull-cite">
                Vuelve a tu plan. Tus métricas, tu coach y tu progreso te están esperando del otro lado.
            </div>
        </div>
    </aside>

    {{-- ── Form column ─────────────────────────────────────────── --}}
    <div class="auth-form-col">

        {{-- Hero --}}
        <section class="auth-hero">
            <div class="auth-hero-eyebrow">
                <span>{{ __('auth.login.eyebrow') }}</span>
            </div>
            <h1 class="auth-hero-title">{!! __('auth.login.hero_title_html') !!}</h1>
            <p class="auth-hero-sub">{{ __('auth.login.sub') }}</p>
        </section>

        {{-- Form --}}
        <form wire:submit="login" class="auth-form-stack" novalidate autocomplete="on">

            @if ($errorMessage)
                <div class="auth-error-banner" role="alert" aria-live="assertive">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>{{ $errorMessage }}</span>
                </div>
            @endif

            {{-- Credenciales card --}}
            <div>
                <div class="auth-group-caption">{{ __('auth.login.eyebrow') }}</div>
                <div class="auth-card" wire:key="cred-card">
                    {{-- Identity (email / username / client_code) --}}
                    <div class="auth-row {{ $errors->has('identity') ? 'has-error' : '' }}"
                         x-data="{ focused: false }"
                         :class="{ 'is-focused': focused }">
                        <label for="identity" class="auth-row-label">{{ __('auth.login.identity_label') }}</label>
                        <input
                            wire:model="identity"
                            id="identity"
                            type="text"
                            inputmode="email"
                            autocomplete="username"
                            placeholder="{{ __('auth.login.identity_placeholder') }}"
                            spellcheck="false"
                            autocapitalize="off"
                            autocorrect="off"
                            class="auth-row-input"
                            x-on:focus="focused = true"
                            x-on:blur="focused = false"
                        >
                    </div>
                    {{-- Password --}}
                    <div class="auth-row {{ $errors->has('password') ? 'has-error' : '' }}"
                         x-data="{ focused: false }"
                         :class="{ 'is-focused': focused }">
                        <label for="password" class="auth-row-label">{{ __('auth.login.password_label') }}</label>
                        <input
                            wire:model="password"
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="current-password"
                            placeholder="{{ __('auth.login.password_placeholder') }}"
                            class="auth-row-input"
                            x-on:focus="focused = true"
                            x-on:blur="focused = false"
                        >
                        <button
                            type="button"
                            class="row-eye"
                            :class="{ 'is-on': showPassword }"
                            x-on:click="showPassword = !showPassword"
                            :aria-label="showPassword ? '{{ __('auth.login.hide_password') }}' : '{{ __('auth.login.show_password') }}'"
                        >
                            <template x-if="!showPassword">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </template>
                            <template x-if="showPassword">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395"/>
                                    <path d="M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774"/>
                                    <line x1="3" y1="3" x2="21" y2="21"/>
                                </svg>
                            </template>
                        </button>
                    </div>
                </div>
                @error('identity')<p class="auth-row-error">{{ $message }}</p>@enderror
                @error('password')<p class="auth-row-error">{{ $message }}</p>@enderror
            </div>

            {{-- Remember + forgot --}}
            <div>
                <div class="auth-card">
                    <div class="auth-toggle-row">
                        <div>
                            <div class="label">{{ __('auth.login.remember') }}</div>
                            <div class="sub">{{ __('auth.login.remember_sub') }}</div>
                        </div>
                        {{-- iOS-style switch sincronizado a wire:model --}}
                        <button
                            type="button"
                            class="auth-switch"
                            :class="{ 'is-on': $wire.rememberMe }"
                            x-on:click="$wire.set('rememberMe', !$wire.rememberMe)"
                            role="switch"
                            :aria-checked="$wire.rememberMe ? 'true' : 'false'"
                            aria-label="{{ __('auth.login.remember') }}"
                        ></button>
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;margin-top:6px;">
                    <button type="button" class="auth-forgot-link" x-on:click="toggleSheet(true)">
                        {{ __('auth.login.forgot') }}
                    </button>
                </div>
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="auth-submit"
                :class="{ 'is-success': @js($loginSuccess) }"
                wire:loading.attr="disabled"
                wire:target="login"
            >
                <span wire:loading.remove wire:target="login">
                    @if ($loginSuccess)
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="margin-right:8px;display:inline-block;vertical-align:middle;">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        {{ __('auth.login.submit_success') }}
                    @else
                        {{ __('auth.login.submit') }}
                    @endif
                </span>
                <span wire:loading wire:target="login" style="display:inline-flex;align-items:center;gap:10px;">
                    <span class="spinner" aria-hidden="true"></span>
                    {{ __('auth.login.submit_loading') }}
                </span>
            </button>

            {{-- Trust strip --}}
            <div class="auth-trust-strip" aria-hidden="true">
                <span>{{ __('auth.login.trust.tls') }}</span>
                <span class="dot"></span>
                <span>{{ __('auth.login.trust.csrf') }}</span>
                <span class="dot"></span>
                <span>{{ __('auth.login.trust.wellcore') }}</span>
            </div>

            {{-- No-access WhatsApp escape hatch --}}
            <div class="auth-no-access">
                {{ __('auth.login.no_access_title') }}
                <a href="https://wa.me/{{ config('wellcore.whatsapp_silvia', '573000000000') }}?text={{ urlencode('Hola WellCore, no puedo acceder a mi cuenta.') }}"
                   target="_blank" rel="noopener">
                    {{ __('auth.login.no_access_whatsapp') }}
                </a>
            </div>
        </form>

        {{-- Bottom: signup --}}
        <footer class="auth-bottom-actions">
            {{ __('auth.login.no_account') }}
            <a href="{{ route('inscripcion') }}">{{ __('auth.login.signup_link') }} →</a>
        </footer>
    </div>
</div>

{{-- ── Bottom sheet: Recuperar acceso (embeds Livewire ForgotPassword) ── --}}
<div
    class="auth-sheet-backdrop"
    :class="{ 'is-open': sheetOpen }"
    aria-hidden="true"
    x-on:click="toggleSheet(false)"
></div>

<aside
    class="auth-sheet"
    :class="{ 'is-open': sheetOpen }"
    role="dialog"
    aria-modal="true"
    aria-labelledby="auth-sheet-title"
    x-on:keydown.escape.window="toggleSheet(false)"
>
    <div class="auth-sheet-handle" aria-hidden="true"></div>
    <div class="auth-sheet-header">
        <div class="auth-sheet-title" id="auth-sheet-title">{{ __('auth.login.forgot_sheet.title') }}</div>
        <button type="button" class="auth-sheet-cancel" x-on:click="toggleSheet(false)">
            {{ __('auth.login.forgot_sheet.cancel') }}
        </button>
    </div>
    <p class="auth-sheet-sub">{{ __('auth.login.forgot_sheet.sub') }}</p>

    {{-- ForgotPassword Livewire component embebido.
         Usa estética propia (Tailwind v1) — Dann puede unificar después.
         Wire-key fuerza re-render limpio cada vez que el sheet abre. --}}
    <div wire:key="forgot-sheet-livewire">
        <livewire:auth.forgot-password />
    </div>
</aside>

</div>
