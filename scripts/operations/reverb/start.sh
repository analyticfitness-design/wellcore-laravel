#!/bin/bash
# =============================================================================
# Reverb daemon start script — ejecutar dentro del container de EasyPanel
# Uso: bash _reverb_start.sh
# Nota: En EasyPanel usar la consola "Run" con UID exacto del servicio,
#       NO usar el boton Rebuild Docker (tumba el container).
# =============================================================================

set -e

APP_DIR="/code"

if [ ! -d "$APP_DIR" ]; then
    echo "ERROR: No se encontro el directorio $APP_DIR"
    exit 1
fi

cd "$APP_DIR"

# Verificar que laravel/reverb esta instalado
if ! grep -q "laravel/reverb" composer.json 2>/dev/null; then
    echo "ERROR: laravel/reverb no esta en composer.json"
    echo "Ejecutar primero: composer require laravel/reverb && php artisan reverb:install"
    exit 1
fi

# Matar instancia previa si existe
if [ -f /tmp/reverb.pid ]; then
    OLD_PID=$(cat /tmp/reverb.pid)
    if kill -0 "$OLD_PID" 2>/dev/null; then
        echo "Terminando instancia previa de Reverb (PID: $OLD_PID)..."
        kill "$OLD_PID"
        sleep 2
    fi
    rm -f /tmp/reverb.pid
fi

echo "Iniciando Reverb en 0.0.0.0:8080..."
php artisan reverb:start --host=0.0.0.0 --port=8080 &
REVERB_PID=$!
echo "Reverb PID: $REVERB_PID"
echo "$REVERB_PID" > /tmp/reverb.pid

sleep 2

# Verificar que arranco
if kill -0 "$REVERB_PID" 2>/dev/null; then
    echo "Reverb OK — escuchando en 0.0.0.0:8080"
    echo "Verificar con: curl -I http://localhost:8080/"
else
    echo "ERROR: Reverb no arranco. Revisar logs con: php artisan reverb:start --host=0.0.0.0 --port=8080 --debug"
    exit 1
fi
