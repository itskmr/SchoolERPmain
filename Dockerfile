FROM php:8.1-apache

# Install system dependencies including MySQL client
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Fix Apache ServerName warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html/

# Create required directories if they don't exist
RUN mkdir -p /var/www/html/application/cache \
             /var/www/html/application/logs \
             /var/www/html/uploads \
             /var/www/html/scripts

# Copy and make database initialization script executable
COPY scripts/init-database.sh /var/www/html/scripts/
RUN chmod +x /var/www/html/scripts/init-database.sh

# Set permissions for writable directories
RUN chown -R www-data:www-data /var/www/html/application/cache \
                               /var/www/html/application/logs \
                               /var/www/html/uploads \
    && chmod -R 755 /var/www/html/application/cache \
                   /var/www/html/application/logs \
                   /var/www/html/uploads

# Update database config for Railway if environment variables exist
RUN if [ -f /var/www/html/application/config/database.php ]; then \
        cp /var/www/html/application/config/database.php /var/www/html/application/config/database.php.backup; \
    fi

# Create startup script that handles database setup and Apache startup
RUN cat > /usr/local/bin/startup.sh << 'EOF'
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
EOF

# Make startup script executable
RUN chmod +x /usr/local/bin/startup.sh

# Expose port 80
EXPOSE 80

CMD ["/usr/local/bin/startup.sh"] 