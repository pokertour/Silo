# --------------------------------------------------------------------------
# Stage 1: Asset Build (Node)
# --------------------------------------------------------------------------
FROM node:22-alpine AS asset-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# --------------------------------------------------------------------------
# Stage 2: Runtime (PHP 8.5)
# --------------------------------------------------------------------------
FROM dunglas/frankenphp:1-php8.5-alpine AS runner

# Install required PHP extensions
RUN install-php-extensions \
    pdo_mysql \
    pdo_sqlite \
    bcmath \
    gd \
    intl \
    zip \
    opcache \
    pcntl \
    redis \
    ftp

# Configure PHP for production and unlimited uploads
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install Supervisor and Redis Server for the monolithic setup
RUN apk add --no-cache supervisor redis

WORKDIR /app

# Copy application files
COPY . .

# Install Composer dependencies cleanly
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN CACHE_STORE=array composer install --no-dev --optimize-autoloader

# Copy built assets from builder stage
COPY --from=asset-builder /app/public/build ./public/build

# Copy custom config files
COPY custom.ini $PHP_INI_DIR/conf.d/
COPY supervisord.conf /etc/supervisord.conf

# Ensure storage directories exist and set permissions
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Environment variables
# Valeurs par défaut de l'application : le .env n'a besoin que de l'essentiel
# (APP_KEY, APP_URL, mots de passe de la base). Toute variable passée au
# conteneur (env_file / environment de docker-compose) reste prioritaire.
ENV APP_NAME="Silo" \
    APP_ENV="production" \
    APP_DEBUG="false" \
    APP_LOCALE="en" \
    APP_FALLBACK_LOCALE="en" \
    LOG_CHANNEL="daily" \
    LOG_LEVEL="warning" \
    LOG_DAILY_DAYS="14" \
    DB_CONNECTION="mysql" \
    DB_HOST="db" \
    DB_PORT="3306" \
    DB_DATABASE="silo" \
    DB_USERNAME="isouser" \
    REDIS_CLIENT="phpredis" \
    REDIS_HOST="127.0.0.1" \
    REDIS_PORT="6379" \
    CACHE_STORE="redis" \
    SESSION_DRIVER="redis" \
    SESSION_LIFETIME="120" \
    SESSION_SECURE_COOKIE="true" \
    QUEUE_CONNECTION="redis" \
    BROADCAST_CONNECTION="log" \
    FILESYSTEM_DISK="local" \
    MAIL_MAILER="log"
ENV SERVER_NAME=":8080"
ENV FRANKENPHP_CONFIG="import /app/Caddyfile"

ARG APP_VERSION=dev
ENV APP_VERSION=${APP_VERSION}

# Expose the correct port
EXPOSE 8080

# Setup entrypoint for automatic migrations and caching
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]

# The default command will start Supervisor to manage all processes
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
