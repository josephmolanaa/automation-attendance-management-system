FROM php:8.3-apache
WORKDIR /app

RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libonig-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libxml2-dev libssl-dev libicu-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo pdo_mysql mbstring gd zip xml bcmath opcache intl exif pcntl

RUN a2enmod rewrite

COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

RUN mkdir -p storage/logs bootstrap/cache && chmod -R 755 storage bootstrap/cache

COPY .env.example .env
RUN php artisan key:generate

RUN sed -i 's|/var/www/html|/app/public|g' /etc/apache2/sites-available/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN echo '<Directory /app/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

RUN chmod -R 755 /app/public
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

EXPOSE 80
CMD bash -c "a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork && sed -i \"s/80/${PORT:-80}/g\" /etc/apache2/ports.conf && sed -i \"s/:80>/:${PORT:-80}>/g\" /etc/apache2/sites-available/000-default.conf && php artisan migrate --force && apache2-foreground"