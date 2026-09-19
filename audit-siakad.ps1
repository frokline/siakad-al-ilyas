[CmdletBinding()]
param(
    [string]$ProjectPath = 'D:\xampp\htdocs\siakad-ilyas',
    [switch]$SkipPhpLint,
    [switch]$SkipDatabase
)

# SIAKAD Ilyas - audit READ ONLY untuk Windows PowerShell 5.1.
$ErrorActionPreference = 'Continue'
$ProjectPath = [System.IO.Path]::GetFullPath($ProjectPath)
$TimeStamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$ReportDirectory = Join-Path $ProjectPath 'storage\logs\siakad-audit'
$TextReport = Join-Path $ReportDirectory "audit-$TimeStamp.txt"
$JsonReport = Join-Path $ReportDirectory "audit-$TimeStamp.json"
$MigrationReport = Join-Path $ReportDirectory "migrations-$TimeStamp.txt"
$RouteReport = Join-Path $ReportDirectory "routes-$TimeStamp.txt"
$DatabaseReport = Join-Path $ReportDirectory "database-$TimeStamp.txt"
$Results = New-Object 'System.Collections.Generic.List[object]'

function Add-Result {
    param([string]$Area, [string]$Status, [string]$Target, [string]$Message)
    $Results.Add([pscustomobject]@{ Area=$Area; Status=$Status; Target=$Target; Message=$Message })
    $Color = 'Gray'
    if ($Status -eq 'PASS') { $Color = 'Green' }
    if ($Status -eq 'WARN') { $Color = 'Yellow' }
    if ($Status -eq 'FAIL') { $Color = 'Red' }
    Write-Host ("[{0}] {1} - {2}" -f $Status, $Target, $Message) -ForegroundColor $Color
}

function Run-Program {
    param([string]$Program, [string[]]$Arguments)
    try {
        $Output = & $Program @Arguments 2>&1 | Out-String
        return [pscustomobject]@{ ExitCode=$LASTEXITCODE; Output=$Output.Trim() }
    } catch {
        return [pscustomobject]@{ ExitCode=999; Output=$_.Exception.Message }
    }
}

function Relative-Path {
    param([string]$FullPath)
    if ($FullPath.StartsWith($ProjectPath, [System.StringComparison]::OrdinalIgnoreCase)) {
        return $FullPath.Substring($ProjectPath.Length).TrimStart([char]92, [char]47)
    }
    return $FullPath
}

if (-not (Test-Path -LiteralPath $ProjectPath -PathType Container)) {
    Write-Host "Folder proyek tidak ditemukan: $ProjectPath" -ForegroundColor Red
    exit 2
}
Set-Location -LiteralPath $ProjectPath
New-Item -ItemType Directory -Force -Path $ReportDirectory | Out-Null
Write-Host 'AUDIT SIAKAD READ ONLY' -ForegroundColor Cyan
Write-Host "Proyek  : $ProjectPath"
Write-Host "Laporan : $ReportDirectory"

if (Test-Path -LiteralPath (Join-Path $ProjectPath 'artisan') -PathType Leaf) {
    Add-Result 'Project' 'PASS' 'artisan' 'Root Laravel ditemukan.'
} else {
    Add-Result 'Project' 'FAIL' 'artisan' 'File artisan tidak ditemukan.'
    exit 2
}

$RequiredFolders = @(
    'app','app\Models','app\Http\Controllers','app\Http\Requests','app\Policies',
    'app\Services','app\Actions','config','database\migrations','resources\views','routes','tests'
)
foreach ($Folder in $RequiredFolders) {
    $Full = Join-Path $ProjectPath $Folder
    if (Test-Path -LiteralPath $Full -PathType Container) { Add-Result 'Folder' 'PASS' $Folder 'Folder tersedia.' }
    else { Add-Result 'Folder' 'FAIL' $Folder 'Folder tidak ditemukan.' }
}

