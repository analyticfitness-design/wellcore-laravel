# WellCore Laravel

## Project Overview
WellCore Fitness platform migrated from PHP vanilla to Laravel 13.
Fitness coaching platform serving LATAM market with personalized training, nutrition, and coaching.

## Tech Stack
- Laravel 13.1.1 + PHP 8.4
- Vue 3.5 + TypeScript + Pinia + Vue Router 4 (frontend SPA en migración)
- Livewire 3 + Alpine.js (componentes legacy, en proceso de migración a Vue 3)
- Tailwind CSS 4
- MySQL (shared DB: wellcore_fitness)
- Vite 8

## Key Architecture Decisions
- Strangler Fig pattern: both apps (vanilla PHP + Laravel) share the same MySQL database
- Custom WellCoreGuard reads auth_tokens table (compatible with vanilla app tokens)
- No Laravel migrations for existing tables — models map directly with $table
- Frontend migration: Livewire → Vue 3 SPA (resources/js/vue/), API via Laravel REST
- Vue 3 components in resources/js/vue/, consumed via Inertia.js or blade vue.blade.php

## Database
- Connection: MySQL wellcore_fitness (host=127.0.0.1, port=3306)
- 60+ tables in wellcore_fitness schema
- DO NOT create destructive migrations
- All models in app/Models/ with explicit $table declarations

## Auth
- Custom guard: WellCoreGuard (app/Auth/)
- Token-based: reads session → Bearer header → cookie
- Login creates tokens in auth_tokens table (64-char hex, 30-day expiry)
- Middleware: EnsureAuthenticated, RedirectIfAuthenticated

## Structure
- app/Livewire/Client/ — 11 client dashboard components
- app/Livewire/Admin/ — 4 admin dashboard components
- app/Livewire/Coach/ — 4 coach portal components
- app/Livewire/Rise/ — 4 RISE program components
- app/Livewire/Shop/ — 2 shop components
- app/Livewire/Auth/ — Login, ForgotPassword
- app/Services/ — AIService, WompiService, PushNotificationService
- app/Console/Commands/ — 3 scheduled commands (cron replacements)
- app/Enums/ — 8 PHP enums
- app/Models/ — 61 Eloquent models

## Commands
- composer install && npm install
- npm run dev (development with hot reload)
- npm run build (production assets)
- php artisan serve (or use Herd: wellcore-laravel.test)
- php artisan schedule:run (run scheduled jobs)
- php artisan wellcore:behavioral-triggers (test behavioral triggers)
- php artisan wellcore:weekly-summary (test weekly summaries)

## Design System
- Tokens in resources/css/app.css (@theme block)
- Colors: wc-bg, wc-bg-secondary, wc-bg-tertiary, wc-accent (#DC2626), wc-text, wc-border
- Fonts: font-display (Bebas Neue), font-sans (Inter), font-data (Barlow), font-mono (JetBrains Mono)
- Dark mode: .dark class on html, managed by Alpine.js + localStorage

## Laravel Agent Team — MANDATORY DELEGATION

IMPORTANT: This is a Laravel project. You MUST delegate tasks to the specialized agents below. Do NOT solve Laravel tasks yourself when a specialized agent exists. Always use the Agent tool to dispatch the correct agent.

### Dispatch Rules (ALWAYS apply in this project)

| Task type | MUST use agent |
|-----------|---------------|
| Architecture, DDD, patterns, service layers | **la-01-architect** |
| Eloquent, models, business logic, Actions, DTOs | **la-02-backend** |
| Vue 3 components, composables, Pinia, Vue Router, UI/UX, animaciones, charts, forms | **la-03-vue3** |
| Tailwind CSS, design tokens, dark mode, UI styling | **la-04-tailwind-ds** |
| Auth, CSRF, security, validation, middleware, permissions | **la-05-security** |
| Migrations, schema, queries, optimization, indexes | **la-06-database** |
| CI/CD, Docker, deploy, EasyPanel, server config | **la-07-devops** |
| i18n, translations, locale, multi-country | **la-08-i18n** |
| Payments, Wompi, Stripe, subscriptions | **la-09-payments** |
| Performance, caching, Redis, N+1, OPcache, profiling | **la-10-performance** |
| Claude API, AI features, SSE streaming, image analysis | **la-11-ai-architect** |
| WebSockets, real-time, Laravel Reverb, broadcasting | **la-12-realtime** |
| Analytics, dashboards, metrics, reporting | **la-13-analytics** |
| PHPUnit, Pest, Feature tests, component testing | **la-14-testing** |
| REST API, resources, API auth, OAuth2 | **la-15-api** |
| PWA, mobile optimization, responsive | **la-16-mobile** |
| SEO, meta tags, sitemap, schema markup | **la-17-seo-growth** |
| Multi-tenancy, SaaS, enterprise patterns | **la-18-enterprise** |

### How to dispatch
```
// Simple task → one agent
Agent(subagent_type="la-03-vue3", prompt="...")

// Complex task → parallel agents
Agent(la-02-backend) + Agent(la-03-vue3) simultaneously

// Explicit user request
"Usa la-09-payments para integrar Wompi"
"Lanza la-14-testing para crear tests"
```

## Rules
- NEVER modify C:\Users\GODSF\Herd\wellcorefitness (vanilla PHP app)
- NEVER create destructive database migrations
- All work in this project directory only
- Use WellCore design tokens for all UI
- Test credentials: daniel.esparza / RISE2026Admin!SuperPower (superadmin)
