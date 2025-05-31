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

# Copy and make scripts executable
COPY scripts/init-database.sh /var/www/html/scripts/
COPY scripts/startup.sh /usr/local/bin/
RUN chmod +x /var/www/html/scripts/init-database.sh \
    && chmod +x /usr/local/bin/startup.sh

# Set permissions for writable directories
RUN chown -R www-data:www-data /var/www/html/application/cache \
                               /var/www/html/application/logs \
                               /var/www/html/uploads \
    && chmod -R 755 /var/www/html/application/cache \
                   /var/www/html/application/logs \
                   /var/www/html/uploads

# Create backup of original database config
RUN if [ -f /var/www/html/application/config/database.php ]; then \
        cp /var/www/html/application/config/database.php /var/www/html/application/config/database.php.backup; \
    fi

# Expose port 80
EXPOSE 80

CMD ["/usr/local/bin/startup.sh"] 