# Spec: Lista de Mercado — GroceryDrawer

**Fecha:** 2026-05-08  
**Estado:** Aprobado  
**Autor:** Daniel Esparza + Claude Code

---

## Resumen

Drawer deslizable en el Tab de Nutrición del cliente que genera automáticamente una lista de mercado semanal o por día basada en los alimentos del plan asignado. Sin nuevo endpoint ni backend — todo el procesamiento ocurre en el cliente con los datos ya disponibles en `nutritionPlan`.

---

## Alcance

### Incluye
- Botón "Lista de mercado" en `NutritionTab.vue` (debajo del PlanStrip o al final del header de comidas)
- Drawer bottom-sheet (mobile-first) con overlay
- Toggle Semanal / Por día
- Agrupación por categoría de alimento con emojis
- Sección "Guía de compra" al final con tips generales profesionales
- Composable `useGroceryList` que extrae y agrupa alimentos del nutritionPlan

### No incluye
- Exportar / copiar / descargar PDF
- Checklist de ítems (tachar al mercar)
- Endpoint de backend
- Cantidades acumuladas normalizadas (se listan por ocurrencia)

---

## Arquitectura

### Archivos nuevos

**`resources/js/vue/composables/useGroceryList.js`**
- Recibe `nutritionPlan` (ref/computed)
- Extrae todos los alimentos de `nutritionPlan.comidas` o `nutritionPlan.comidas_sugeridas`
- Agrupa por categoría usando keyword matching sobre el nombre del alimento
- Retorna `{ byCategory, byDay, dayLabels }`

**`resources/js/vue/components/plan/nutrition/GroceryDrawer.vue`**
- Props: `open: Boolean`, `nutritionPlan: Object`
- Emits: `close`
- Internamente usa `useGroceryList`
- Toggle semanal / por comida
- Sección "Guía de compra" fija al final

### Archivos modificados

**`resources/js/vue/components/plan/nutrition/NutritionTab.vue`**
- Import y montaje de `GroceryDrawer`
- Botón "🛒 Lista de mercado" en el header del plan de comidas
- `ref(false)` para controlar apertura del drawer

---

## Datos

### Estructura del nutritionPlan (existente)

```json
{
  "comidas": [
    {
      "nombre": "Desayuno",
      "hora": "7:00 AM",
      "alimentos": ["2 huevos revueltos", "1 taza avena", "1 banano"],
      "opciones": {
        "a": ["..."],
        "b": ["..."]
      }
    }
  ]
}
```

Los alimentos pueden ser:
- String: `"150g pollo a la plancha"`
- Objeto: `{ nombre: "Pollo", cantidad: "150g" }`
- Con opciones A/B: extraer ambas opciones

### Agrupación por categoría

El composable aplica keyword matching (case-insensitive, sin tildes) sobre el nombre del alimento:

| Categoría | Keywords |
|-----------|----------|
| 🥩 Proteínas | pollo, carne, res, atún, huevo, salmón, tilapia, cerdo, pavo, queso cottage, yogur griego, proteína |
| 🍚 Carbohidratos | arroz, avena, pan, papa, yuca, plátano, pasta, quinoa, tortilla, granola, arepa, maíz |
| 🥑 Grasas | aguacate, aceite, nuez, almendra, mantequilla, maní, coco, chía, linaza |
| 🥦 Verduras y frutas | brócoli, espinaca, lechuga, tomate, pepino, zanahoria, manzana, banano, fresa, naranja, mango |
| 🛒 Otros | todo lo que no matchee las anteriores |

### Estructura de retorno del composable

El plan de nutrición tiene **una estructura diaria** (un set de comidas para el día, no 7 días separados). La vista "Por comida" reemplaza a "Por día":

```js
{
  byCategory: [
    { label: 'Proteínas', emoji: '🥩', items: [{ name, qty, meal }] },
    ...
  ],
  byMeal: [
    { label: 'Desayuno', hora: '7:00 AM', items: [{ name, qty }] },
    { label: 'Almuerzo', hora: '12:00 PM', items: [...] },
    ...
  ]
}
```

---

## UI / UX

### Botón de entrada
- Posición: debajo del `DayTimeline`, antes de las `MealCard`s — dentro del header "Plan del día"
- Estilo: botón secundario con ícono de carrito (`ShoppingCart` de lucide-vue-next)
- Label: `Lista de mercado`

### GroceryDrawer

**Estructura del drawer (bottom-sheet mobile):**
```
┌─────────────────────────────┐
│ ── (drag handle)            │
│ 🛒 LISTA DE MERCADO         │ ← título + close button
│                             │
│ [Semanal] [Por día ▾]       │ ← toggle tabs
│  ┌────────────────────────┐ │
│  │ 🥩 PROTEÍNAS           │ │ ← sección colapsable
│  │  · 150g Pollo pechuga  │ │
│  │  · 2 Huevos            │ │
│  └────────────────────────┘ │
│  🍚 CARBOHIDRATOS           │
│  🥑 GRASAS                  │
│  🥦 VERDURAS Y FRUTAS       │
│  🛒 OTROS                   │
│                             │
│ ─────────────────────────── │
│ 📋 GUÍA DE COMPRA           │ ← sección fija al final
│  Tips profesionales         │
└─────────────────────────────┘
```

**Vista "Por comida":** chips horizontales scrollables con el nombre de cada comida (Desayuno / Almuerzo / Cena / etc.), muestra los alimentos de esa comida sin agrupar por categoría, en el orden en que aparecen. El toggle cambia de "Semanal" (todo agrupado por categoría) a "Por comida" (filtrado por comida individual).

**Guía de compra** — 6 tips genéricos fijos:
1. Compra proteínas frescas el mismo día que las consumas o máximo 2 días antes
2. Para carnes: elige cortes magros, color rojizo brillante, sin olor fuerte
3. Para vegetales: firmes, color vivo, sin manchas oscuras ni humedad excesiva
4. Lee etiquetas: el primer ingrediente es el que más hay; evita azúcares en los primeros 3
5. Prefiere productos con lista de ingredientes corta — menos procesado, mejor
6. Organiza tu carrito en el orden de las secciones de esta lista para un mercado más eficiente

### Animaciones
- Drawer: slide-up con backdrop fade, usando `<Transition>` Vue
- Tokens WellCore: `bg-wc-bg-secondary`, `border-wc-border`, `text-wc-accent`
- Estilo visual consistente con `MealCard` y `SwapPanel` existentes

---

## Manejo de edge cases

| Caso | Comportamiento |
|------|---------------|
| Plan sin comidas | Drawer muestra estado vacío: "Tu coach está preparando el plan" |
| Alimento como objeto sin nombre | Se omite silenciosamente |
| Plan con opciones A/B | Se incluyen ambas opciones en la lista con label "(Opción A)" / "(Opción B)" |
| `nutritionPlan` null | Botón no se muestra; guard en el composable |

---

## Testing

No se escriben tests unitarios para esta feature (pure UI, sin lógica de negocio crítica). Se valida manualmente:
- [ ] Drawer abre y cierra correctamente
- [ ] Toggle semanal/por comida funciona
- [ ] Categorías agrupan correctamente con planes reales
- [ ] Vista por día muestra comidas en orden correcto
- [ ] Guía de compra visible al final
- [ ] Funciona con plan null (botón oculto)
- [ ] Responsive en mobile y desktop
