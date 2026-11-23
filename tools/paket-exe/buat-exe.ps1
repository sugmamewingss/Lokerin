# Merakit Lokerin.exe: project Laravel + PHP portable + database demo, ditempel ke peluncur.
# Jalankan lewat buat-exe.bat dari folder ini.
$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

function Langkah($teks) { Write-Host "`n==> $teks" -ForegroundColor Green }
function Wajib($perintah) {
    if (-not (Get-Command $perintah -ErrorAction SilentlyContinue)) { throw "Perintah '$perintah' tidak ditemukan. Pastikan sudah terpasang dan ada di PATH." }
}
function Jalankan([string]$label) {
    if ($LASTEXITCODE -ne 0) { throw "$label gagal (kode $LASTEXITCODE)." }
}

$Akar     = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$Dist     = Join-Path $PSScriptRoot 'dist'
$Stage    = Join-Path $Dist 'stage'
$App      = Join-Path $Stage 'app'
$PhpPaket = Join-Path $Stage 'php'
$Peluncur = Join-Path $PSScriptRoot 'Lokerin-launcher.exe'
$Hasil    = Join-Path $Dist 'Lokerin.exe'

Langkah 'Memeriksa alat'
Wajib php; Wajib composer; Wajib npm
if (-not (Test-Path $Peluncur)) { throw "Lokerin-launcher.exe tidak ditemukan di $PSScriptRoot." }
$PhpAsal = Split-Path (Get-Command php).Source
if (-not (Test-Path (Join-Path $PhpAsal 'ext\php_pdo_sqlite.dll'))) { throw "Ekstensi php_pdo_sqlite.dll tidak ada di $PhpAsal\ext." }
Write-Host "Project : $Akar"
Write-Host "PHP     : $PhpAsal"

Langkah 'Membersihkan hasil sebelumnya'
if (Test-Path $Dist) { Remove-Item $Dist -Recurse -Force }
New-Item -ItemType Directory -Force -Path $App, $PhpPaket | Out-Null

Langkah 'Build tampilan (npm run build)'
Push-Location $Akar
try { npm run build; Jalankan 'npm run build' } finally { Pop-Location }

Langkah 'Menyalin project'
robocopy $Akar $App /E /NFL /NDL /NJH /NJS /NP `
    /XD (Join-Path $Akar '.git') (Join-Path $Akar 'node_modules') (Join-Path $Akar 'vendor') (Join-Path $Akar 'tests') `
        (Join-Path $Akar 'tools') (Join-Path $Akar '.idea') (Join-Path $Akar '.vscode') `
        (Join-Path $Akar 'storage\logs') (Join-Path $Akar 'storage\framework') `
    /XF .env database.sqlite hot .phpunit.result.cache | Out-Null
if ($LASTEXITCODE -ge 8) { throw "robocopy gagal (kode $LASTEXITCODE)." }
$global:LASTEXITCODE = 0
Remove-Item (Join-Path $App 'bootstrap\cache\*.php') -Force -ErrorAction SilentlyContinue
foreach ($d in 'storage\logs', 'storage\framework\views', 'storage\framework\sessions', 'storage\framework\cache\data') {
    New-Item -ItemType Directory -Force -Path (Join-Path $App $d) | Out-Null
}

Langkah 'Menyiapkan .env'
$kunci = New-Object byte[] 32
[Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($kunci)
$env_ = Get-Content (Join-Path $Akar '.env.example') -Raw
$env_ = $env_ -replace '(?m)^APP_ENV=.*$', 'APP_ENV=production'
$env_ = $env_ -replace '(?m)^APP_DEBUG=.*$', 'APP_DEBUG=false'
$env_ = $env_ -replace '(?m)^APP_URL=.*$', 'APP_URL=http://127.0.0.1:8765'
$env_ = $env_ -replace '(?m)^APP_KEY=.*$', ('APP_KEY=base64:' + [Convert]::ToBase64String($kunci))
[IO.File]::WriteAllText((Join-Path $App '.env'), $env_, (New-Object Text.UTF8Encoding $false))

Langkah 'Memasang paket PHP tanpa paket pengembangan (composer --no-dev)'
composer install --working-dir="$App" --no-dev --optimize-autoloader --prefer-dist --no-interaction --no-progress
Jalankan 'composer install'

Langkah 'Membuat database demo'
$Db = Join-Path $App 'database\database.sqlite'
New-Item -ItemType File -Force -Path $Db | Out-Null
Push-Location $Akar
try {
    $env:DB_CONNECTION = 'sqlite'
    $env:DB_DATABASE = $Db
    php artisan migrate:fresh --seed --force
    Jalankan 'migrate:fresh --seed'
} finally {
    Remove-Item Env:DB_DATABASE -ErrorAction SilentlyContinue
    Remove-Item Env:DB_CONNECTION -ErrorAction SilentlyContinue
    Pop-Location
}
Remove-Item (Join-Path $App 'storage\logs\*.log') -Force -ErrorAction SilentlyContinue

Langkah 'Menyalin PHP portable'
Copy-Item (Join-Path $PhpAsal '*.exe'), (Join-Path $PhpAsal '*.dll') $PhpPaket
Copy-Item (Join-Path $PhpAsal 'ext') $PhpPaket -Recurse
Get-ChildItem (Join-Path $PhpPaket 'ext') -Exclude '*.dll' | Remove-Item -Recurse -Force

Langkah 'Mengompres paket'
Add-Type -AssemblyName System.IO.Compression.FileSystem
$Zip = Join-Path $Dist 'paket.zip'
[IO.Compression.ZipFile]::CreateFromDirectory($Stage, $Zip, [IO.Compression.CompressionLevel]::Optimal, $false)

Langkah 'Menggabungkan menjadi Lokerin.exe'
$keluar = [IO.File]::Create($Hasil)
try {
    foreach ($bagian in $Peluncur, $Zip) {
        $masuk = [IO.File]::OpenRead($bagian)
        try { $masuk.CopyTo($keluar) } finally { $masuk.Close() }
    }
} finally { $keluar.Close() }
Remove-Item $Zip -Force
Remove-Item $Stage -Recurse -Force

$mb = [math]::Round((Get-Item $Hasil).Length / 1MB, 1)
Write-Host "`nSELESAI: $Hasil ($mb MB)" -ForegroundColor Green
Write-Host 'Coba klik dua kali Lokerin.exe sebelum dikirim.'
