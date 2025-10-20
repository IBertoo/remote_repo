FROM php:8.1-apache

# Instalar dependencias necesarias para pdo_pgsql
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql

# Habilitar el módulo de Apache rewrite (opcional, si lo necesitas)
RUN a2enmod rewrite

# Copiar el código de la aplicación
COPY ./php /var/www/html

# Establecer permisos adecuados
RUN chown -R www-data:www-data /var/www/html
