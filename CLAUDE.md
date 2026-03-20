# WellCore Laravel

## Project Overview
WellCore Fitness platform migrated from PHP vanilla to Laravel 13.
Fitness coaching platform serving LATAM market with personalized training, nutrition, and coaching.

## Tech Stack
- Laravel 13.1.1 + PHP 8.4
- Livewire 3 + Alpine.js
- Tailwind CSS 4
- MySQL (shared DB: wellcore_fitness)
- Vite 8

## Key Architecture Decisions
- Strangler Fig pattern: both apps (vanilla PHP + Laravel) share the same MySQL database
- Custom WellCoreGuard reads auth_tokens table (compatible with vanilla app tokens)
- No Laravel migrations for existing tables — models map directly with $table
- Livewire for all interactivity (no separate SPA/API)

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

## Laravel Agent Team (wellcore-agents plugin)
Use these specialized agents for complex tasks in this project:
- **la-01-architect** — Architecture decisions, service layers, design patterns
- **la-02-backend** — Eloquent advanced, business logic, form requests, enums
- **la-03-livewire-blade** — Livewire components, Blade views, Alpine.js integration
- **la-04-tailwind-ds** — Tailwind CSS 4, WellCore design tokens, dark mode
- **la-05-security** — Auth, CSRF, OWASP, input validation, middleware
- **la-06-database** — Migrations, schema design, query optimization
- **la-10-performance** — Caching, N+1 prevention, Redis, OPcache
- **la-11-ai-architect** — Claude API integration, SSE streaming, image analysis

## Rules
- NEVER modify C:\Users\GODSF\Herd\wellcorefitness (vanilla PHP app)
- NEVER create destructive database migrations
- All work in this project directory only
- Use WellCore design tokens for all UI
- Test credentials: daniel.esparza / RISE2026Admin!SuperPower (superadmin)
