# Runbook — Comprobantes de Pago Externo

**Feature:** payment_proofs  
**Tabla:** `payment_proofs`  
**Disco privado:** `storage/app/private/payment_proofs/`

---

## Flujo normal

1. **Coach** sube comprobante en `/coach/profile` → pestaña Comprobantes
2. Sistema valida MIME real (finfo), hash SHA-256 anti-duplicado, throttle 10/día
3. Admin recibe notificación in-app + email → revisa en `/admin/payment-proofs`
4. Admin **aprueba** → `ApprovePaymentProofAction`: crea `CoachInvitation` + `Payment` + `Client` + `ClientCoach(source='payment_proof')` → email acceso al cliente
5. Admin **rechaza** → `RejectPaymentProofAction`: notificación + email al coach con razón
6. Cron diario → `wellcore:expire-payment-proofs` marca como `expirado` los `pendiente` con `expires_at < now()`

---

## Comandos Artisan

```bash
# Ver comprobantes pendientes en producción
php artisan tinker --execute="PaymentProof::where('status','pendiente')->count()"

# Expirar manualmente (--dry-run para preview)
php artisan wellcore:expire-payment-proofs

# Limpiar payments Wompi huérfanos (sin relación con proof)
php artisan wellcore:cleanup-pending-payments --dry-run
php artisan wellcore:cleanup-pending-payments --hours=48
```

---

## Estados del comprobante

| Estado | Descripción |
|--------|-------------|
| `pendiente` | Subido, esperando revisión admin |
| `aprobado` | Cliente creado/activado, coach asignado |
| `rechazado` | Admin rechazó, coach puede re-subir |
| `expirado` | Sin revisión después de 7 días |

---

## Errores comunes

| Error | Causa | Solución |
|-------|-------|---------|
| `DUPLICATE_PENDING` (409) | Coach ya subió proof para ese email y está pendiente | Admin debe revisar el proof pendiente |
| `DUPLICATE_FILE` (409) | Mismo archivo SHA-256 ya en sistema (pendiente) | Verificar si fue subido por otro coach |
| `FILE_NOT_FOUND` (404) en `/file` | Archivo borrado del disco | Pedir re-subida al coach |
| `BadMethodCallException` en approve/reject | Proof ya procesado (estado != pendiente) | Idempotent — ignorar |

---

## Migraciones aplicadas

```
2026_04_27_000000_create_payment_proofs_table  — tabla base
2026_04_27_000001_add_file_hash_to_payment_proofs — columna file_hash CHAR(64)
```

---

## Seguridad

- **Disco privado** — archivos NO accesibles por URL directa
- **URL firmada** — HMAC token en Cache, 5 min, single-use (ver `AdminPaymentProofViewController`)
- **MIME real** — `finfo_file()` verifica bytes reales (anti-spoofing)
- **Rate limit** — 10/día por coach (superadmin/jefe sin límite)
- **IDOR** — `PaymentProofPolicy` bloquea acceso cross-coach

---

## Compliance / retención

Comprobantes pueden contener PII (nombres, números de cuenta bancaria).  
Considerar purge automático de `file_path` (no el registro) 90 días post-aprobación.
