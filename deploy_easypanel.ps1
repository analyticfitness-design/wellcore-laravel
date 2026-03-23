$baseUrl = 'https://panel.wellcorefitness.com'
$token = 'cmn24vvlw000h07mwazpia1vm'
$headers = @{
    'Authorization' = $token
    'Content-Type' = 'application/json'
}

# Re-ejecutar deploy-migrate con el fix de la migración
$deployContent = 'cd /var/www/html && git pull origin main && php artisan migrate --force && php artisan cache:clear && php artisan view:cache && echo DEPLOY_OK'
$scriptBody = "{`"json`":{`"projectName`":`"wellcorefitness`",`"serviceName`":`"wellcorefitness`",`"name`":`"deploy-migrate`",`"content`":`"$deployContent`"}}"

Write-Output "=== Re-ejecutando deploy-migrate ==="
$r = Invoke-WebRequest "$baseUrl/api/trpc/services.box.runScript" -Headers $headers -Method POST -Body $scriptBody -UseBasicParsing -TimeoutSec 30
Write-Output "Esperando 20s para que termine..."

Start-Sleep -Seconds 20

# Obtener resultado
$inputEncoded = [System.Uri]::EscapeDataString('{"json":{"projectName":"wellcorefitness","serviceName":"wellcorefitness","type":"script","limit":1}}')
$r2 = Invoke-WebRequest "$baseUrl/api/trpc/actions.listActions?input=$inputEncoded" -Headers $headers -Method GET -UseBasicParsing -TimeoutSec 15
$data2 = $r2.Content | ConvertFrom-Json
$actionId = $data2.result.data.json[0].id
Write-Output "Action ID: $actionId"

$inputId = [System.Uri]::EscapeDataString("{`"json`":{`"id`":`"$actionId`"}}")
$r3 = Invoke-WebRequest "$baseUrl/api/trpc/actions.getAction?input=$inputId" -Headers $headers -Method GET -UseBasicParsing -TimeoutSec 15
$data3 = $r3.Content | ConvertFrom-Json
Write-Output "Status: $($data3.result.data.json.status)"
Write-Output "Output:"
Write-Output $data3.result.data.json.log
