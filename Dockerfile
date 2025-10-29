# Use the official PHP Apache image
FROM php:8.1-apache

# Copy all project files to the web root
COPY . /var/www/html/

# Expose port 80
EXPOSE 80