$RequiredFiles = @(
    'app\Models\User.php','app\Models\Mahasiswa.php','app\Models\Dosen.php',
    'app\Models\Pengumuman.php','app\Models\Notifikasi.php',
    'app\Services\AksesMateri.php','app\Services\AksesBerkas.php',
    'app\Services\AksesPengumuman.php','app\Services\AksesNotifikasi.php',
    'app\Services\SumberNotifikasi.php','app\Http\Controllers\PengumumanController.php',
    'app\Http\Controllers\NotifikasiController.php','app\Http\Requests\PengumumanRequest.php',
    'app\Http\Requests\NotifikasiRequest.php','app\Policies\PengumumanPolicy.php',
    'app\Policies\NotifikasiPolicy.php','app\Providers\AppServiceProvider.php',
    'config\notifikasi.php','routes\web.php','routes\console.php'
)
foreach ($File in $RequiredFiles) {
    $Full = Join-Path $ProjectPath $File
    if (-not (Test-Path -LiteralPath $Full -PathType Leaf)) { Add-Result 'Completeness' 'FAIL' $File 'File tidak ditemukan.' }
    elseif ((Get-Item -LiteralPath $Full).Length -eq 0) { Add-Result 'Completeness' 'FAIL' $File 'File ada tetapi kosong.' }
    else { Add-Result 'Completeness' 'PASS' $File 'File ditemukan.' }
}

foreach ($Folder in @('pengumuman','notifikasi')) {
    $Full = Join-Path $ProjectPath ("resources\views\$Folder")
    if (-not (Test-Path -LiteralPath $Full -PathType Container)) { Add-Result 'Blade' 'FAIL' "resources\views\$Folder" 'Folder Blade tidak ditemukan.' }
    elseif ((Get-ChildItem -LiteralPath $Full -File -Filter '*.blade.php').Count -eq 0) { Add-Result 'Blade' 'FAIL' "resources\views\$Folder" 'Folder ada tetapi tidak berisi Blade.' }
    else { Add-Result 'Blade' 'PASS' "resources\views\$Folder" 'Blade ditemukan.' }
}

$PhpFiles = Get-ChildItem -LiteralPath $ProjectPath -Recurse -File -Filter '*.php' | Where-Object {
    $_.FullName -notlike '*\vendor\*' -and $_.FullName -notlike '*\storage\*' -and
    $_.FullName -notlike '*\bootstrap\cache\*' -and $_.FullName -notlike '*\node_modules\*'
}
foreach ($File in $PhpFiles) {
    $Relative = Relative-Path $File.FullName
    if ($File.Length -eq 0) { Add-Result 'PHP' 'FAIL' $Relative 'File PHP kosong.'; continue }
    $Content = Get-Content -LiteralPath $File.FullName -Raw
    if ($Content -match '(?i)TODO|TBD|implementasi di sini|kode di sini|isi sendiri|tempel.*di sini|your code') {
        Add-Result 'PHP' 'WARN' $Relative 'Mengandung TODO/placeholder; periksa manual.'
    }
    if (-not $SkipPhpLint) {
        $Lint = Run-Program 'php.exe' @('-l', $File.FullName)
        if ($Lint.ExitCode -eq 0) { Add-Result 'PHP lint' 'PASS' $Relative 'Sintaks valid.' }
        else { Add-Result 'PHP lint' 'FAIL' $Relative $Lint.Output }
    }
}

$BladeFiles = Get-ChildItem -LiteralPath (Join-Path $ProjectPath 'resources\views') -Recurse -File -Filter '*.blade.php'
foreach ($File in $BladeFiles) {
    $Relative = Relative-Path $File.FullName
    $Content = Get-Content -LiteralPath $File.FullName -Raw
    if ($File.Length -eq 0) { Add-Result 'Blade' 'FAIL' $Relative 'File Blade kosong.' }
    if ($Content.Contains('{!!')) { Add-Result 'Blade security' 'WARN' $Relative 'Ada output mentah {!! !!}; periksa risiko XSS.' }
    if ($Content.Contains('method="post"') -and -not $Content.Contains('@csrf') -and -not $Content.Contains('@include')) {
        Add-Result 'Blade security' 'WARN' $Relative 'Form POST mungkin tidak memiliki @csrf.'
    }
}

$WebRoute = Join-Path $ProjectPath 'routes\web.php'
if (Test-Path -LiteralPath $WebRoute) {
    $RouteContent = Get-Content -LiteralPath $WebRoute -Raw
    if ($RouteContent.Contains("prefix('admin/admin")) { Add-Result 'Routes' 'FAIL' 'routes\web.php' 'Ditemukan prefix admin/admin.' }
    foreach ($Name in @('pengumuman.','notifikasi.')) {
        if ($RouteContent.Contains($Name)) { Add-Result 'Routes' 'PASS' $Name 'Grup route ditemukan di web.php.' }
        else { Add-Result 'Routes' 'FAIL' $Name 'Grup route tidak ditemukan di web.php.' }
    }
}

$About = Run-Program 'php.exe' @('artisan','about')
if ($About.ExitCode -eq 0) { Add-Result 'Laravel' 'PASS' 'php artisan about' 'Laravel berhasil dimuat.' }
else { Add-Result 'Laravel' 'FAIL' 'php artisan about' $About.Output }

