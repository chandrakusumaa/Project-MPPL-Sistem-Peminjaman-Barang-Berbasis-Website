FROM composer:2.8 AS composer
FROM webdevops/php:8.4@sha256:a48bcc09dd6fa69716d3272c63a8f18d710480aa19ec61a1af59ff5a7bc6145c
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
ENTRYPOINT []
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
