# Rollback Plan — Cardio Module 2026-05-16

**Punto de retorno:** git tag `pre-cardio-module-2026-05-16` (commit `560446bde`).

## Si algo se rompe, sigue este orden:

### 1. Rollback del código frontend/backend (más probable)

```bash
# Local
cd C:\Users\GODSF\Herd\wellcore-laravel
git fetch origin
git checkout pre-cardio-module-2026-05-16
git checkout -b rollback-cardio-$(date +%Y%m%d-%H%M)
git push origin rollback-cardio-XXXX
# Luego en EasyPanel: cambiar el repo branch o force-push main al tag
# Más simple: revertir los commits del cardio module en main
git checkout main
git revert <commits-cardio>
git push origin main
```

En el container EasyPanel (panel.wellcorefitness.com → wellcorefitness/box → Scripts):

```bash
# Click "silvia-gitpull-load" → arrastra main al estado revertido
# O directo en consola bash:
cd /code && git fetch origin && git reset --hard pre-cardio-module-2026-05-16 && composer install --no-dev && php artisan config:cache
```

### 2. Rollback de la migración DB (solo si F0 corrió)

La migración F0 es **estrictamente aditiva**: añade columnas con DEFAULT NULL. Rollback:

```sql
-- Ejecutar en consola bash del container:
mysql -h wellcorefitness_wellcorefitness-mysql -u wellcorefitness -p wellcorefitness <<EOF
ALTER TABLE workout_logs
  DROP COLUMN cardio_metadata,
  DROP COLUMN rpe,
  DROP COLUMN rounds_completed,
  DROP COLUMN rounds_planned,
  DROP COLUMN cardio_type;
EOF
```

**Importante:** este rollback NO destruye datos existentes — las columnas que se dropean solo existirán DESPUÉS de F0. Si nunca corrió F0, no hay nada que dropear.

Para verificar antes:
```sql
SHOW COLUMNS FROM workout_logs LIKE 'cardio%';
SHOW COLUMNS FROM workout_logs LIKE 'rpe';
SHOW COLUMNS FROM workout_logs LIKE 'rounds_%';
```

### 3. Restaurar JSONs de planes (solo si UPDATE accidental)

Los 5 JSONs auditados están persistidos en el container:
- `/tmp/backup-cardio-2026-05-16/plan-115.json` (143 KB)
- `/tmp/backup-cardio-2026-05-16/plan-128.json` (170 KB)
- `/tmp/backup-cardio-2026-05-16/plan-156.json` (168 KB)
- `/tmp/backup-cardio-2026-05-16/plan-164.json` (88 KB)
- `/tmp/backup-cardio-2026-05-16/plan-188.json` (192 KB) — Lizeth

⚠️ **/tmp se borra al reiniciar el container.** Si esperás reiniciar antes de necesitar este backup, copialos a `/code/storage/app/backups/` o descargalos vía IDE.

Restauración:
```bash
cd /code && php -r "
\$j = json_decode(file_get_contents('/tmp/backup-cardio-2026-05-16/plan-188.json'), true);
\$content = json_encode(\$j['content'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
\$p = new PDO('mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness', 'wellcorefitness', 'fYCVgn4XZ7twq34');
\$st = \$p->prepare('UPDATE assigned_plans SET content=? WHERE id=?');
\$st->execute([\$content, 188]);
echo 'Restaurado plan 188\n';
"
```

### 4. Invalidar caches después del rollback

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# O por cliente:
php artisan tinker --execute="\Cache::forget('client_plan_v3_98'); \Cache::forget('wp:plan:98');"
```

## Inventario de backups

| Recurso | Ubicación | Tamaño |
|---|---|---|
| Código frontend | `backups/2026-05-16-pre-cardio-module/frontend/` | ~6 archivos + carpeta `components-workout/` |
| Código backend | `backups/2026-05-16-pre-cardio-module/backend/` | 5 archivos PHP (Models + Controllers) |
| Schema DB local | `backups/2026-05-16-pre-cardio-module/db-schema/schema-workout_logs.sql` | 1.4 KB |
| Schema DB container | `/tmp/backup-cardio-2026-05-16/schema-*.sql` | 4 tablas |
| JSONs planes container | `/tmp/backup-cardio-2026-05-16/plan-*.json` | 762 KB total |
| Git tag (remoto) | `pre-cardio-module-2026-05-16` en origin | inmutable |
| Resumen workout_logs | `/tmp/backup-cardio-2026-05-16/workout-logs-counts.txt` | counts pre-cambio |

## Validación post-rollback

Después de cualquier rollback ejecutar:

1. Login admin → impersonate Lizeth (`/admin/impersonate/98`) → `/client/plan` → verificar que Tab Entrenamiento, Nutrición y Suplementos renderizan idéntico al snapshot del 2026-05-16 14:00 UTC
2. Console F12 → cero errores rojos
3. Sábado HIIT — los 4 ejercicios (jumping jacks, salto cuerda, escaladores, sentadilla salto) renderizan con sus GIFs
4. Cardio LISS de Lunes/Miércoles/Viernes (escaladora 25 min) renderiza con `duracion_min` correcto
