<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Inter', Arial, sans-serif; margin: 0; padding: 40px; background: #F5F5F7; color: #1A1A1A; }
        .invoice { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: #09090B; color: white; padding: 32px; text-align: center; }
        .header h1 { font-size: 24px; margin: 0; letter-spacing: 2px; }
        .header p { color: rgba(255,255,255,0.6); font-size: 12px; margin-top: 8px; }
        .body { padding: 32px; }
        .row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #EBEBEF; }
        .row:last-child { border-bottom: none; }
        .label { color: #6B7280; font-size: 14px; }
        .value { font-weight: 600; font-size: 14px; }
        .total-row { background: #F5F5F7; padding: 16px; border-radius: 8px; margin-top: 16px; }
        .total-row .value { color: #DC2626; font-size: 20px; }
        .footer { padding: 24px 32px; background: #F5F5F7; text-align: center; font-size: 11px; color: #6B7280; }
        .accent { color: #DC2626; }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="header">
            <h1>WELLCORE FITNESS</h1>
            <p>Comprobante de Pago</p>
        </div>
        <div class="body">
            <div class="row">
                <span class="label">Cliente</span>
                <span class="value">{{ $clientName }}</span>
            </div>
            <div class="row">
                <span class="label">Plan</span>
                <span class="value">{{ $planName }}</span>
            </div>
            <div class="row">
                <span class="label">Periodo</span>
                <span class="value">{{ $period }}</span>
            </div>
            <div class="row">
                <span class="label">Referencia</span>
                <span class="value">{{ $reference }}</span>
            </div>
            <div class="row">
                <span class="label">Fecha</span>
                <span class="value">{{ $date }}</span>
            </div>
            <div class="row">
                <span class="label">Metodo</span>
                <span class="value">{{ $paymentMethod }}</span>
            </div>
            @if(isset($discount) && $discount > 0)
            <div class="row">
                <span class="label">Descuento</span>
                <span class="value accent">-${{ number_format($discount, 0) }} COP</span>
            </div>
            @endif
            <div class="total-row">
                <div class="row" style="border: none;">
                    <span class="label" style="font-size: 16px; font-weight: 600;">Total Pagado</span>
                    <span class="value">${{ number_format($total, 0) }} COP</span>
                </div>
            </div>
        </div>
        <div class="footer">
            <p>WellCore Fitness SAS &middot; Bucaramanga, Santander, Colombia</p>
            <p>info@wellcorefitness.com &middot; wellcorefitness.com</p>
            <p style="margin-top: 8px;">Este documento es un comprobante de pago, no una factura electronica.</p>
        </div>
    </div>
</body>
</html>
