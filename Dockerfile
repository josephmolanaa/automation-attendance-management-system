FROM php:8.3-apache

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql mbstring

RUN a2enmod rewrite

COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-dev --optimize-autoloader

RUN mkdir -p storage/logs bootstrap/cache && \
    chmod -R 755 storage bootstrap/cache

COPY .env.example .env

RUN php artisan key:generate

RUN sed -i 's|/var/www/html|/app/public|g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD php artisan migrate --force; apache2-foreground
