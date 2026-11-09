#!/bin/bash
#
# setup.sh - Laravel Baseplate Setup Script
#
# Author: Karl Almario <arvin.almario@appetiser.com.au>
# Created: 2025-07-31
# Description: This script installs dependencies, configures environment variables,
#              installs modules, and prepares the Laravel Baseplate application.
#
# Usage:
#   chmod +x setup.sh
#   ./setup.sh
#

# Check for required CLI tools
REQUIRED_COMMANDS=("php" "composer" "sed" "cp" "grep")
for cmd in "${REQUIRED_COMMANDS[@]}"; do
  if ! command -v "$cmd" >/dev/null 2>&1; then
    echo "Error: '$cmd' is not installed or not in PATH. Please install it and try again."
    exit 1
  fi
done


# Ensure script stops on errors
set -e

# Step 1: install laravel and baseplate core
# ignore some of the required for windows
composer install 
# Step 2: Copy .env and prompt for DB config
cp .env.example .env

read -p "DB Host (default: 127.0.0.1): " DB_HOST
DB_HOST=${DB_HOST:-127.0.0.1}

read -p "DB Port (default: 3306): " DB_PORT
DB_PORT=${DB_PORT:-3306}

read -p "DB Database (default: laravel): " DB_DATABASE
DB_DATABASE=${DB_DATABASE:-laravel}

read -p "DB Username (default: root): " DB_USERNAME
DB_USERNAME=${DB_USERNAME:-root}

read -p "DB Password: " DB_PASSWORD

read -p "Enter your APP_URL (default: http://127.0.0.1:8000): " APP_URL_INPUT
APP_URL=${APP_URL_INPUT:-http://127.0.0.1:8000}

# Update .env file
sed -i.bak "s/^DB_HOST=.*/DB_HOST=${DB_HOST}/" .env
sed -i.bak "s/^DB_PORT=.*/DB_PORT=${DB_PORT}/" .env
sed -i.bak "s/^DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE}/" .env
sed -i.bak "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME}/" .env
sed -i.bak "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" .env
sed -i.bak "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env

# Step 3: Install modules
php artisan lego:install AuthModule
php artisan lego:install AdminModule
php artisan lego:install AppSettingsModule
php artisan lego:install ReportingModule
php artisan lego:install MediaModule

# Step 4: Update config/concord.php
cat > config/concord.php <<EOL
<?php

return [
    'modules' => [
        \\Appetiser\\Auth\\Providers\\ModuleServiceProvider::class,
        \\Appetiser\\Admin\\Providers\\ModuleServiceProvider::class,
        \\Appetiser\\Reporting\\Providers\\ModuleServiceProvider::class,
        \\Appetiser\\Media\\Providers\\ModuleServiceProvider::class,
        \\Appetiser\\AppSettings\\Providers\\ModuleServiceProvider::class,
    ],
    'register_route_models' => true,
];
EOL

# Step 5: Update config/auth.php
sed -i.bak "s|'model' => .*|'model' => Appetiser\\\Auth\\\Models\\\User::class,|" config/auth.php

# Step 6: Run migrations
php artisan migrate

# Step 7: Generate application key
echo "Generating application key..."
php artisan key:generate

# Step 8: Create admin user
php artisan admin:new

# Step 9: Display URLs
APP_URL=$(grep ^APP_URL= .env | cut -d '=' -f2)
APP_URL=${APP_URL:-http://127.0.0.1:8000}

echo ""
echo "Setup complete"
echo "Run: php artisan serve"
echo ""
echo "Installation complete. Your URLs are:"
echo "API:        ${APP_URL}/api/v1"
echo "CMS Admin:  ${APP_URL}/admin"
echo "Telescope:  ${APP_URL}/telescope"