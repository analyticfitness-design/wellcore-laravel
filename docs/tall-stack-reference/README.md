# TALL Stack Reference Docs

Documentación de referencia extraída del [Laravel TALL Claude AI Configs](https://github.com/tott/laravel-tall-claude-ai-configs).

**Nota:** Estos archivos **NO** son agentes dispatchables (los WellCore ya tiene en `la-01` a `la-18` definidos en CLAUDE.md). Son **referencias de conocimiento** sobre patrones TALL stack que se pueden consultar cuando se trabaje con código legacy Livewire/Alpine.

## Archivos

| Archivo | Cuándo consultar |
|---------|-----------------|
| `tall-database-architect.md` | Patrones de schema/Eloquent para Laravel. Complementa `la-06-database`. |
| `tall-devops-specialist.md` | Sail, deployment, CI/CD Laravel. Complementa `la-07-devops`. |
| `tall-performance-specialist.md` | Optimización Laravel + Livewire. Complementa `la-10-performance`. |
| `tall-security-specialist.md` | OWASP + Laravel security. Complementa `la-05-security`. |
| `tall-testing-specialist.md` | Pest/PHPUnit/Dusk patterns. Complementa `la-14-testing`. |

## Agente dispatchable

Para trabajo en código **Livewire/Alpine legacy** (los 21 componentes activos en `app/Livewire/`), usar el agente:

```
.claude/agents/tall-livewire-specialist.md
```

Que es el único genuinamente complementario a los agentes `la-XX` (que asumen Vue 3 migrado).
