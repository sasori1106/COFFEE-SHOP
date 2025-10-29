# Use the official PHP + Apache image
FROM php:8.2-apache

# Copy all project files to Apache's web root
COPY . /var/www/html/

# Enable mod_rewrite (optional, but helps if you use .htaccess)
RUN a2enmod rewrite

# Expose port 80 for web traffic
EXPOSE 80
