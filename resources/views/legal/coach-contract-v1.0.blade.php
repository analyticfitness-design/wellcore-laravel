<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vinculación a WellCore Fitness como Coach · Acuerdo de Alianza Comercial</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700;800&family=Barlow:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --wc-bg: #0A0A0A;
    --wc-bg-secondary: #141414;
    --wc-bg-tertiary: #1F1F1F;
    --wc-bg-card: #181818;
    --wc-accent: #DC2626;
    --wc-accent-dark: #991B1B;
    --wc-accent-light: #EF4444;
    --wc-text: #F5F5F5;
    --wc-text-muted: #A3A3A3;
    --wc-text-dim: #737373;
    --wc-border: #262626;
    --wc-success: #10B981;
    --wc-warning: #F59E0B;
    --wc-danger: #EF4444;
    --font-display: 'Bebas Neue', sans-serif;
    --font-sans: 'Inter', sans-serif;
    --font-data: 'Barlow', sans-serif;
    --font-mono: 'JetBrains Mono', monospace;
}

* { margin: 0; padding: 0; box-sizing: border-box; }
html { scroll-behavior: smooth; }

body {
    background: var(--wc-bg);
    color: var(--wc-text);
    font-family: var(--font-sans);
    line-height: 1.7;
    font-size: 16px;
    -webkit-font-smoothing: antialiased;
}

/* HERO */
.hero {
    background: linear-gradient(135deg, var(--wc-bg) 0%, var(--wc-bg-secondary) 100%);
    border-bottom: 1px solid var(--wc-border);
    padding: 100px 40px 70px;
    position: relative;
    overflow: hidden;
}

.hero::before {
    content: "";
    position: absolute;
    top: -30%;
    right: -10%;
    width: 700px;
    height: 700px;
    background: radial-gradient(circle, var(--wc-accent) 0%, transparent 70%);
    opacity: 0.1;
    filter: blur(80px);
}

.hero-inner {
    max-width: 1100px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
}

.eyebrow {
    display: inline-block;
    font-family: var(--font-data);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.2em;
    color: var(--wc-accent);
    background: rgba(220, 38, 38, 0.1);
    padding: 8px 16px;
    border: 1px solid rgba(220, 38, 38, 0.3);
    border-radius: 4px;
    text-transform: uppercase;
    margin-bottom: 24px;
}

.hero h1 {
    font-family: var(--font-display);
    font-size: clamp(44px, 7vw, 82px);
    line-height: 0.95;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    margin-bottom: 20px;
}

.hero h1 .accent { color: var(--wc-accent); display: block; }

.hero-subtitle {
    font-size: 20px;
    color: var(--wc-text-muted);
    max-width: 720px;
    line-height: 1.5;
    margin-bottom: 36px;
    font-weight: 300;
}

.hero-welcome {
    background: rgba(220, 38, 38, 0.08);
    border-left: 3px solid var(--wc-accent);
    padding: 20px 28px;
    border-radius: 4px;
    max-width: 720px;
    font-size: 17px;
    line-height: 1.6;
}

/* CONTAINER */
.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 80px 40px;
}

.section {
    margin-bottom: 72px;
    scroll-margin-top: 40px;
}

.section-number {
    font-family: var(--font-data);
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.25em;
    color: var(--wc-accent);
    margin-bottom: 12px;
    text-transform: uppercase;
}

h2 {
    font-family: var(--font-display);
    font-size: clamp(36px, 5vw, 56px);
    line-height: 1;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    margin-bottom: 20px;
}

h2 .accent { color: var(--wc-accent); }

.section-lead {
    font-size: 18px;
    color: var(--wc-text-muted);
    line-height: 1.6;
    margin-bottom: 32px;
    max-width: 750px;
}

h3 {
    font-family: var(--font-display);
    font-size: 28px;
    line-height: 1.1;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--wc-text);
    margin-top: 36px;
    margin-bottom: 16px;
}

h4 {
    font-family: var(--font-sans);
    font-size: 17px;
    font-weight: 700;
    color: var(--wc-text);
    margin-top: 24px;
    margin-bottom: 12px;
}

p { margin-bottom: 14px; color: var(--wc-text); }

strong { color: var(--wc-text); font-weight: 700; }

em { color: var(--wc-accent-light); font-style: normal; font-weight: 500; }

/* TWO COL CARDS */
.two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin: 24px 0;
}

.role-card {
    background: var(--wc-bg-card);
    border: 1px solid var(--wc-border);
    border-radius: 10px;
    padding: 28px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s;
}

.role-card:hover {
    border-color: var(--wc-accent);
    transform: translateY(-2px);
}

.role-card.wellcore { border-top: 3px solid var(--wc-accent); }
.role-card.coach { border-top: 3px solid #FFB800; }

.role-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--wc-border);
}

.role-badge {
    font-family: var(--font-display);
    font-size: 20px;
    letter-spacing: 0.1em;
    padding: 4px 14px;
    border-radius: 4px;
}

.role-card.wellcore .role-badge {
    background: var(--wc-accent);
    color: white;
}

