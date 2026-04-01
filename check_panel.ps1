try {
    $r = Invoke-WebRequest 'https://panel.wellcorefitness.com' -TimeoutSec 15 -UseBasicParsing
    Write-Output "Status: $($r.StatusCode)"
} catch {
    Write-Output "Error: $($_.Exception.Message)"
}
