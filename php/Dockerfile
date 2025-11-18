# Usamos imagen oficial con Apache y PHP
FROM php:8.2-apache

# Instalar extensiones necesarias para PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite si fuera necesario
#RUN a2enmod rewrite

# Establecer dir de trabajo
WORKDIR /var/www/html

# =================================================================
# AQUÍ VIENEN LOS CAMBIOS CLAVE PARA SUBIR ARCHIVOS GRANDES
# =================================================================

# 1. Sobrescribe directamente los valores de php.ini en una sola línea
RUN echo "upload_max_filesize = 100M\n\
post_max_size = 110M\n\
memory_limit = 256M\n\
max_execution_time = 600\n\
max_input_time = 600\n\
file_uploads = On" > /usr/local/etc/php/conf.d/uploads.ini

COPY ./php /var/www/html
RUN chown -R www-data:www-data /var/www/html
# Copiar composer si lo necesitas (opcional)
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar archivos de configuración de Apache si tuvieras (opcional)
# COPY docker/php/vhost.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
CMD ["apache2-foreground"]
