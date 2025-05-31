#!/bin/bash

echo "🚀 Starting School Management System..."

# Update database configuration if Railway MySQL variables are available
if [ ! -z "$MYSQLHOST" ]; then
    echo "🔧 Configuring database connection for Railway..."
    sed -i "s/'hostname' => 'localhost'/'hostname' => '$MYSQLHOST'/g" /var/www/html/application/config/database.php
    sed -i "s/'username' => 'root'/'username' => '$MYSQLUSER'/g" /var/www/html/application/config/database.php
    sed -i "s/'password' => ''/'password' => '$MYSQLPASSWORD'/g" /var/www/html/application/config/database.php
    sed -i "s/'database' => 'school'/'database' => '$MYSQLDATABASE'/g" /var/www/html/application/config/database.php
    sed -i "s/'port' => ''/'port' => '$MYSQLPORT'/g" /var/www/html/application/config/database.php
    
    echo "✅ Database configuration updated"
    
    # Run database initialization in background
    echo "🗄️ Starting database initialization..."
    /var/www/html/scripts/init-database.sh &
else
    echo "⚠️  No Railway database variables found. Using local configuration."
fi

echo "🌐 Starting Apache web server..."
# Start Apache in foreground
exec apache2-foreground 