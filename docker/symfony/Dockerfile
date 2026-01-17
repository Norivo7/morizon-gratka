FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip zip libpq-dev curl \
  && docker-php-ext-install pdo pdo_pgsql \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

CMD ["bash", "-lc", "php -S 0.0.0.0:8000 -t public"]