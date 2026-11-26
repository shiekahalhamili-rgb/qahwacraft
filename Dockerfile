# Container used by Render: PHP 8.3 with Apache and the MySQL driver.
FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && echo "date.timezone=Asia/Riyadh" > /usr/local/etc/php/conf.d/timezone.ini

COPY . /var/www/html/
RUN rm -rf /var/www/html/Dockerfile /var/www/html/README.md /var/www/html/.git

# Render tells the app which port to use in the PORT variable (80 when it is not set).
CMD sed -i "s/^Listen .*/Listen ${PORT:-80}/" /etc/apache2/ports.conf \
    && sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT:-80}>/" /etc/apache2/sites-enabled/000-default.conf \
    && apache2-foreground
