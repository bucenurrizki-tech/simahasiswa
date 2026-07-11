FROM php:8.2-apache

# Ekstensi PDO MySQL untuk koneksi database
RUN docker-php-ext-install pdo_mysql

# Salin kode aplikasi
COPY . /var/www/html/

# Folder upload foto harus bisa ditulis oleh Apache
RUN mkdir -p /var/www/html/assets/uploads \
    && chown -R www-data:www-data /var/www/html/assets/uploads