.role-card.coach .role-badge {
    background: #FFB800;
    color: var(--wc-bg);
}

.role-title {
    font-family: var(--font-display);
    font-size: 28px;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    line-height: 1;
}

.role-list {
    list-style: none;
}

.role-list li {
    position: relative;
    padding: 10px 0 10px 28px;
    color: var(--wc-text);
    font-size: 15px;
    line-height: 1.5;
    border-bottom: 1px dashed var(--wc-border);
}

.role-list li:last-child { border-bottom: none; }

.role-list li::before {
    content: "";
    position: absolute;
    left: 0;
    top: 16px;
    width: 8px;
    height: 8px;
    background: var(--wc-accent);
    border-radius: 50%;
}

.role-card.coach .role-list li::before {
    background: #FFB800;
}

/* SI / NO CARDS */
.yes-no-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin: 24px 0;
}

.yn-card {
    background: var(--wc-bg-card);
    border: 1px solid var(--wc-border);
    border-radius: 10px;
    padding: 28px;
    position: relative;
}

.yn-card.yes { border-top: 3px solid var(--wc-success); }
.yn-card.no { border-top: 3px solid var(--wc-danger); }

.yn-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.yn-icon {
    font-family: var(--font-display);
    font-size: 40px;
    line-height: 1;
}

.yn-card.yes .yn-icon { color: var(--wc-success); }
.yn-card.no .yn-icon { color: var(--wc-danger); }

.yn-title {
    font-family: var(--font-display);
    font-size: 26px;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

.yn-list {
    list-style: none;
}

.yn-list li {
    position: relative;
    padding: 10px 0 10px 32px;
    font-size: 15px;
    line-height: 1.5;
    border-bottom: 1px dashed var(--wc-border);
}

.yn-list li:last-child { border-bottom: none; }

.yn-card.yes .yn-list li::before {
    content: "✓";
    position: absolute;
    left: 0;
    top: 10px;
    color: var(--wc-success);
    font-weight: 700;
    font-size: 18px;
}

.yn-card.no .yn-list li::before {
    content: "✕";
    position: absolute;
    left: 0;
    top: 10px;
    color: var(--wc-danger);
    font-weight: 700;
    font-size: 18px;
}

/* CALLOUT */
.callout {
    background: var(--wc-bg-card);
    border: 1px solid var(--wc-border);
    border-left: 4px solid var(--wc-accent);
    border-radius: 6px;
    padding: 22px 28px;
    margin: 24px 0;
}

.callout.warning {
    border-left-color: var(--wc-warning);
    background: rgba(245, 158, 11, 0.04);
}

.callout.danger {
    border-left-color: var(--wc-danger);
    background: rgba(239, 68, 68, 0.04);
}

.callout.success {
    border-left-color: var(--wc-success);
    background: rgba(16, 185, 129, 0.04);
}

.callout-title {
    font-family: var(--font-data);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--wc-accent);
    margin-bottom: 8px;
}

.callout.warning .callout-title { color: var(--wc-warning); }
.callout.danger .callout-title { color: var(--wc-danger); }
.callout.success .callout-title { color: var(--wc-success); }

/* REVENUE SPLIT */
.revenue-split {
    display: grid;
    grid-template-columns: 3fr 2fr;
    gap: 0;
    margin: 32px 0;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--wc-border);
}

.split-block {
    padding: 36px;
    text-align: center;
    position: relative;
}

.split-coach {
    background: linear-gradient(135deg, #FFB800 0%, #F59E0B 100%);
    color: var(--wc-bg);
}

.split-wellcore {
    background: linear-gradient(135deg, var(--wc-accent) 0%, var(--wc-accent-dark) 100%);
    color: white;
}

.split-percent {
    font-family: var(--font-display);
    font-size: 80px;
    line-height: 1;
    letter-spacing: 0.02em;
    font-weight: 400;
}

.split-label {
    font-family: var(--font-data);
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    margin-top: 8px;
    opacity: 0.95;
}

.split-desc {
    font-size: 14px;
    margin-top: 6px;
    opacity: 0.85;
}

/* PROHIBITED LIST */
.prohibited-list {
    background: rgba(239, 68, 68, 0.03);
    border: 1px solid var(--wc-danger);
    border-radius: 10px;
    padding: 28px 32px;
    list-style: none;
    margin: 24px 0;
    counter-reset: prohibited;
}

.prohibited-list li {
    padding: 14px 0;
    padding-left: 48px;
    position: relative;
    border-bottom: 1px solid rgba(239, 68, 68, 0.15);
    font-size: 15px;
    line-height: 1.5;
    counter-increment: prohibited;
}

.prohibited-list li:last-child { border-bottom: none; }

.prohibited-list li::before {
    content: counter(prohibited, decimal-leading-zero);
    position: absolute;
    left: 0;
    top: 14px;
    font-family: var(--font-mono);
    font-size: 13px;
    font-weight: 700;
    color: var(--wc-danger);
    background: rgba(239, 68, 68, 0.1);
    padding: 2px 8px;
    border-radius: 3px;
}

/* PLATFORM SHOWCASE */
.platform-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin: 24px 0;
}

