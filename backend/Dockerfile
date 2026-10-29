FROM php:8.2-apache
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN a2enmod rewrite headers
# Enable .htaccess overrides for URL rewriting and configure DirectoryIndex
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
RUN echo "DirectoryIndex index.php menu.php index.html" >> /etc/apache2/apache2.conf

# Copy admin directory files as initial content
COPY ./admin /var/www/html/
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html
