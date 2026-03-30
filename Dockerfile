FROM php:8.3-apache

# Activar mod_rewrite
RUN a2enmod rewrite headers

# Dependencias del sistema + extensiones PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip \
    && rm -rf /var/lib/apt/lists/*

# Asegurar que $_ENV recibe las variables de entorno del contenedor
RUN echo "variables_order = EGPCS" > /usr/local/etc/php/conf.d/env-vars.ini

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar la aplicación (vendor/ excluido por .dockerignore)
COPY . .

# Instalar dependencias PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Apuntar DocumentRoot a public/
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf && \
    sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' \
    /etc/apache2/apache2.conf

# Crear directorios necesarios y establecer permisos correctos
RUN mkdir -p uploads storage && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 750 uploads storage

EXPOSE 80
