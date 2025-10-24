FROM php:8.2-apache

# ติดตั้ง mysqli extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# เปิด mod_rewrite ถ้าต้องการใช้กับ .htaccess
RUN a2enmod rewrite

# ตั้ง working directory
WORKDIR /var/www/html
