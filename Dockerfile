# Use the official PHP 8.5 image with Apache
FROM php:8.5-apache

# Install system dependencies for cURL and PostgreSQL
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql curl

# Set the web root to public_html
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public_html

# Update Apache configuration
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf* \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Enable Apache rewrite
RUN a2enmod rewrite

# Copy the repo into the container
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

EXPOSE 80