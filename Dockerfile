FROM heroku/heroku:22-build as build

WORKDIR /app
COPY . .

RUN curl --silent --show-error --fail --location \
      --header "accept-encoding: gzip" \
      -o /tmp/heroku-php-apache2.tar.gz \
      "https://lang-php-apache2-prod.s3.us-east-1.amazonaws.com/heroku-php-apache2-7.2.tar.gz" && \
    tar xzf /tmp/heroku-php-apache2.tar.gz -C / && \
    rm /tmp/heroku-php-apache2.tar.gz

RUN composer install --no-dev --optimize-autoloader

# Production image
FROM heroku/heroku:22

WORKDIR /app
COPY --from=build /app /app
COPY --from=build /usr/local/bin /usr/local/bin
COPY --from=build /usr/local/lib /usr/local/lib

RUN mkdir -p /app/storage/logs && \
    mkdir -p /app/bootstrap/cache && \
    chmod -R 755 /app/storage && \
    chmod -R 755 /app/bootstrap/cache

EXPOSE 80

CMD vendor/bin/heroku-php-apache2 public/
