$baseUrl = 'https://panel.wellcorefitness.com'
$token = 'cmn24vvlw000h07mwazpia1vm'
$headers = @{
    'Authorization' = $token
    'Content-Type' = 'application/json'
}

# Get the actual error message (beginning of exception)
$scriptBody = '{"json":{"projectName":"wellcorefitness","serviceName":"wellcorefitness","name":"get-logs2","content":"cd /var/www/html && grep -A 5 \"production.ERROR\" storage/logs/laravel.log | tail -30"}}'

$r = Invoke-WebRequest "$baseUrl/api/trpc/services.box.runScript" -Headers $headers -Method POST -Body $scriptBody -UseBasicParsing -TimeoutSec 30
Write-Output "Waiting 10s..."
Start-Sleep -Seconds 10

$inputEncoded = [System.Uri]::EscapeDataString('{"json":{"projectName":"wellcorefitness","serviceName":"wellcorefitness","type":"script","limit":1}}')
$r2 = Invoke-WebRequest "$baseUrl/api/trpc/actions.listActions?input=$inputEncoded" -Headers $headers -Method GET -UseBasicParsing -TimeoutSec 15
$data2 = $r2.Content | ConvertFrom-Json
$actionId = $data2.result.data.json[0].id

$inputId = [System.Uri]::EscapeDataString('{"json":{"id":"' + $actionId + '"}}')
$r3 = Invoke-WebRequest "$baseUrl/api/trpc/actions.getAction?input=$inputId" -Headers $headers -Method GET -UseBasicParsing -TimeoutSec 15
$data3 = $r3.Content | ConvertFrom-Json
Write-Output $data3.result.data.json.log
