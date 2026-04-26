# Pasos deploy Reverb en EasyPanel — WellCore Production

> Fecha: 2026-04-26
> Dominio: wellcorefitness.com
> Container path: /code
> IMPORTANTE: Usar consola "Run" con UID exacto. NO usar boton Rebuild Docker.

---

## Paso 1: Instalar laravel/reverb (REQUERIDO — no esta en composer.json)

En la consola del container de EasyPanel:

```bash
cd /code
composer require laravel/reverb
php artisan reverb:install
```

`reverb:install` publicara `config/reverb.php` y preguntara si publicar el Service Provider.
Confirmar con `yes`.

---

## Paso 2: Configurar variables de entorno

Copiar los siguientes valores al `.env` de produccion en EasyPanel
(seccion Environment del servicio):

```
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=wellcore-prod
REVERB_APP_KEY=ac18b99742d512d6349b493ca72d7953
REVERB_APP_SECRET=ab8564d4673436b153f2dff2ad4e813e82ddd5f7b117e20a5d16a8a09c244eec
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=https

VITE_REVERB_APP_KEY=ac18b99742d512d6349b493ca72d7953
VITE_REVERB_HOST=wellcorefitness.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

> Los valores VITE_* ya estan hardcodeados (no usar sintaxis ${VAR} en EasyPanel
> porque el container no expande variables entre si en el panel de env).

---

## Paso 3: Limpiar caches de configuracion

Despues de agregar las variables:

```bash
cd /code
php artisan config:clear
php artisan config:cache
```

---

## Paso 4: Iniciar daemon Reverb

En la consola del container:

```bash
cd /code
php artisan reverb:start --host=0.0.0.0 --port=8080
```

Para correrlo en background y guardar PID:

```bash
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > /tmp/reverb.log 2>&1 &
echo $! > /tmp/reverb.pid
echo "Reverb PID: $(cat /tmp/reverb.pid)"
```

Verificar que arranco:

```bash
curl -I http://localhost:8080/
```

Debe responder 200, 400 o 426 (Upgrade Required). Cualquiera de estos indica que Reverb escucha.

---

## Paso 5: Configurar Nginx proxy WebSocket

En EasyPanel, ir a la configuracion Nginx del dominio wellcorefitness.com.
Agregar el contenido del archivo `_reverb_nginx.conf` dentro del `server {}` block existente.

Despues de guardar, recargar Nginx:

```bash
nginx -s reload
# o si nginx esta en otro container:
# docker exec <nginx-container> nginx -s reload
```

---

## Paso 6: Verificar upgrade WebSocket

Desde el browser:

1. Abrir `https://wellcorefitness.com/client/chat`
2. DevTools > Network > filtrar por "WS"
3. Debe aparecer una conexion con status `101 Switching Protocols`
4. En la columna de protocolo debe decir `websocket`

Con wscat (si esta disponible):

```bash
wscat -c 'wss://wellcorefitness.com/app/ac18b99742d512d6349b493ca72d7953?protocol=7&client=js&version=8.0.0&flash=false'
```

Debe conectar y mostrar el mensaje de bienvenida de Pusher/Reverb:
`{"event":"pusher:connection_established",...}`

---

## Paso 7: Configurar reinicio automatico del proceso

EasyPanel no gestiona procesos internos del container. Opciones:

**Opcion A — Supervisor** (recomendada si esta disponible en el container):

```ini
[program:reverb]
command=php /code/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/code
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
stderr_logfile=/tmp/reverb.err.log
stdout_logfile=/tmp/reverb.out.log
```

```bash
supervisorctl reread && supervisorctl update && supervisorctl start reverb
```

**Opcion B — Script de arranque en Dockerfile** (si se puede modificar el imagen):

```dockerfile
CMD ["/bin/sh", "-c", "php artisan reverb:start --host=0.0.0.0 --port=8080 & php-fpm"]
```

**Opcion C — Cron dentro del container** (fallback minimo):

```bash
# Agregar a crontab del container
* * * * * pgrep -f "reverb:start" || nohup php /code/artisan reverb:start --host=0.0.0.0 --port=8080 >> /tmp/reverb.log 2>&1 &
```

---

## Resumen de archivos de soporte

| Archivo | Uso |
|---------|-----|
| `_reverb_env_values.txt` | Valores .env listos para pegar en EasyPanel |
| `_reverb_start.sh` | Script para iniciar el daemon |
| `_reverb_nginx.conf` | Snippet Nginx para reverse proxy WSS |
| `_reverb_healthcheck.sh` | Verificar que Reverb responde |
| `_reverb_deploy_steps.md` | Este archivo — guia completa |

---

## Troubleshooting

| Sintoma | Causa probable | Solucion |
|---------|---------------|----------|
| `curl localhost:8080` timeout | Reverb no esta corriendo | Ejecutar paso 4 |
| WS connection refused en browser | Nginx no proxy el path /app/ | Verificar paso 5 |
| `pusher:error` en consola JS | APP_KEY no coincide | Verificar VITE_REVERB_APP_KEY = REVERB_APP_KEY |
| Canal privado 403 | BROADCAST_CONNECTION no es reverb | Verificar variable .env paso 2 |
| Reverb se cae al poco tiempo | Sin proceso supervisor | Implementar paso 7 opcion A |
