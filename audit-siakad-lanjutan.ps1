
# AUDIT FRONTEND vs BACKEND - LARAVEL 12
# Jalankan dari PowerShell pada folder proyek.
# Script ini hanya membaca file dan membuat laporan.

$ErrorActionPreference = "Stop"
$Project = (Get-Location).Path
$ReportDir = Join-Path $Project "audit-report"

if (!(Test-Path (Join-Path $Project "artisan"))) {
    Write-Host "ERROR: artisan tidak ditemukan." -ForegroundColor Red
    Write-Host "Pindah dulu ke folder root proyek Laravel."
    exit 1
}

New-Item -ItemType Directory -Force -Path $ReportDir | Out-Null

Write-Host "`n[1/5] Membaca route Laravel..." -ForegroundColor Cyan
$routeOutput = & php artisan route:list --json 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host ($routeOutput | Out-String) -ForegroundColor Red
    exit 1
}

try {
    $routes = ($routeOutput | Out-String | ConvertFrom-Json)
} catch {
    Write-Host "Gagal membaca output route:list --json." -ForegroundColor Red
    exit 1
}

$routes | ForEach-Object {
    [PSCustomObject]@{
        Method     = $_.method
        URI        = $_.uri
        Name       = $_.name
        Action     = $_.action
        Middleware = ($_.middleware -join ",")
    }
} | Export-Csv (Join-Path $ReportDir "backend-routes.csv") `
    -NoTypeInformation -Encoding UTF8

Write-Host "[2/5] Memeriksa file Blade..." -ForegroundColor Cyan
$viewsPath = Join-Path $Project "resources\views"
$bladeFiles = @(Get-ChildItem $viewsPath -Recurse -File -Filter "*.blade.php")

$frontendCalls = @()
$crudEvidence = @()

foreach ($file in $bladeFiles) {
    $content = Get-Content $file.FullName -Raw
    $relative = $file.FullName.Substring($Project.Length).TrimStart('\')

    # Semua pemanggilan route('nama.route')
    $matches = [regex]::Matches(
        $content,
        "route\(\s*['`"]([^'`"]+)['`"]"
    )

    foreach ($match in $matches) {
        $routeName = $match.Groups[1].Value
        $found = @($routes | Where-Object { $_.name -eq $routeName })

        $frontendCalls += [PSCustomObject]@{
            View       = $relative
            RouteName  = $routeName
            RouteFound = ($found.Count -gt 0)
            BackendURI = ($found.uri -join "; ")
            Method     = ($found.method -join "; ")
            Action     = ($found.action -join "; ")
        }
    }

    # Bukti form dan tombol/link CRUD secara tekstual
    $forms = [regex]::Matches($content, '(?is)<form\b.*?</form>')
    $crudEvidence += [PSCustomObject]@{
        View       = $relative
        Forms      = $forms.Count
        HasPost    = [bool]($content -match '(?i)\bmethod\s*=\s*["'']post["'']')
        HasDelete  = [bool]($content -match '(?i)\b(delete|destroy)\b')
        HasCreate  = [bool]($content -match '(?i)\b(tambah|buat|create|input baru)\b')
        HasEdit    = [bool]($content -match '(?i)\b(edit|ubah|update)\b')
        HasShow    = [bool]($content -match '(?i)\b(detail|show|lihat)\b')
    }
}

$frontendCalls |
    Sort-Object View, RouteName -Unique |
    Export-Csv (Join-Path $ReportDir "frontend-route-calls.csv") `
        -NoTypeInformation -Encoding UTF8

$crudEvidence |
    Export-Csv (Join-Path $ReportDir "blade-crud-indicators.csv") `
        -NoTypeInformation -Encoding UTF8

Write-Host "[3/5] Mencari route backend yang tidak dipanggil langsung oleh Blade..." -ForegroundColor Cyan
$calledNames = @($frontendCalls.RouteName | Sort-Object -Unique)
$backendNotFoundInBlade = @($routes | Where-Object {
    $_.name -and $_.name -notin $calledNames
} | ForEach-Object {
    [PSCustomObject]@{
        Name       = $_.name
        Method     = $_.method
        URI        = $_.uri
        Action     = $_.action
        Middleware = ($_.middleware -join ",")
        Note       = "Belum ditemukan pemanggilan langsung di Blade; bisa jadi API, redirect, AJAX, atau memang belum dipakai."
    }
})

$backendNotFoundInBlade |
    Export-Csv (Join-Path $ReportDir "backend-routes-not-seen-in-blade.csv") `
        -NoTypeInformation -Encoding UTF8

Write-Host "[4/5] Mencari pemanggilan route yang tidak terdaftar..." -ForegroundColor Cyan
$missingRoutes = @($frontendCalls | Where-Object { !$_.RouteFound })
$missingRoutes |
    Export-Csv (Join-Path $ReportDir "frontend-route-missing.csv") `
        -NoTypeInformation -Encoding UTF8

Write-Host "[5/5] Membuat ringkasan..." -ForegroundColor Cyan
$summary = @"
AUDIT SIAKAD
Tanggal : $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
Proyek  : $Project

Jumlah route backend             : $(@($routes).Count)
Jumlah file Blade                : $($bladeFiles.Count)
Jumlah pemanggilan route di Blade: $($frontendCalls.Count)
Pemanggilan route tidak ditemukan: $($missingRoutes.Count)
Route bernama tak terlihat di Blade: $($backendNotFoundInBlade.Count)

File laporan:
- backend-routes.csv
- frontend-route-calls.csv
- frontend-route-missing.csv
- backend-routes-not-seen-in-blade.csv
- blade-crud-indicators.csv

CATATAN:
Laporan ini adalah pemeriksaan statis awal, bukan bukti pasti bug.
Route yang tidak dipanggil langsung oleh Blade bisa digunakan oleh JavaScript,
Livewire, redirect, API, atau controller.
Indikator CRUD berbasis teks juga perlu diverifikasi manual.
"@

$summary | Set-Content (Join-Path $ReportDir "README.txt") -Encoding UTF8
Write-Host $summary -ForegroundColor Green
Write-Host "`nSelesai. Laporan tersimpan di: $ReportDir" -ForegroundColor Yellow
