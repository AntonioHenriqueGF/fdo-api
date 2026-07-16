FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    supervisor \
    && docker-php-ext-install pdo_mysql pcntl sockets \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --prefer-dist

COPY . .

RUN mkdir -p /var/log/supervisor

EXPOSE 8080 8081

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
