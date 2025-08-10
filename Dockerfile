FROM php:8.3.0-cli

WORKDIR /app

COPY web/ /app/

RUN php -v && ldd --version

EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/app"]
