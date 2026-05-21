FROM php:8.3-apache-bookworm
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

ENV VIRTUAL_ENV=/opt/ocr-venv
ENV PATH="${VIRTUAL_ENV}/bin:${PATH}"
ENV PIP_REQUIRE_HASHES=0
RUN python3 -m venv "${VIRTUAL_ENV}" \
    && python -m pip install --no-cache-dir --upgrade pip setuptools wheel \
    && python -m pip --version

# Fix MPM conflict: hapus symlink event & worker secara langsung (lebih reliable dari a2dismod)
RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf \
           /etc/apache2/mods-enabled/mpm_event.load \
           /etc/apache2/mods-enabled/mpm_worker.conf \
           /etc/apache2/mods-enabled/mpm_worker.load
RUN a2enmod mpm_prefork rewrite

COPY . .

RUN npm install && npm run prod

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Install Python dependencies for OCR service
RUN pip install --no-cache-dir --retries 10 --timeout 120 --index-url https://pypi.org/simple -r ocr_service/requirements.txt

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
RUN chmod +x /app/start.sh

EXPOSE 80
CMD ["/app/start.sh"]
