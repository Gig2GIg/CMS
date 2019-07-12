FROM composer as vendor
WORKDIR /app
COPY database/ database/
ADD composer.json composer.lock ./
RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

FROM node:lts-alpine as frontend
RUN mkdir -p /app/public
COPY package.json webpack.mix.js tailwind.js /app/
COPY resources/ /app/resources/
WORKDIR /app
RUN yarn install && yarn production

FROM php:7.2-apache
RUN apt-get update && apt-get install -y libssl-dev zlib1g-dev zip libxml2-dev supervisor cron
RUN docker-php-ext-install zip pdo pdo_mysql mbstring tokenizer ctype json bcmath
RUN a2enmod rewrite headers
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
ENV PORT=8080
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN sed -s -i -e "s/80/${PORT}/" /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf
WORKDIR /var/www/html
USER www-data
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=vendor /app/vendor/ ./vendor
COPY --chown=www-data:www-data --from=frontend /app/public/js/ ./public/js/
COPY --chown=www-data:www-data --from=frontend /app/public/css/ ./public/css/
COPY --chown=www-data:www-data --from=frontend /app/mix-manifest.json ./mix-manifest.json
RUN chmod 777 bootstrap/cache -R
EXPOSE 8080