.platform-group {
    background: var(--wc-bg-card);
    border: 1px solid var(--wc-border);
    border-radius: 8px;
    padding: 20px;
}

.platform-group-title {
    font-family: var(--font-data);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.2em;
    color: var(--wc-accent);
    text-transform: uppercase;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--wc-border);
}

.platform-group ul {
    list-style: none;
}

.platform-group li {
    padding: 6px 0;
    font-size: 14px;
    color: var(--wc-text-muted);
    padding-left: 16px;
    position: relative;
}

.platform-group li::before {
    content: "▸";
    position: absolute;
    left: 0;
    color: var(--wc-accent);
}

/* CLAUSE */
.clause {
    background: linear-gradient(135deg, var(--wc-bg-card), var(--wc-bg-tertiary));
    border: 2px solid var(--wc-accent);
    border-radius: 12px;
    padding: 36px;
    margin: 32px 0;
    position: relative;
}

.clause::before {
    content: "CLÁUSULA";
    position: absolute;
    top: -12px;
    left: 24px;
    background: var(--wc-bg);
    padding: 4px 14px;
    font-family: var(--font-data);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.25em;
    color: var(--wc-accent);
    border: 1px solid var(--wc-accent);
    border-radius: 4px;
}

.clause h3 {
    margin-top: 0;
    color: var(--wc-accent);
}

/* CTA FINAL */
.cta-final {
    background: linear-gradient(135deg, var(--wc-bg-card), var(--wc-bg-secondary));
    border: 2px solid var(--wc-accent);
    border-radius: 16px;
    padding: 56px 40px;
    text-align: center;
    margin: 40px 0;
}

.cta-final h3 {
    font-family: var(--font-display);
    font-size: 40px;
    letter-spacing: 0.03em;
    color: var(--wc-accent);
    margin-bottom: 16px;
}

.cta-final p {
    font-size: 18px;
    color: var(--wc-text-muted);
    max-width: 600px;
    margin: 0 auto 12px;
}

/* FOOTER */
.footer {
    border-top: 1px solid var(--wc-border);
    padding: 48px 40px;
    text-align: center;
    background: var(--wc-bg-secondary);
}

.footer-logo {
    font-family: var(--font-display);
    font-size: 36px;
    letter-spacing: 0.1em;
    margin-bottom: 10px;
}

.footer-logo .accent { color: var(--wc-accent); }

