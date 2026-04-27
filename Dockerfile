FROM php:8.3-apache
WORKDIR /app

RUN apt-get update && apt-get install -y \
    git curl zip unzip nodejs npm \
    libonig-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libxml2-dev libssl-dev libicu-dev \
    python3 python3-pip python3-venv \
    tesseract-ocr tesseract-ocr-ind tesseract-ocr-eng \
    poppler-utils \
    # Dependency untuk opencv-python-headless
    libglib2.0-0 libsm6 libxrender1 libxext6 libgl1 \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo pdo_mysql mbstring gd zip xml bcmath opcache intl exif pcntl

# Nonaktifkan MPM event & worker saat BUILD agar tidak bentrok saat runtime
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true
RUN a2enmod mpm_prefork
RUN a2enmod rewrite

COPY . .

RUN npm install && npm run prod

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Install Python dependencies for OCR service
RUN pip3 install --no-cache-dir --break-system-packages -r ocr_service/requirements.txt || \
    pip3 install --no-cache-dir -r ocr_service/requirements.txt

RUN mkdir -p storage/logs bootstrap/cache && chmod -R 755 storage bootstrap/cache

# .env sudah dikopi dari COPY . . di atas (tidak perlu copy .env.example)
RUN php artisan key:generate --no-interaction 2>/dev/null || true

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
CMD bash -c "sed -i \"s/80/${PORT:-80}/g\" /etc/apache2/ports.conf && sed -i \"s/:80>/:${PORT:-80}>/g\" /etc/apache2/sites-available/000-default.conf && php artisan migrate --force --no-interaction && apache2-foreground"