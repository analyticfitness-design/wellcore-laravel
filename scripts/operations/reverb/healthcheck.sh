#!/bin/bash
# =============================================================================
# Health check para Reverb WebSocket — WellCore Production
# Ejecutar dentro del container de EasyPanel
# Uso: bash _reverb_healthcheck.sh [REVERB_APP_KEY]
# =============================================================================

APP_KEY="${1:-ac18b99742d512d6349b493ca72d7953}"

echo "=== WellCore Reverb Health Check ==="
echo ""

# 1. Verificar proceso corriendo
echo "[1/4] Verificando proceso Reverb..."
if [ -f /tmp/reverb.pid ]; then
    PID=$(cat /tmp/reverb.pid)
    if kill -0 "$PID" 2>/dev/null; then
        echo "  OK — Proceso activo (PID: $PID)"
    else
        echo "  FALLO — PID $PID no existe. Reverb no esta corriendo."
    fi
else
    # Buscar por nombre del proceso como fallback
    if pgrep -f "reverb:start" > /dev/null 2>&1; then
        echo "  OK — Proceso encontrado via pgrep"
    else
        echo "  FALLO — No se encontro proceso reverb:start"
    fi
fi

echo ""

# 2. Verificar HTTP local en puerto 8080
echo "[2/4] Verificando HTTP en localhost:8080..."
HTTP_CODE=$(curl -sf -o /dev/null -w "%{http_code}" --max-time 5 http://localhost:8080/ 2>/dev/null || echo "000")
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "400" ] || [ "$HTTP_CODE" = "426" ]; then
    echo "  OK — Responde HTTP $HTTP_CODE (426=Upgrade Required es normal para WS)"
else
    echo "  FALLO — HTTP $HTTP_CODE (esperado 200/400/426)"
fi

echo ""

# 3. Verificar app key endpoint
echo "[3/4] Verificando endpoint de app key..."
APP_STATUS=$(curl -sf -o /dev/null -w "%{http_code}" --max-time 5 "http://localhost:8080/app/${APP_KEY}" 2>/dev/null || echo "000")
if [ "$APP_STATUS" = "200" ] || [ "$APP_STATUS" = "400" ] || [ "$APP_STATUS" = "426" ]; then
    echo "  OK — Endpoint /app/ responde HTTP $APP_STATUS"
else
    echo "  ADVERTENCIA — HTTP $APP_STATUS para /app/${APP_KEY}"
fi

echo ""

# 4. Instrucciones para verificar WSS desde exterior
echo "[4/4] Verificacion WSS externa (desde browser o wscat):"
echo ""
echo "  Con wscat (npm install -g wscat):"
echo "  wscat -c 'wss://wellcorefitness.com/app/${APP_KEY}?protocol=7&client=js&version=8.0.0&flash=false'"
echo ""
echo "  Con DevTools browser:"
echo "  - Abrir wellcorefitness.com/client/chat"
echo "  - DevTools > Network > filtrar WS"
echo "  - Debe aparecer upgrade 101 Switching Protocols"
echo ""
echo "=== Fin Health Check ==="
