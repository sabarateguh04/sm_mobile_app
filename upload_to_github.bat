@echo off
echo ==============================================
echo   Mengunggah Smart BackOffice ke GitHub
echo ==============================================
echo.

:: Cek apakah git terinstal
where git >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Git tidak ditemukan di komputer Anda. Silakan unduh dan instal Git terlebih dahulu.
    pause
    exit /b
)

:: Inisialisasi git jika belum ada
if not exist .git (
    echo [1/5] Menginisialisasi Git lokal...
    git init
    echo.
) else (
    echo [1/5] Git sudah terinisialisasi.
    echo.
)

:: Menambahkan file
echo [2/5] Menambahkan berkas ke Git staging...
git add .
echo.

:: Commit
echo [3/5] Membuat commit...
git commit -m "Initial commit - Smart Backoffice Laravel"
echo.

:: Menentukan branch main
echo [4/5] Mengatur branch ke main...
git branch -M main
echo.

:: Menambahkan remote origin dan push
echo [5/5] Menghubungkan ke remote origin...
git remote remove origin >nul 2>nul
git remote add origin https://github.com/Baden-Copilot/smartbackoffice-laravel.git

echo.
echo Sedang mengunggah berkas ke GitHub...
git push -u origin main

echo.
echo ==============================================
echo   Proses Selesai!
echo ==============================================
pause
