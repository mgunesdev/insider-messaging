#!/bin/bash
set -e

echo ">>> Container starting..."

# Eğer .env yoksa kopyala
if [ ! -f .env ]; then
  cp .env.example .env
  echo ">>> .env file created."
fi

# Laravel key generate (sadece boşsa)
php artisan key:generate --force

# Migration çalıştır
php artisan migrate --force

echo ">>> Ready. Starting Laravel..."
exec "$@"
