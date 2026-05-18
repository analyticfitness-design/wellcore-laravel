# 0003 — Solo migraciones aditivas: prohibición de cambios destructivos al schema

La base de datos `wellcore_fitness` es compartida entre Laravel y la app vanilla PHP. Cualquier cambio destructivo al schema (DROP TABLE, DROP COLUMN, ALTER COLUMN para cambiar tipos, renombrar tablas o columnas) puede romper la app vanilla PHP inmediatamente en producción — sin rollback automático y sin ventana de mantenimiento.

## Regla

Solo se permiten migraciones **aditivas**:
- `CREATE TABLE` (tablas nuevas)
- `ADD COLUMN` (columnas nuevas, siempre con DEFAULT o nullable)
- Crear índices nuevos (`CREATE INDEX`)
- Insertar datos de seed en tablas de configuración

## Prohibido sin análisis explícito y luz verde

- `DROP TABLE` — eliminar tabla
- `DROP COLUMN` — eliminar columna
- `ALTER COLUMN` que cambie tipo de dato (ejemplo real: `clients.plan` es ENUM en prod, no VARCHAR)
- Renombrar tablas o columnas
- Cambiar constraints existentes (NOT NULL, UNIQUE, FK)

## Por qué esto importa

**Incidente real (2026-04)**: Se asumió que `clients.plan` era VARCHAR (como en el entorno de test). En producción es ENUM. El hotfix requirió un ALTER manual en producción. Lección: siempre verificar el schema real en el container antes de asumir tipos.

## Cómo verificar el schema real

```bash
# En EasyPanel terminal (consola del container):
mysql -u wellcorefitness -p wellcore_fitness -e "DESCRIBE clients;"
```

Nunca asumir que el schema local o de test coincide con producción.
