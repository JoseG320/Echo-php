FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Suppress the ServerName warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy app files into Apache's web root
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html