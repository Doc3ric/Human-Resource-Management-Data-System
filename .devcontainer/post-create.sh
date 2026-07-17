#!/usr/bin/env bash
set -e

cd /workspace

if [ ! -f .env ]; then
  cp .env.example .env
fi

# Point the app at the devcontainer's MySQL service instead of localhost
sed -i 's/^DB_HOST=.*/DB_HOST=db/' .env
sed -i 's/^DB_DATABASE=.*/DB_DATABASE=csc_plantilla/' .env
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=root/' .env
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=/' .env

composer install --no-interaction

php artisan key:generate --ansi

echo "Waiting for MySQL to accept connections..."
until mysqladmin ping -h db --silent; do
  sleep 2
done

php artisan migrate --force
php artisan storage:link || true

npm install
npm run build

echo "Devcontainer setup complete. Run 'composer run dev' to start Laravel + Vite."
