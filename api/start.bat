@echo off
echo ========================================
echo   Solar CRM API - Demarrage du serveur
echo ========================================
echo.
echo L'API sera disponible sur: http://localhost:8000/api
echo.
echo Identifiants de test:
echo   Email: admin@solarcrm.com
echo   Password: password
echo.
echo ========================================
echo.
php -S localhost:8000 -t public
