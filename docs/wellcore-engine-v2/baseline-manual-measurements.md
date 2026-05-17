# Baseline manual — mediciones del flujo "Claude Code humano"

> Capturado para comparar honestamente contra el motor v2 (doc 09 §7).
> Daniel completa esta tabla con las próximas 3 generaciones de planes manuales.

## Por qué importa

Sin baseline real medido, los "-70% cost" del doc 05 §8 son **estimaciones**. Para celebrar honestamente el ahorro del motor v2, necesitamos números exactos del flujo actual.

**Target del ahorro**: -70% mínimo. Realístico esperado por el doc 05 §8: -80% a -87%.

## Cómo completar cada generación

Después de cada plan que crees con el flujo manual ("Claude Code humano lee 27 MDs"), agrega una entrada acá con:

1. **Tokens reales** según el contador de Claude Code (visible en `/cost` o al final de la sesión)
2. **Tiempo cronometrado** (timer del celular, desde "intake completo" hasta "INSERT exitoso en `assigned_plans`")
3. **Resultado**: ¿pasó verify visual primer intento? ¿Daniel editó después?

## Generaciones

### Generación #1 — pendiente

```
Fecha:               YYYY-MM-DD
Cliente:             ______ (id ______)
Vertical:            ______ (entrenamiento / nutricion / combinado / suplementacion / habitos)
Tier comercial:      ______ (esencial / metodo / elite / entreno_solo / nutricion_solo / rise)

TOKENS (según Claude Code /cost):
  Input tokens:      _______
  Output tokens:     _______
  Cached input:      _______ (si aplicable)
  Modelo principal:  ______ (sonnet-4-6 / opus-4-7 / haiku-4-5)
  Cost USD:          $___.____

TIEMPO (cronómetro):
  Fase 0 intake completion:      ___ min
  Fase 1 lectura de MDs:         ___ min
  Fase 2 diseño del plan:        ___ min
  Fase 3 armar JSON:             ___ min
  Fase 4 INSERT (PDO / tinker):  ___ min
  Fase 5 verify visual MCP:      ___ min
  Fase 6+ notificaciones:        ___ min
  TOTAL:                         ___ min

RESULTADO:
  ¿Pasó verify visual al primer intento?    SÍ / NO
  Si NO, ¿qué tuviste que regenerar?         ________________
  ¿Daniel editó manualmente post-INSERT?    SÍ / NO
  Si SÍ, ¿qué editaste?                      ________________
  Lint findings retroactivos (si los hubiera): ________________
```

### Generación #2 — pendiente

(Mismo template — completar después de la siguiente generación manual)

### Generación #3 — pendiente

(Mismo template)

---

## Resumen calculado (rellenar cuando haya 3 mediciones)

| Métrica | Gen #1 | Gen #2 | Gen #3 | Promedio |
|---------|--------|--------|--------|----------|
| Input tokens | | | | |
| Output tokens | | | | |
| Cost USD | | | | |
| Tiempo total (min) | | | | |
| Verify 1er intento (SÍ/NO) | | | | __/3 |
| Daniel editó (SÍ/NO) | | | | __/3 |

**Baseline final**:
- Cost promedio por plan: **$_______**
- Tiempo promedio: **___ min**
- Verify pass rate: **__%**
- Editado post-gen rate: **__%**

## Cómo se compara contra motor v2 (Sprint 4+)

Cuando arranque Sprint 4, el dashboard `/engine-health` mostrará las mismas métricas para motor v2. La fórmula de reducción:

```
reducción_cost = (baseline_avg_cost - engine_v2_avg_cost) / baseline_avg_cost
reducción_tiempo = (baseline_avg_time - engine_v2_avg_time) / baseline_avg_time
```

**Target a celebrar honestamente**:
- Reducción cost > 70% ✅ valida la inversión del motor v2
- Reducción tiempo > 60% ✅ valida el ROI para Daniel
- Verify pass rate motor v2 ≥ baseline ✅ valida que no perdimos calidad

## Notas de Daniel mientras captura

(Espacio libre para anotar friction points del flujo manual que el motor v2 debería resolver)

- ...
- ...
- ...
