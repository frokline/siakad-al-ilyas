[CmdletBinding()]
param(
    [string]$ProjectPath = "D:\xampp\htdocs\siakad-ilyas",
    [string]$ReportPath = "",
    [switch]$IncludeVendorAndNodeModules
)
$ErrorActionPreference = "Stop"
if (-not (Test-Path -LiteralPath $ProjectPath -PathType Container)) { throw "Folder proyek tidak ditemukan: $ProjectPath" }
$ProjectPath = (Resolve-Path -LiteralPath $ProjectPath).Path
if ([string]::IsNullOrWhiteSpace($ReportPath)) { $ReportPath = Join-Path $ProjectPath ("audit-sampah-{0}.csv" -f (Get-Date -Format "yyyyMMdd-HHmmss")) }

# Area dependency/cache/log dikecualikan agar hasil lebih relevan.
$skipRegex = '[\\/](vendor|node_modules|\.git|storage[\\/]framework[\\/](cache|sessions|views)|storage[\\/]logs|bootstrap[\\/]cache)[\\/]'
if ($IncludeVendorAndNodeModules) { $skipRegex = '[\\/](\.git|storage[\\/]framework[\\/](cache|sessions|views)|storage[\\/]logs|bootstrap[\\/]cache)[\\/]' }

$allFiles = @(Get-ChildItem -LiteralPath $ProjectPath -File -Recurse -Force -ErrorAction SilentlyContinue |
    Where-Object { $_.FullName -notmatch $skipRegex })
Write-Host "Memindai $($allFiles.Count) file di: $ProjectPath" -ForegroundColor Cyan
Write-Host "Mode aman: hanya membuat laporan; tidak menghapus atau mengubah file." -ForegroundColor Yellow

$refExt = @('.php','.js','.ts','.jsx','.tsx','.vue','.css','.scss','.json','.xml','.yml','.yaml','.md','.html','.htm','.sql')
$sourceFiles = @($allFiles | Where-Object { $refExt -contains $_.Extension.ToLowerInvariant() -or $_.Name.ToLowerInvariant().EndsWith('.blade.php') -or $_.Name -eq '.env.example' })
$sourceText = @{}
foreach ($sf in $sourceFiles) {
    try { if ($sf.Length -le 2MB) { $sourceText[$sf.FullName] = [IO.File]::ReadAllText($sf.FullName) } } catch {}
}

$results = foreach ($file in $allFiles) {
    $relative = $file.FullName.Substring($ProjectPath.Length).TrimStart('\')
    $n = $file.Name.ToLowerInvariant()
    $ext = $file.Extension.ToLowerInvariant()
    $reasons = [Collections.Generic.List[string]]::new()
    $status = 'INFO'
    $refCount = 0
    $examples = ''

    if ($ext -eq '.txt') { $reasons.Add('File .txt perlu diperiksa; tidak otomatis sampah'); $status = 'PERIKSA' }
    if ($n -match '(^|[._-])(old|backup|bak|orig|disabled|nonaktif|lama|copy|salinan|unused|deprecated)([._-]|$)' -or $n -match '\.(bak|old|orig|backup|disabled|tmp|temp)$') {
        $reasons.Add('Nama file mengandung penanda backup/lama/nonaktif'); $status = 'PERIKSA'
    }
    if ($n -match '^(test|debug|dump|hasil-test|output|temp|tmp)[._-]') {
        $reasons.Add('Nama file mirip artefak debug/test sementara'); $status = 'PERIKSA'
    }

    $isAppCode = $relative -match '^(app|routes|resources|database|config|public|bootstrap|tests|lang)[\\/]'
    if ($isAppCode -and $file.Length -le 2MB -and $ext -in @('.php','.js','.ts','.css','.scss','.vue','.json','.html','.htm')) {
        $base = [regex]::Escape($file.Name)
        $relSlash = [regex]::Escape(($relative -replace '\\','/'))
        $relWin = [regex]::Escape($relative)
        foreach ($entry in $sourceText.GetEnumerator()) {
            if ($entry.Key -eq $file.FullName) { continue }
            if ($entry.Value -match $base -or $entry.Value -match $relSlash -or $entry.Value -match $relWin) {
                $refCount++
                if ($refCount -le 3) {
                    $r = $entry.Key.Substring($ProjectPath.Length).TrimStart('\')
                    if ($examples) { $examples += ' | ' }; $examples += $r
                }
            }
        }
        if ($refCount -eq 0) {
            $reasons.Add('Tidak ditemukan referensi tekstual langsung; perlu verifikasi manual')
            if ($status -eq 'INFO') { $status = 'KANDIDAT' }
        }
    }
    if ($relative -match '^database[\\/](migrations|seeders|factories)[\\/]') {
        if ($reasons.Count -eq 0) { $reasons.Add('File database: periksa sebelum mengubah/menghapus') }
        if ($status -eq 'INFO') { $status = 'PERIKSA' }
    }
    if ($reasons.Count -gt 0) {
        [PSCustomObject]@{
            Status = $status
            RelPath = $relative
            Extension = $ext
            SizeKB = [math]::Round($file.Length / 1KB, 1)
            ReferencesFound = $refCount
            Reasons = ($reasons -join '; ')
            ExampleReferences = $examples
            LastModified = $file.LastWriteTime
        }
    }
}
$results = @($results | Sort-Object @{Expression={switch ($_.Status) {'KANDIDAT'{0} 'PERIKSA'{1} default{2}}}}, RelPath)
$results | Export-Csv -LiteralPath $ReportPath -NoTypeInformation -Encoding UTF8
Write-Host "`nSelesai. Jumlah temuan: $($results.Count)" -ForegroundColor Green
Write-Host "Laporan CSV: $ReportPath" -ForegroundColor Green
$results | Group-Object Status | Sort-Object Name | ForEach-Object { Write-Host ("  {0}: {1}" -f $_.Name, $_.Count) }
Write-Host "`nPENTING: status KANDIDAT atau referensi 0 bukan bukti file boleh dihapus." -ForegroundColor Yellow
Write-Host "Periksa pemanggilan dinamis, route, Composer autoload, config, scheduler, migration, dan penggunaan runtime." -ForegroundColor Yellow
