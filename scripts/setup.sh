#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
export DOCKER_CONFIG="${DOCKER_CONFIG:-$PWD/.runtime/docker}"
mkdir -p "$DOCKER_CONFIG"
test -f .env || cp .env.example .env
if ! docker image inspect orginventory-php >/dev/null 2>&1; then
  docker compose build app
  python3 scripts/flatten-runtime.py
fi
docker compose up -d --wait db
# Reapply development test grants without resetting existing data.
docker compose exec -T db sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql -uroot' < docker/mysql-init.sql
docker run --rm --network host --user "$(id -u):$(id -g)" \
  -e HTTP_PROXY -e HTTPS_PROXY -e http_proxy -e https_proxy -e NO_PROXY -e no_proxy \
  -e COMPOSER_HOME=/tmp/composer \
  -v "$PWD:/app" \
  -v /etc/ssl/certs/ca-certificates.crt:/etc/ssl/certs/ca-certificates.crt:ro \
  orginventory-php composer install --no-interaction --prefer-dist
docker compose run --rm app php scripts/ensure-key.php
docker compose run --rm app php artisan migrate --force
test -L public/storage || docker compose run --rm app php artisan storage:link
npm ci --cache "${NPM_CONFIG_CACHE:-$PWD/.runtime/npm-cache}"
npm run build
docker compose up -d app queue scheduler
