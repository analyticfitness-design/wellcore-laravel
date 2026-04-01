try {
    $r = Invoke-WebRequest 'https://wellcorefitness-wellcorefitness.v9xcpt.easypanel.host' -TimeoutSec 20 -UseBasicParsing -MaximumRedirection 5
    Write-Output "Status: $($r.StatusCode)"
    Write-Output "Length: $($r.Content.Length)"
    # Show first 500 chars
    Write-Output $r.Content.Substring(0, [Math]::Min(500, $r.Content.Length))
} catch {
    Write-Output "Error: $($_.Exception.Message)"
}
