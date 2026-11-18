FROM php:8.3-apache
RUN apt-get update \
 && apt-get install -y --no-install-recommends libpq-dev libcurl4-openssl-dev \
 && docker-php-ext-install pdo_pgsql curl \
 && rm -rf /var/lib/apt/lists/* \
 && a2enmod rewrite
WORKDIR /var/www/fruta_madre
COPY . /var/www/fruta_madre
RUN cp /var/www/fruta_madre/app/config/database.example.php /var/www/fruta_madre/app/config/database.php \
 && chown -R www-data:www-data /var/www/fruta_madre
COPY integracion/render-apache.conf /etc/apache2/sites-available/000-default.conf
COPY integracion/render-start.sh /usr/local/bin/render-start
RUN chmod +x /usr/local/bin/render-start
EXPOSE 10000
CMD ["render-start"]
