$srcDir = "C:\Users\GODSF\Herd\wellcore-laravel\docs\stories-menopausia"
$lightDst = "E:\WELLCORE FITNESS PLATAFORMA\Coaches\VENTAS DE PLANES CARDS\STORIES MUJER\_LIGHT\03-MENOPAUSIA"
$darkDst  = "E:\WELLCORE FITNESS PLATAFORMA\Coaches\VENTAS DE PLANES CARDS\STORIES MUJER\_DARK\03-MENOPAUSIA"

if (-not (Test-Path $lightDst)) { New-Item -ItemType Directory -Path $lightDst -Force | Out-Null }
if (-not (Test-Path $darkDst))  { New-Item -ItemType Directory -Path $darkDst  -Force | Out-Null }

$files = @(
  @{src="light-01-intro.html";     dst="$lightDst\01-INTRO.html"},
  @{src="light-02-ciencia.html";   dst="$lightDst\02-CIENCIA.html"},
  @{src="light-03-contexto.html";  dst="$lightDst\03-CONTEXTO.html"},
  @{src="light-04-aplicacion.html";dst="$lightDst\04-APLICACION.html"},
  @{src="light-05-cta.html";       dst="$lightDst\05-CTA.html"},
  @{src="dark-01-intro.html";      dst="$darkDst\01-INTRO.html"},
  @{src="dark-02-ciencia.html";    dst="$darkDst\02-CIENCIA.html"},
  @{src="dark-03-contexto.html";   dst="$darkDst\03-CONTEXTO.html"},
  @{src="dark-04-aplicacion.html"; dst="$darkDst\04-APLICACION.html"},
  @{src="dark-05-cta.html";        dst="$darkDst\05-CTA.html"}
)

foreach ($f in $files) {
  $srcPath = Join-Path $srcDir $f.src
  Copy-Item $srcPath $f.dst -Force
  Write-Output "Copied: $($f.dst)"
}

Write-Output ""
Write-Output "--- LIGHT ---"
Get-ChildItem $lightDst | Select-Object Name, Length
Write-Output ""
Write-Output "--- DARK ---"
Get-ChildItem $darkDst | Select-Object Name, Length
