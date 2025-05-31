@echo off
echo 🚀 School Management System - Deployment Setup
echo ==============================================

REM Check if we're in the right directory
if not exist "index.php" (
    echo ❌ Error: Please run this script from the project root directory
    pause
    exit /b 1
)

echo ✅ Preparing your School Management System for deployment...

REM Create necessary directories if they don't exist
echo 📁 Creating required directories...
if not exist "application\cache" mkdir application\cache
if not exist "application\logs" mkdir application\logs
if not exist "uploads" mkdir uploads

REM Check if important files exist
echo 🔍 Checking required files...

if not exist "railway.json" (
    echo ❌ railway.json not found
) else (
    echo ✅ railway.json found
)

if not exist "Dockerfile.railway" (
    echo ❌ Dockerfile.railway not found
) else (
    echo ✅ Dockerfile.railway found
)

if not exist "database_file\school.sql" (
    echo ❌ Database file not found
) else (
    echo ✅ Database file found
)

if not exist "DEPLOYMENT.md" (
    echo ❌ DEPLOYMENT.md not found
) else (
    echo ✅ DEPLOYMENT.md found
)

echo.
echo 🎉 Setup complete! Your project is ready for deployment.
echo.
echo 📋 Next steps:
echo 1. Push your code to GitHub if you haven't already
echo 2. Follow the instructions in DEPLOYMENT.md
echo 3. Choose your preferred deployment platform (Railway recommended)
echo.
echo 📖 Read DEPLOYMENT.md for detailed step-by-step instructions
echo.
echo Happy deploying! 🚀
echo.
pause 