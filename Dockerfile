# Gunakan image dasar PHP 8.3 dengan Apache
FROM php:8.3-apache

# Set metadata
LABEL maintainer="TernakApp Team"
LABEL description="Containerized environment for TernakApp (SI-PHT)"

# Install dependensi sistem dan library untuk ekstensi PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        mysqli \
        pdo_mysql \
        gd \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Aktifkan modul Apache rewrite dan headers
RUN a2enmod rewrite headers

# Atur konfigurasi PHP
RUN echo "upload_max_filesize = 32M\n\
post_max_size = 32M\n\
memory_limit = 256M\n\
max_execution_time = 300\n\
date.timezone = Asia/Jakarta\n" > /usr/local/etc/php/conf.d/custom.ini

# Atur direktori kerja
WORKDIR /var/www/html

# Salin kode aplikasi ke direktori web root Apache
COPY . /var/www/html/

# Atur kepemilikan dan hak akses untuk direktori upload
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/images

# Expose port HTTP Apache
EXPOSE 80

# Jalankan Apache di foreground
CMD ["apache2-foreground"]