$Migrations = Run-Program 'php.exe' @('artisan','migrate:status')
$Migrations.Output | Set-Content -LiteralPath $MigrationReport -Encoding UTF8
if ($Migrations.ExitCode -eq 0) {
    Add-Result 'Migration' 'PASS' 'php artisan migrate:status' 'Status migration berhasil dibaca.'
    if ($Migrations.Output -match 'Pending') { Add-Result 'Migration' 'WARN' 'Pending' 'Masih ada migration Pending.' }
} else { Add-Result 'Migration' 'FAIL' 'php artisan migrate:status' $Migrations.Output }

$Routes = Run-Program 'php.exe' @('artisan','route:list')
$Routes.Output | Set-Content -LiteralPath $RouteReport -Encoding UTF8
if ($Routes.ExitCode -eq 0) {
    Add-Result 'Routes' 'PASS' 'php artisan route:list' 'Daftar route berhasil dibangun.'
    if ($Routes.Output.Contains('admin/admin/')) { Add-Result 'Routes' 'FAIL' 'admin/admin' 'URL admin tergandakan.' }
} else { Add-Result 'Routes' 'FAIL' 'php artisan route:list' $Routes.Output }

if (-not $SkipDatabase) {
    $DbShow = Run-Program 'php.exe' @('artisan','db:show','--counts')
    $DbShow.Output | Set-Content -LiteralPath $DatabaseReport -Encoding UTF8
    if ($DbShow.ExitCode -eq 0) { Add-Result 'Database' 'PASS' 'php artisan db:show --counts' 'Koneksi database berhasil.' }
    else { Add-Result 'Database' 'FAIL' 'php artisan db:show --counts' $DbShow.Output }

    $ExpectedTables = @(
        'users','roles','user_roles','mahasiswa','dosen','program_studi','periode_akademik',
        'kurikulum','mata_kuliah','kurikulum_mata_kuliah','riwayat_studi','paket_semester',
        'detail_paket','rombel','registrasi_semester','kelas_kuliah','krs','detail_krs',
        'pengajar_kelas','jadwal_kuliah','pertemuan','presensi','berkas','materi','materi_berkas',
        'kegiatan','kegiatan_berkas','pengumpulan','pengumpulan_berkas','jenis_biaya','tagihan',
        'pembayaran','verifikasi_pembayaran','pengumuman','sasaran_pengumuman','notifikasi',
        'kalender_akademik','jenis_surat','permohonan_surat','riwayat_surat','audit_log'
    )
    foreach ($Table in $ExpectedTables) {
        $TableResult = Run-Program 'php.exe' @('artisan','db:table',$Table)
        if ($TableResult.ExitCode -eq 0) {
            Add-Result 'Database table' 'PASS' $Table 'Tabel dapat dibaca.'
            Add-Content -LiteralPath $DatabaseReport -Value ("`r`n===== $Table =====`r`n" + $TableResult.Output)
        } else { Add-Result 'Database table' 'FAIL' $Table 'Tabel tidak ditemukan atau tidak dapat dibaca.' }
    }
}

$FailCount = @($Results | Where-Object { $_.Status -eq 'FAIL' }).Count
$WarnCount = @($Results | Where-Object { $_.Status -eq 'WARN' }).Count
$PassCount = @($Results | Where-Object { $_.Status -eq 'PASS' }).Count
$Header = @('AUDIT SIAKAD ILYAS - READ ONLY', "Tanggal : $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')", "Proyek  : $ProjectPath", "PASS=$PassCount WARN=$WarnCount FAIL=$FailCount", '') -join [Environment]::NewLine
$Body = $Results | Sort-Object Status,Area,Target | Format-Table -AutoSize -Wrap | Out-String -Width 260
Set-Content -LiteralPath $TextReport -Value ($Header + $Body) -Encoding UTF8
$Results | ConvertTo-Json -Depth 5 | Set-Content -LiteralPath $JsonReport -Encoding UTF8
Write-Host ''
Write-Host "SELESAI: PASS=$PassCount WARN=$WarnCount FAIL=$FailCount" -ForegroundColor Cyan
Write-Host "Laporan utama : $TextReport"
Write-Host "Laporan JSON  : $JsonReport"
Write-Host "Migration     : $MigrationReport"
Write-Host "Route         : $RouteReport"
Write-Host "Database      : $DatabaseReport"
if ($FailCount -gt 0) { exit 1 }
exit 0
