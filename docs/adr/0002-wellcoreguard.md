# 0002 — WellCoreGuard: Sistema de autenticación propio

Se decidió NO usar Laravel Sanctum, Passport, ni el guard nativo de Laravel Auth. La razón: la app vanilla PHP ya tenía un sistema de tokens en la tabla `auth_tokens` que los clientes usaban activamente en producción. Migrar a Sanctum requería invalidar todas las sesiones activas y sincronizar dos sistemas de tokens durante la transición.

WellCoreGuard es un guard custom que:
1. Lee el Bearer token del header `Authorization` o de la cookie `wellcore_token`
2. Busca en `auth_tokens` (token hex de 64 chars, campo `expires_at`, 30 días de vida)
3. Retorna el modelo `Client`, `Coach`, o `Admin` según la tabla `token_type`

## Consecuencias vinculantes

- **NUNCA usar `actingAs()`** en tests de Feature — WellCoreGuard ignora el estado del guard de Laravel; autenticar siempre vía tabla `auth_tokens` con Bearer token
- **NUNCA asumir que `Auth::user()` retorna el modelo correcto** sin verificar el guard específico (`client`, `coach`, `admin`)
- Los middlewares de auth son: `wellcore.client`, `wellcore.coach`, `wellcore.admin` — no `auth:sanctum`
- Al crear tests, usar el helper `actingAsClient()` que genera un token real en `auth_tokens`
- `actingAsClient()` retorna `Client`, no `TestCase` — no chainear `->getJson()` directamente

## Por qué no Sanctum en el futuro

Migrar a Sanctum requeriría: invalidar sesiones activas de todos los usuarios, actualizar la app vanilla PHP para leer tokens de la tabla `personal_access_tokens` de Sanctum, y coordinar el cutover sin downtime. El costo supera el beneficio actual.
