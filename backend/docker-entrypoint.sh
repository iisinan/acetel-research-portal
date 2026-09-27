#!/bin/sh
set -e

echo "=== ACETEL TMS Entrypoint ==="

# ── Storage directories ──────────────────────────────────────────
mkdir -p storage/framework/views \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/logs \
         bootstrap/cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# ── Preserve .env ────────────────────────────────────────────────
touch .env

# ── Clear cached bootstrap files ────────────────────────────────
rm -f bootstrap/cache/config.php \
      bootstrap/cache/routes.php \
      bootstrap/cache/services.php \
      bootstrap/cache/packages.php 2>/dev/null || true

# ── APP_KEY diagnostics ─────────────────────────────────────────
if [ -n "$APP_KEY" ]; then
    echo "APP_KEY: found (${#APP_KEY} chars)"
else
    echo "WARNING: APP_KEY missing — generating…"
    php artisan key:generate --force
fi

# ── Artisan clears ───────────────────────────────────────────────
php artisan config:clear  || true
php artisan route:clear   || true
php artisan view:clear    || true

# ── Schema initialization ────────────────────────────────────────
if [ -n "$DB_SCHEMA" ] && [ "$DB_SCHEMA" != "public" ]; then
    echo "=== Ensuring PostgreSQL schema '$DB_SCHEMA' exists ==="
    php -r "\
        \$pdo = new PDO(getenv('DATABASE_URL') ?: 'pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));\
        \$pdo->exec('CREATE SCHEMA IF NOT EXISTS "' . getenv('DB_SCHEMA') . '";');\
    " || echo "WARN: Failed to create schema $DB_SCHEMA"
fi


# ── Migrations ───────────────────────────────────────────────────
echo "=== Running migrations ==="
php artisan migrate --force || echo "WARN: Migration failed — check DB connection"

# ── Production caching ───────────────────────────────────────────
echo "=== Caching config, routes, views, and events ==="
php artisan config:cache || echo "WARN: Config cache failed"
php artisan route:cache  || echo "WARN: Route cache failed"
php artisan view:cache   || echo "WARN: View cache failed"
php artisan event:cache  || echo "WARN: Event cache failed"

# ── Test whether Redis is actually reachable ─────────────────────
echo "=== Testing Redis connection ==="
php -r "
try {
    \$redis = new Redis();
    \$host = getenv('REDIS_HOST') ?: '127.0.0.1';
    \$port = intval(getenv('REDIS_PORT') ?: 6379);
    \$scheme = (getenv('REDIS_TLS') === 'true') ? 'tls://' : '';
    \$redis->connect(\$scheme . \$host, \$port);
    if (getenv('REDIS_PASSWORD') && getenv('REDIS_PASSWORD') !== 'null') {
        \$redis->auth(getenv('REDIS_PASSWORD'));
    }
    \$redis->ping();
    echo 'Redis: CONNECTED' . PHP_EOL;
} catch (Exception \$e) {
    echo 'Redis: UNAVAILABLE — ' . \$e->getMessage() . PHP_EOL;
}
" 2>/dev/null || echo "Redis test skipped"

# ── Start Queue Worker (Background) ──
echo "=== Starting Background Queue Worker ==="
php artisan queue:work --sleep=3 --tries=3 &

# ── Start Laravel Octane ─────────────────────────────────────────
echo "=== Starting application server (Laravel Octane + FrankenPHP) ==="
export PORT="${PORT:-8000}"
exec php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=$PORT --workers=auto --task-workers=auto --max-requests=500
