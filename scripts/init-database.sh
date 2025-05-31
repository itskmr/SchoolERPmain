#!/bin/bash

echo "🚀 Starting School Management System Database Setup..."

# Wait for database to be ready
wait_for_db() {
    echo "⏳ Waiting for database connection..."
    local max_attempts=30
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if mysql -h"$MYSQLHOST" -P"$MYSQLPORT" -u"$MYSQLUSER" -p"$MYSQLPASSWORD" -e "SELECT 1;" > /dev/null 2>&1; then
            echo "✅ Database connection established!"
            return 0
        fi
        echo "⏳ Attempt $attempt/$max_attempts - Database not ready yet..."
        sleep 2
        attempt=$((attempt + 1))
    done
    
    echo "❌ Could not connect to database after $max_attempts attempts"
    return 1
}

# Check if database exists and has tables
check_database_setup() {
    echo "🔍 Checking if database is already set up..."
    
    local table_count=$(mysql -h"$MYSQLHOST" -P"$MYSQLPORT" -u"$MYSQLUSER" -p"$MYSQLPASSWORD" "$MYSQLDATABASE" -sN -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$MYSQLDATABASE';" 2>/dev/null)
    
    if [ "$table_count" -gt 0 ]; then
        echo "✅ Database already contains $table_count tables. Skipping import."
        return 1
    else
        echo "📋 Database is empty. Will import schema."
        return 0
    fi
}

# Import database schema
import_database() {
    echo "📁 Importing database schema from school.sql..."
    
    if [ -f "/var/www/html/database_file/school.sql" ]; then
        mysql -h"$MYSQLHOST" -P"$MYSQLPORT" -u"$MYSQLUSER" -p"$MYSQLPASSWORD" "$MYSQLDATABASE" < /var/www/html/database_file/school.sql
        
        if [ $? -eq 0 ]; then
            echo "✅ Database schema imported successfully!"
        else
            echo "❌ Failed to import database schema"
            return 1
        fi
    else
        echo "❌ Database file not found at /var/www/html/database_file/school.sql"
        return 1
    fi
}

# Add custom admin user
add_custom_admin() {
    echo "👤 Adding custom admin user (Ronit)..."
    
    # Check if custom admin already exists
    local user_exists=$(mysql -h"$MYSQLHOST" -P"$MYSQLPORT" -u"$MYSQLUSER" -p"$MYSQLPASSWORD" "$MYSQLDATABASE" -sN -e "SELECT COUNT(*) FROM admin WHERE email = 'ronit@gmail.com';" 2>/dev/null)
    
    if [ "$user_exists" -gt 0 ]; then
        echo "✅ Custom admin user already exists. Skipping creation."
    else
        mysql -h"$MYSQLHOST" -P"$MYSQLPORT" -u"$MYSQLUSER" -p"$MYSQLPASSWORD" "$MYSQLDATABASE" -e "INSERT INTO admin (name, email, phone, password, level, login_status) VALUES ('Ronit', 'ronit@gmail.com', '0000000000', '8cb2237d0679ca88db6464eac60da96345513964', '1', '0');"
        
        if [ $? -eq 0 ]; then
            echo "✅ Custom admin user created successfully!"
            echo "   📧 Email: ronit@gmail.com"
            echo "   🔑 Password: 12345"
        else
            echo "❌ Failed to create custom admin user"
            return 1
        fi
    fi
}

# Main execution
main() {
    # Only run database setup if we have database connection details
    if [ -n "$MYSQLHOST" ] && [ -n "$MYSQLUSER" ] && [ -n "$MYSQLDATABASE" ]; then
        echo "🔗 Database configuration detected. Starting setup..."
        
        if wait_for_db; then
            if check_database_setup; then
                import_database
                if [ $? -eq 0 ]; then
                    add_custom_admin
                fi
            fi
        else
            echo "⚠️  Could not connect to database. Application will start but may not work properly."
        fi
    else
        echo "⚠️  Database environment variables not found. Skipping database setup."
        echo "   This is normal for local development with docker-compose."
    fi
    
    echo "🎉 Database setup completed!"
}

# Run main function
main 