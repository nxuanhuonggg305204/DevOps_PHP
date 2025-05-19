# --- Giai đoạn 1: PHP Dependencies & Application Code ---
FROM php:8.2-fpm-alpine AS vendor

# Cài đặt các gói hệ thống cần thiết cho việc build và các PHP extensions
RUN apk add --no-cache \
    build-base \
    curl \
    git \
    unzip \
    zip \
    libzip-dev \
    zlib-dev \
    libpng-dev libjpeg-turbo-dev freetype-dev libwebp-dev \
    icu-dev \
    oniguruma-dev \
    && rm -rf /var/cache/apk/*

# Cài đặt các PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    gd \
    intl \
    mbstring \
    exif \
    pcntl \
    pdo pdo_mysql \
    zip \
    bcmath

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

# Cài đặt dependencies của PHP cho production, không chạy scripts tự động của composer ở bước này
RUN composer install --no-interaction --no-dev --no-plugins --no-scripts --prefer-dist --optimize-autoloader

# Sao chép toàn bộ mã nguồn ứng dụng
COPY . .

# Tạo file .env từ .env.example và đảm bảo APP_ENV=production, APP_DEBUG=false
RUN cp .env.example .env \
    && sed -i 's/APP_ENV=local/APP_ENV=production/g' .env \
    && sed -i 's/APP_DEBUG=true/APP_DEBUG=false/g' .env \
    # Đảm bảo có dòng APP_ENV và APP_DEBUG nếu chưa có
    && grep -qxF 'APP_ENV=production' .env || echo 'APP_ENV=production' >> .env \
    && grep -qxF 'APP_DEBUG=false' .env || echo 'APP_DEBUG=false' >> .env

# Chạy các lệnh Artisan để tối ưu hóa cho production
# Trình tự quan trọng: tạo key, dump-autoload (không chạy scripts), dọn dẹp cache kỹ lưỡng,
# sau đó mới khám phá package, rồi cache lại mọi thứ.
RUN php artisan key:generate --force \
    # Dump autoload một cách tường minh cho production, không chạy scripts (bao gồm cả package:discover ở post-autoload-dump)
    && composer dump-autoload --optimize --no-dev --classmap-authoritative --no-scripts \
    # Xóa tất cả các loại cache của Laravel một cách triệt để
    && php artisan optimize:clear \
    && php artisan event:clear \
    && php artisan view:clear \
    && php artisan route:clear \
    && php artisan config:clear \
    && php artisan cache:clear \
    # Bây giờ mới chạy package:discover, nó sẽ dựa trên autoloader "sạch" (không có dev dependencies)
    # và không bị ảnh hưởng bởi cache cũ.
    && php artisan package:discover \
    # Cuối cùng, cache lại mọi thứ cho production
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan event:cache


# Xóa file .env tạm thời đã dùng để build
RUN rm .env

# --- Giai đoạn 2: PHP-FPM Image Cuối cùng ---
FROM php:8.2-fpm-alpine AS app_php_fpm

# Cài đặt các gói cần thiết cho runtime VÀ build-time cho extensions trong stage này
RUN apk add --no-cache \
    build-base \
    libzip-dev \
    zlib-dev \
    libpng-dev libjpeg-turbo-dev freetype-dev libwebp-dev \
    icu-dev \
    oniguruma-dev \
    && rm -rf /var/cache/apk/*

# Cài đặt các PHP extensions cần thiết
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    gd \
    intl \
    mbstring \
    exif \
    pcntl \
    pdo pdo_mysql \
    zip \
    bcmath

WORKDIR /var/www/html

COPY --from=vendor /var/www/html .

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]

# --- Giai đoạn 3: Web Server (Nginx) ---
FROM nginx:1.25-alpine AS app_nginx

WORKDIR /var/www/html
RUN rm /etc/nginx/conf.d/default.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app_php_fpm /var/www/html/public /var/www/html/public
RUN chown -R nginx:nginx /var/www/html/public && \
    chmod -R 755 /var/www/html/public
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]