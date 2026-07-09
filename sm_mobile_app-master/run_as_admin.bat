@echo off
echo Running Flutter with elevated privileges...
cd /d "%~dp0"
flutter clean
flutter pub get
flutter run -d 127.0
pause
