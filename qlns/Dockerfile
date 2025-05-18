# --- Giai đoạn 1: PHP Dependencies & Application Code ---
# Sử dụng base image PHP chính thức với FPM và Alpine Linux (nhẹ)
# Thay '8.2' bằng phiên bản PHP bạn đang sử dụng (ví dụ: 8.1, 8.3)
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

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Sao chép các file composer trước để tận dụng Docker layer caching
COPY composer.json composer.lock ./

# Cài đặt dependencies của PHP cho production
RUN composer install --no-interaction --no-dev --no-plugins --no-scripts --prefer-dist --optimize-autoloader

# Sao chép toàn bộ mã nguồn ứng dụng
COPY . .

# Chạy các lệnh Artisan để tối ưu hóa cho production
RUN cp .env.example .env \
    && php artisan key:generate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan package:discover

# Xóa các file không cần thiết cho production image
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
    # Các thư viện runtime (không có -dev) sẽ được tự động cài đặt như là dependency của các gói -dev trên
    # Hoặc bạn có thể liệt kê rõ ràng: libpng libjpeg-turbo freetype libwebp icu-libs libzip zlib
    && rm -rf /var/cache/apk/*

# Cài đặt các PHP extensions cần thiết (lặp lại từ stage vendor nếu cần build lại trong stage này)
# Nếu bạn muốn image nhỏ hơn, bạn có thể COPY các file .so đã biên dịch từ stage 'vendor'
# thay vì chạy lại docker-php-ext-install. Nhưng để đơn giản và đảm bảo, chúng ta cài lại.
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

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Sao chép mã nguồn đã xử lý (bao gồm thư mục vendor) từ giai đoạn 'vendor'
COPY --from=vendor /var/www/html .

# Thiết lập quyền sở hữu và quyền ghi cho thư mục storage và bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Mở cổng 9000 cho PHP-FPM
EXPOSE 9000

# Lệnh mặc định để chạy PHP-FPM
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