#!/bin/bash

echo "🚀 School Management System - Deployment Setup"
echo "=============================================="

# Check if we're in the right directory
if [ ! -f "index.php" ]; then
    echo "❌ Error: Please run this script from the project root directory"
    exit 1
fi

echo "✅ Preparing your School Management System for deployment..."

# Create necessary directories if they don't exist
echo "📁 Creating required directories..."
mkdir -p application/cache
mkdir -p application/logs
mkdir -p uploads

# Set proper permissions
echo "🔐 Setting permissions..."
chmod 755 application/cache
chmod 755 application/logs
chmod 755 uploads

# Check if important files exist
echo "🔍 Checking required files..."

if [ ! -f "railway.json" ]; then
    echo "❌ railway.json not found"
else
    echo "✅ railway.json found"
fi

if [ ! -f "Dockerfile.railway" ]; then
    echo "❌ Dockerfile.railway not found"
else
    echo "✅ Dockerfile.railway found"
fi

if [ ! -f "database_file/school.sql" ]; then
    echo "❌ Database file not found"
else
    echo "✅ Database file found"
fi

if [ ! -f "DEPLOYMENT.md" ]; then
    echo "❌ DEPLOYMENT.md not found"
else
    echo "✅ DEPLOYMENT.md found"
fi

echo ""
echo "🎉 Setup complete! Your project is ready for deployment."
echo ""
echo "📋 Next steps:"
echo "1. Push your code to GitHub if you haven't already"
echo "2. Follow the instructions in DEPLOYMENT.md"
echo "3. Choose your preferred deployment platform (Railway recommended)"
echo ""
echo "📖 Read DEPLOYMENT.md for detailed step-by-step instructions"
echo ""
echo "Happy deploying! 🚀" 