.footer-meta {
    font-family: var(--font-data);
    font-size: 12px;
    color: var(--wc-text-dim);
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

/* RESPONSIVE — TABLET */
@media (max-width: 960px) {
    .container { padding: 64px 28px; }
    .hero { padding: 80px 28px 64px; }
    .section { margin-bottom: 60px; }
    .two-col, .yes-no-grid { grid-template-columns: 1fr; gap: 20px; }
    .platform-grid { grid-template-columns: 1fr 1fr; }
    .revenue-split { grid-template-columns: 1fr; }
    h2 { font-size: 44px; }
    h3 { font-size: 26px; }
}

/* RESPONSIVE — MOBILE */
@media (max-width: 640px) {
    body { font-size: 16px; line-height: 1.65; }
    .hero { padding: 56px 20px 48px; }
    .hero h1 { font-size: clamp(46px, 12vw, 64px); line-height: 1; }
    .hero-subtitle { font-size: 17px; line-height: 1.55; margin-bottom: 28px; }
    .hero-welcome { font-size: 16px; padding: 18px 20px; line-height: 1.6; }
    .eyebrow { font-size: 11px; padding: 7px 14px; margin-bottom: 20px; }
    .container { padding: 52px 20px; }
    .section { margin-bottom: 52px; }
    .section-number { font-size: 12px; }
    h2 { font-size: 36px; line-height: 1.05; margin-bottom: 16px; }
    h2 .accent { display: inline; }
    h3 { font-size: 23px; margin-top: 28px; margin-bottom: 14px; }
    h4 { font-size: 16px; }
    .section-lead { font-size: 16px; line-height: 1.55; margin-bottom: 26px; }
    .role-card, .yn-card { padding: 22px 20px; border-radius: 10px; }
    .role-header { gap: 12px; margin-bottom: 16px; padding-bottom: 14px; }
    .role-badge { font-size: 18px; padding: 4px 12px; }
    .role-title, .yn-title { font-size: 24px; }
    .role-list li { font-size: 15px; padding: 10px 0 10px 24px; line-height: 1.5; }
    .role-list li::before { top: 16px; width: 7px; height: 7px; }
    .yn-list li { font-size: 15px; padding: 10px 0 10px 28px; line-height: 1.5; }
    .yn-list li::before { font-size: 16px; }
    .yn-icon { font-size: 34px; }
    .callout { padding: 18px 20px; margin: 20px 0; }
    .callout p { font-size: 15.5px; line-height: 1.55; }
    .callout-title { font-size: 11px; }
    .platform-grid { grid-template-columns: 1fr; gap: 10px; }
    .platform-group { padding: 18px 20px; }
    .platform-group-title { font-size: 11px; }
    .platform-group li { font-size: 14.5px; padding: 7px 0 7px 16px; }
    .split-block { padding: 30px 20px; }
    .split-percent { font-size: 68px; }
    .split-label { font-size: 12px; letter-spacing: 0.2em; }
    .split-desc { font-size: 13.5px; }
    .prohibited-list { padding: 20px 22px; }
    .prohibited-list li { font-size: 15px; padding: 12px 0 12px 44px; line-height: 1.5; }
    .prohibited-list li::before { font-size: 12px; top: 13px; padding: 2px 6px; }
    .clause { padding: 28px 22px; }
    .clause::before { font-size: 10px; padding: 3px 12px; letter-spacing: 0.2em; }
    .cta-final { padding: 40px 24px; border-radius: 12px; }
    .cta-final h3 { font-size: 30px; }
    .cta-final p { font-size: 16px; line-height: 1.55; }
    .footer { padding: 36px 20px; }
    .footer-logo { font-size: 30px; margin-bottom: 8px; }
    .footer-meta { font-size: 11px; line-height: 1.7; }
}

/* RESPONSIVE — SMALL MOBILE */
@media (max-width: 380px) {
    .hero h1 { font-size: 48px; }
    .hero-subtitle { font-size: 16px; }
    .hero-welcome { font-size: 15.5px; }
    h2 { font-size: 32px; }
    h3 { font-size: 22px; }
    .split-percent { font-size: 56px; }
    .role-title, .yn-title { font-size: 22px; }
    .cta-final h3 { font-size: 26px; }
}

/* PRINT */
@media print {
    body { background: white; color: #111; font-size: 11pt; }
    .hero { background: none; border-bottom: 2px solid #DC2626; padding: 20pt; page-break-after: always; }
    .hero h1, h2, h3, .role-title, .yn-title { color: #111; }
    .hero h1 .accent, .eyebrow, .section-number, .cta-final h3, .footer-logo .accent { color: #DC2626; }
    .container { padding: 20pt; }
    .section { page-break-inside: avoid; margin-bottom: 30pt; }
    .role-card, .yn-card, .callout, .platform-group, .clause, .cta-final {
        break-inside: avoid; background: #fafafa; border: 1px solid #ddd; color: #111;
    }
    .prohibited-list { background: #fff5f5; }
    .split-coach, .split-wellcore { background: #f4f4f4; color: #111; }
}
</style>
</head>
<body>

<!-- HERO -->
<section class="hero">
    <div class="hero-inner">
        <span class="eyebrow">Acuerdo de Alianza Comercial · 2026</span>
        <h1>Vinculación<br><span class="accent">Coach WellCore</span></h1>
        <p class="hero-subtitle">Servicio, condiciones y requisitos para ser parte del equipo de coaches profesionales de WellCore Fitness.</p>
        <div class="hero-welcome">
            <strong>Hola, coach.</strong><br>
            Este documento es tu guía completa sobre cómo funciona la alianza entre tú y WellCore Fitness. Lo hicimos claro y directo: qué hacemos nosotros, qué haces tú, qué esperas de la plataforma y qué esperamos de ti. Léelo con calma — es la base de una relación de trabajo sana y rentable para ambos.
        </div>
    </div>
</section>

<!-- MAIN CONTAINER -->
<div class="container">

<!-- 1. QUÉ HACE WELLCORE Y QUÉ HACES TÚ -->
<section class="section">
    <div class="section-number">— 01 —</div>
    <h2>¿Qué hace <span class="accent">WellCore</span> y qué haces tú?</h2>
    <p class="section-lead">Para que todo fluya, cada quien tiene un rol claro. Aquí te explicamos quién se encarga de qué, sin letra pequeña.</p>

    <div class="two-col">
        <div class="role-card wellcore">
            <div class="role-header">
                <span class="role-badge">WELLCORE</span>
                <div class="role-title">Lo hacemos nosotros</div>
            </div>
            <p style="font-size:14px; color:var(--wc-text-muted); margin-bottom:16px; font-style:italic;">Somos tu programador, diseñador, asistente y respaldo operativo.</p>
            <ul class="role-list">
                <li><strong>Creación del plan personalizado</strong> de cada cliente por nuestro equipo técnico, a partir del ticket que tú armas</li>
                <li><strong>Acompañamiento para cerrar la venta</strong> con nuevos clientes (te damos argumentos, materiales y respaldo)</li>
                <li><strong>Onboarding del cliente</strong> a la plataforma: le explicamos cómo usar su dashboard, check-ins y funciones</li>
                <li><strong>Fichas de marketing para tus redes</strong>: campañas segmentadas según el buyer persona de cada servicio</li>
                <li><strong>Dirección semanal sobre el manejo del asesorado</strong>, para que tu acompañamiento sea óptimo y progresivo</li>
                <li><strong>Entrega alternativa</strong>: si tu cliente prefiere PDFs o documentos descargables en vez de la plataforma, nosotros los preparamos</li>
            </ul>
        </div>

        <div class="role-card coach">
            <div class="role-header">
                <span class="role-badge">COACH</span>
                <div class="role-title">Lo haces tú</div>
            </div>
            <p style="font-size:14px; color:var(--wc-text-muted); margin-bottom:16px; font-style:italic;">Tú eres la cara visible, el vendedor y el acompañante directo.</p>
            <ul class="role-list">
                <li><strong>Promocionar, promover y vender la asesoría</strong> WellCore en tus redes y canales</li>
                <li><strong>Presentarle la plataforma al cliente</strong> y enseñarle las funcionalidades básicas</li>
                <li><strong>Entrevista inicial</strong>: recolectar la información necesaria para armar el ticket de plan</li>
                <li><strong>Seguimiento semanal vía WhatsApp</strong> con cada uno de tus asesorados</li>
                <li><strong>Agendar videollamadas</strong> con tu asesorado si el plan lo incluye: <em>primeros 2-3 días y a los 15 días</em>, para chequeo, ajustes y revisión</li>
                <li><strong>Enviar los tickets de ajuste</strong> desde la plataforma cuando haya modificaciones al plan</li>
            </ul>
        </div>
    </div>

    <div class="callout">
        <div class="callout-title">En una frase</div>
        <p style="margin:0"><strong>Tú vendes y acompañas. Nosotros construimos y respaldamos.</strong> Juntos entregamos un servicio que ningún coach puede dar solo.</p>
    </div>
</section>

<!-- 2. QUÉ SÍ Y QUÉ NO TE DA WELLCORE -->
<section class="section">
    <div class="section-number">— 02 —</div>
    <h2>Qué <span class="accent">SÍ</span> y qué NO te da WellCore</h2>
    <p class="section-lead">Honestidad total desde el primer día. Para que sepas exactamente qué esperar de esta alianza.</p>

    <div class="yes-no-grid">
        <div class="yn-card yes">
            <div class="yn-header">
                <span class="yn-icon">✓</span>
                <div class="yn-title">Lo que SÍ te damos</div>
            </div>
            <ul class="yn-list">
                <li><strong>Plataforma</strong> completa para gestionar clientes, planes y comunicación</li>
                <li><strong>Asistencia</strong> directa del equipo cuando la necesites</li>
                <li><strong>Respaldo</strong> operativo, técnico y de marca</li>
                <li><strong>Formas parte del equipo</strong> WellCore (no eres un contratista suelto)</li>
                <li><strong>Garantía de ajustes</strong> en los planes cuando el cliente lo requiera</li>
                <li><strong>Calidad basada en ciencia</strong>: terminología y métodos validados</li>
                <li><strong>Precios fijos establecidos</strong> — te llegan claros y pactados</li>
            </ul>
        </div>

        <div class="yn-card no">
            <div class="yn-header">
                <span class="yn-icon">✕</span>
                <div class="yn-title">Lo que NO te damos</div>
            </div>
            <ul class="yn-list">
                <li><strong>No damos clases</strong> de entrenamiento o nutrición</li>
                <li><strong>No damos certificaciones</strong> profesionales</li>
                <li><strong>No se reembolsan</strong> servicios ya prestados</li>
                <li><strong>No damos contratación</strong> laboral formal (eres aliado, no empleado)</li>
                <li><strong>No buscamos clientes por ti</strong> — tu captación es tuya</li>
                <li><strong>No administramos tus redes sociales</strong> con objetivo de venta</li>
                <li><strong>No respaldamos la mala praxis</strong> del coach con sus asesorados</li>
            </ul>
        </div>
    </div>
</section>

<!-- 3. CONDICIONES Y REQUISITOS -->
<section class="section">
    <div class="section-number">— 03 —</div>
    <h2>Condiciones y <span class="accent">requisitos</span> para ser coach WellCore</h2>
    <p class="section-lead">Dos cosas no negociables: cómo trabajas y cómo te relacionas con el equipo.</p>

    <h3>1. Alineación metodológica</h3>
    <p>Las metodologías de trabajo del coach deben <strong>alinearse o desarrollarse con las metodologías de WellCore Fitness</strong>. Esto no significa que pierdas tu estilo — significa que el fondo científico, la terminología y los métodos son comunes para todo el equipo. Así garantizamos calidad uniforme a cada cliente, venga de quien venga.</p>

    <h3>2. Respeto y ética con la comunidad WellCore</h3>
    <p>Como miembro del equipo, <strong>eres imagen de WellCore</strong>. Por eso:</p>

    <div class="callout danger">
        <div class="callout-title">Cero tolerancia</div>
        <p style="margin:0">No se irrespeta, ataca, calumnia, denigra ni critica públicamente a otros miembros del equipo. Hacerlo es una falta grave a la integridad de la empresa y <strong>causa definitiva de terminación</strong> de tu vinculación.</p>
    </div>

    <p><strong>¿Qué pasa con los clientes si se termina tu vinculación?</strong><br>
    Tus clientes se mantienen activos <strong>hasta su fecha de corte</strong>, y luego serán desactivados de la plataforma. Respetamos siempre al cliente final.</p>
</section>

<!-- 4. DEBERES Y RESPONSABILIDADES -->
<section class="section">
    <div class="section-number">— 04 —</div>
    <h2>Deberes y <span class="accent">responsabilidades</span></h2>
    <p class="section-lead">Lo que cada parte se compromete a cumplir, sin ambigüedad.</p>

    <div class="two-col">
        <div class="role-card coach">
            <div class="role-header">
                <span class="role-badge">COACH</span>
                <div class="role-title">Tus deberes</div>
            </div>
            <ul class="role-list">
                <li><strong>Vender la asesoría</strong> usando la plataforma WellCore, con los <em>precios fijados</em> por el equipo y previamente pactados</li>
                <li><strong>Adjuntar comprobantes</strong> de pago, transacción o confirmación de Wompi del cliente nuevo o de cada renovación</li>
                <li><strong>Hacer seguimiento</strong> y contacto con tus asesorados vía WhatsApp de forma consistente</li>
            </ul>
        </div>

        <div class="role-card wellcore">
            <div class="role-header">
                <span class="role-badge">WELLCORE</span>
                <div class="role-title">Nuestros deberes</div>
            </div>
            <ul class="role-list">
                <li><strong>Proteger la confidencialidad</strong> de los datos de los asesorados vinculados a la plataforma por medio de cualquier coach</li>
                <li><strong>Mantener la plataforma</strong> operativa, con soporte técnico y actualizaciones</li>
                <li><strong>Generar los planes personalizados</strong> en tiempo razonable a partir de tu ticket</li>
                <li><strong>Entregar el material de marketing</strong> pactado y la dirección semanal</li>
            </ul>
        </div>
    </div>
</section>

<!-- 5. POLÍTICAS DE PAGO -->
<section class="section">
    <div class="section-number">— 05 —</div>
    <h2>Políticas de <span class="accent">pago</span></h2>
    <p class="section-lead">Transparencia total sobre cómo se reparte el dinero y cómo lo documentamos.</p>

    <h3>Acuerdo de ganancias</h3>
    <div class="revenue-split">
        <div class="split-block split-coach">
            <div class="split-percent">60%</div>
            <div class="split-label">COACH</div>
            <div class="split-desc">Tu trabajo, tu captación, tu seguimiento</div>
        </div>
        <div class="split-block split-wellcore">
            <div class="split-percent">40%</div>
            <div class="split-label">WELLCORE</div>
            <div class="split-desc">Plataforma, soporte, planes, marca</div>
        </div>
    </div>

    <h3>Precios fijos</h3>
    <p>Los precios los fija WellCore Fitness. Tú debes <strong>vender el servicio exactamente a ese precio</strong>. No hay descuentos por tu cuenta, ni incrementos para tu conveniencia.</p>

    <div class="callout danger">
        <div class="callout-title">Importante</div>
        <p style="margin:0">Si se descubre que un coach vende el plan a un <strong>precio mayor al fijado</strong> para su beneficio personal, esto es <strong>causa directa de terminación</strong> de la vinculación del coach y sus asesorados con WellCore Fitness.</p>
    </div>

    <h3>Comprobantes de pago</h3>
    <p>Para cada nuevo cliente o renovación, <strong>debes adjuntar el comprobante de pago</strong> (transferencia, confirmación Wompi, recibo, etc.).</p>

    <div class="callout warning">
        <div class="callout-title">Sin comprobante, no hay servicio</div>
        <p style="margin:0">Sin este comprobante <strong>no se podrá crear el usuario del cliente ni solicitar el ticket de nueva asesoría</strong>. Es el primer paso del flujo de onboarding.</p>
    </div>
</section>

<!-- 6. CONFIDENCIALIDAD Y FINALIZACIÓN -->
<section class="section">
    <div class="section-number">— 06 —</div>
    <h2>Confidencialidad y <span class="accent">cláusula de finalización</span></h2>
    <p class="section-lead">Relaciones sanas requieren límites claros. Estas son las conductas que rompen el acuerdo de forma inmediata.</p>

    <div class="clause">
        <h3>Finalización unilateral del acuerdo</h3>
        <p>WellCore Fitness se reserva el derecho de terminar <strong>unilateralmente</strong> la vinculación con un coach cuando se incurra en cualquiera de las siguientes malas prácticas:</p>

        <ol class="prohibited-list">
            <li><strong>Alteración de los precios del servicio</strong> (cobrar más o menos del precio fijado)</li>
            <li><strong>Captar clientes de otros miembros del equipo</strong> con métodos NO éticos: hablar mal de otro coach, sugestionar al cliente, sobornar con beneficios extras, etc.</li>
            <li><strong>Reiteradas faltas o fallas</strong> en el cumplimiento de tus funciones y deberes con tus clientes</li>
            <li><strong>Irrespeto con los miembros del equipo</strong> (coaches, admin, staff operativo)</li>
            <li><strong>No cumplir con el workflow</strong> de trabajo estipulado por WellCore para la vinculación de nuevos clientes</li>
            <li><strong>Compartir o filtrar datos confidenciales</strong> de los clientes con terceros ajenos a WellCore</li>
            <li><strong>Dar asesorías paralelas fuera de la plataforma</strong> cobrándole al mismo cliente WellCore por fuera (doble facturación)</li>
            <li><strong>Plagio o uso indebido</strong> del contenido, metodologías o materiales de marca de WellCore y de otros coaches del equipo</li>
            <li><strong>Manipulación de testimonios o resultados</strong> de clientes con fines de marketing engañoso</li>
            <li><strong>Conducta inapropiada con clientes</strong>: acoso, discriminación, trato irrespetuoso o cruce de límites profesionales</li>
            <li><strong>Crear alianzas comerciales paralelas</strong> usando tu acceso a clientes WellCore para derivar a otros servicios sin autorización</li>
            <li><strong>Uso de la marca WellCore</strong> en contextos, productos o servicios no autorizados por la empresa</li>
        </ol>
    </div>

    <div class="callout success">
        <div class="callout-title">Confidencialidad mutua</div>
        <p style="margin:0">Esta confidencialidad es recíproca: tus datos, tus ventas y tu información como coach también están protegidos por WellCore frente a terceros y frente a otros coaches del equipo.</p>
    </div>
</section>

<!-- 7. BONUS: TU DASHBOARD -->
<section class="section">
    <div class="section-number">— 07 —</div>
    <h2>Tu <span class="accent">dashboard</span> de coach</h2>
    <p class="section-lead">Un vistazo rápido a las herramientas que vas a tener desde el día 1. Son 14 secciones agrupadas en 4 áreas.</p>

    <div class="platform-grid">
        <div class="platform-group">
            <div class="platform-group-title">Coach</div>
            <ul>
                <li>Dashboard · resumen de tu equipo</li>
                <li>Clientes · listado de asignados</li>
                <li>Kanban · flujo de trabajo visual</li>
            </ul>
        </div>
        <div class="platform-group">
            <div class="platform-group-title">Comunicación</div>
            <ul>
                <li>Check-ins · revisa semanales</li>
                <li>Mensajes · bandeja directa</li>
                <li>Broadcast · envío masivo</li>
            </ul>
        </div>
        <div class="platform-group">
            <div class="platform-group-title">Seguimiento</div>
            <ul>
                <li>Tickets de Plan · tu flujo principal</li>
                <li>Planes · gestión y asignación</li>
                <li>Analítica · tu performance</li>
                <li>Notas · privadas por cliente</li>
            </ul>
        </div>
        <div class="platform-group">
            <div class="platform-group-title">Mi Espacio</div>
            <ul>
                <li>Perfil · tu bio pública</li>
                <li>Mi Marca · personalización</li>
                <li>Herramientas · tus features</li>
                <li>Recursos · material de apoyo</li>
            </ul>
        </div>
    </div>

    <div class="callout">
        <div class="callout-title">Flujo clave: Tickets de Plan</div>
        <p style="margin-bottom:8px"><strong>No vas a escribir el plan tú.</strong> Tu trabajo es armar un ticket bien hecho con la info del cliente (7 pasos, 8 si es Elite): datos generales, entrenamiento, nutrición, hábitos, suplementación y ciclo hormonal si aplica.</p>
            <p style="margin:0">Ese ticket lo toma nuestro equipo técnico, lo procesa y genera un plan profesional personalizado. Tú lo revisas, lo validas con el cliente y lo entregas. <em>Así todos ganamos calidad y tiempo.</em></p>
    </div>
</section>

<!-- 8. ASPECTOS LEGALES -->
<section class="section">
    <div class="section-number">— 08 —</div>
    <h2>Aspectos <span class="accent">legales</span></h2>
    <p class="section-lead">Marco normativo colombiano que rige esta alianza. Léelo: protege a ambas partes.</p>

    <h3>8.1 Naturaleza no laboral</h3>
    <p>Esta es una <strong>alianza comercial</strong>, no un contrato de trabajo. No existe subordinación, horario fijo ni exclusividad. El coach actúa como aliado independiente. En consecuencia, no aplican las disposiciones del Código Sustantivo del Trabajo (CST art. 23) ni se generan prestaciones sociales, parafiscales o seguridad social a cargo de WellCore Fitness.</p>

    <h3>8.2 Régimen tributario</h3>
    <p>El coach declara ser responsable de su propio régimen tributario ante la DIAN (RUT, retención en la fuente, IVA cuando aplique, régimen simple si está inscrito). WellCore expedirá los soportes de pago correspondientes a las comisiones del coach según las disposiciones tributarias vigentes.</p>

    <h3>8.3 Tratamiento de datos personales (Habeas Data)</h3>
    <p>De conformidad con la Ley 1581 de 2012 y el Decreto 1377 de 2013, el coach <strong>autoriza expresamente</strong> a WellCore Fitness para el tratamiento de sus datos personales con fines operativos, comerciales y de seguridad. Adicionalmente, el coach se compromete a respetar la política de privacidad de los datos de los clientes a los que tenga acceso a través de la plataforma, y a no usarlos por fuera del propósito de la asesoría.</p>

    <h3>8.4 Comercio electrónico y validez del documento digital</h3>
    <p>De acuerdo con la Ley 527 de 1999 (artículos 5 a 7), este documento aceptado en formato digital tiene la <strong>misma fuerza vinculante</strong> que un documento físico firmado. La aceptación electrónica constituye un mensaje de datos válido para todos los efectos legales.</p>

    <h3>8.5 Propiedad intelectual</h3>
    <p>WellCore Fitness conserva todos los derechos sobre los planes, metodologías, marca, materiales gráficos y contenidos de la plataforma (Ley 23 de 1982, Decisión Andina 351). El coach recibe una <strong>licencia de uso no exclusiva, no transferible y revocable</strong> exclusivamente para vender y entregar los servicios de WellCore dentro de la plataforma. Cualquier uso fuera de este alcance requiere autorización escrita.</p>

    <h3>8.6 Confidencialidad reforzada</h3>
    <p>Las obligaciones de confidencialidad descritas en la sección 06 se mantienen vigentes durante <strong>dos (2) años posteriores</strong> a la terminación de la alianza, por cualquier causa. El incumplimiento da derecho a WellCore Fitness a iniciar las acciones civiles y penales que correspondan según la legislación colombiana.</p>

    <h3>8.7 Resolución de conflictos</h3>
    <p>Cualquier controversia derivada de esta alianza se intentará resolver primero por <strong>conciliación</strong> ante un centro autorizado de la cámara de comercio del domicilio principal de WellCore Fitness. Si la conciliación fracasa, las partes acudirán a la jurisdicción ordinaria colombiana o, si así lo acuerdan por escrito, a un tribunal de arbitramento conforme a la Ley 1563 de 2012.</p>
</section>

<!-- 9. CLÁUSULA DE ACEPTACIÓN DIGITAL -->
<section class="section">
    <div class="section-number">— 09 —</div>
    <h2>Aceptación <span class="accent">digital y evidencia</span></h2>
    <p class="section-lead">Cómo se registra tu aceptación y qué evidencia queda guardada.</p>

    <div class="clause">
        <h3>Manifestación expresa de aceptación</h3>
        <p>Al hacer clic en <strong>"Aceptar y continuar"</strong> dentro de la plataforma WellCore, el coach manifiesta de forma libre, expresa e informada su aceptación íntegra del presente acuerdo en todas sus secciones.</p>
        <p>Para que la aceptación sea válida, el sistema verifica que el coach haya recorrido el documento hasta el final ("scroll completo"). Si rechaza el acuerdo o cierra la sesión sin aceptarlo, su cuenta de coach quedará <strong>inactiva</strong> y no podrá acceder al portal hasta que aceptar o contactar al administrador.</p>

        <h3>Evidencia almacenada</h3>
        <p>WellCore Fitness conserva, por cada aceptación o rechazo, los siguientes datos como prueba electrónica conforme a la Ley 527 de 1999:</p>
        <ul class="role-list">
            <li>Identificador del coach y versión del acuerdo aceptado</li>
            <li>Marca temporal UTC de la aceptación o rechazo</li>
            <li>Dirección IP desde la cual se realizó la acción</li>
            <li>User-agent del navegador / dispositivo</li>
            <li>Hash SHA-256 del HTML del documento al momento de la aceptación</li>
            <li>Indicador de scroll completo del documento</li>
        </ul>
    </div>
</section>

<!-- CTA FINAL -->
<section class="section">
    <div class="cta-final">
        <h3>¿Listo para sumarte al equipo?</h3>
        <p>Si hasta aquí todo resuena contigo, entonces esta alianza tiene futuro. En la reunión resolvemos dudas específicas, revisamos tu perfil y arrancamos con el onboarding formal.</p>
        <p style="margin-top:24px; font-family: var(--font-data); font-size:13px; letter-spacing:0.2em; text-transform:uppercase; color:var(--wc-accent);">Bienvenido a WellCore Fitness</p>
    </div>
</section>

</div>

<div id="contract-end-sentinel" style="height:1px;width:100%;"></div>

<!-- Scroll-end notifier for the gate iframe (postMessage to parent) -->
<script>
    (function () {
        var sentinel = document.getElementById('contract-end-sentinel');
        if (!sentinel) return;
        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        try {
                            window.parent.postMessage({ type: 'wc-contract-end' }, '*');
                        } catch (e) { /* parent may be cross-origin in dev — ignore */ }
                        io.disconnect();
                    }
                });
            }, { threshold: 0.1 });
            io.observe(sentinel);
        } else {
            // Older browsers fallback: post on document scroll-bottom (fires once)
            function onScroll() {
                if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 4) {
                    try { window.parent.postMessage({ type: 'wc-contract-end' }, '*'); } catch (e) {}
                    window.removeEventListener('scroll', onScroll);
                }
            }
            window.addEventListener('scroll', onScroll, { passive: true });
        }
    })();
</script>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-logo">WELL<span class="accent">CORE</span> FITNESS</div>
    <div class="footer-meta">
        Acuerdo de alianza comercial · Coaches · Versión 1.0 · 25 de abril de 2026<br>
        Documento vinculante · Uso exclusivo del equipo WellCore
    </div>
</footer>

</body>
</html>
