FROM php:7.3-apache

# PHP configurations
# Remote File Inclusion
RUN echo "allow_url_include = On" >> /usr/local/etc/php/conf.d/rfi.ini 
# Display errors for debug
RUN echo "display_errors = On" >> /usr/local/etc/php/conf.d/error.ini

WORKDIR /var/www/html

# Copy php files
COPY web/www/ .
COPY web/internal/ internal/

# Copy start script
COPY start.sh ./start.sh
RUN chmod +x ./start.sh

EXPOSE 8080

CMD ["./start.sh"]