FROM php:8.2-fpm-alpine AS vendor
RUN apk add --no-cache \
    build-base \
    curl \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev libjpeg-turbo-dev freetype-dev libwebp-dev \
    icu-dev \
    oniguruma-dev \
    && rm -rf /var/cache/apk/*
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
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Sao chép các file composer trước để tận dụng Docker layer caching
COPY composer.json composer.lock ./

# Sao chép toàn bộ mã nguồn ứng dụng
COPY . .
# BIẾN MÔI TRƯỜNG THỰC TẾ SẼ ĐƯỢC INJECT KHI RUNTIME.
RUN cp .env.example .env \
    && php artisan key:generate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    # Chạy package:discover sau khi đã có .env và key (nếu cần)
    && php artisan package:discover
RUN rm .env

# --- Giai đoạn 2: PHP-FPM Image Cuối cùng ---
# Sử dụng lại base image PHP-FPM Alpine để giữ image nhỏ gọn
FROM php:8.2-fpm-alpine AS app_php_fpm
RUN apk add --no-cache \
    # GD cho xử lý hình ảnh
    libpng libjpeg-turbo freetype libwebp \
    # Intl cho quốc tế hóa
    icu-libs \
    # Các extensions khác
    libzip \
    # Xóa cache của apk sau khi cài đặt
    && rm -rf /var/cache/apk/*
# Lưu ý: các thư viện -dev đã được cài ở stage 'vendor' để build, ở đây chỉ cần thư viện runtime
RUN docker-php-ext-install -j$(nproc) \
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
# www-data là người dùng mặc định mà PHP-FPM chạy
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache
# Mở cổng 9000 cho PHP-FPM
EXPOSE 9000

# Lệnh mặc định để chạy PHP-FPM
CMD ["php-fpm"]

# --- Giai đoạn 3: Web Server (Nginx) ---
FROM nginx:1.25-alpine AS app_nginx
WORKDIR /var/www/html

# Xóa cấu hình Nginx mặc định
RUN rm /etc/nginx/conf.d/default.conf

# Sao chép file cấu hình Nginx tùy chỉnh cho Laravel
# Bạn cần tạo file này trong dự án, ví dụ: docker/nginx/default.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
# Nginx chỉ cần phục vụ các file tĩnh từ thư mục public
COPY --from=app_php_fpm /var/www/html/public /var/www/html/public

# Thiết lập quyền sở hữu cho thư mục mã nguồn (để Nginx có thể đọc)
RUN chown -R nginx:nginx /var/www/html/public && \
    chmod -R 755 /var/www/html/public

# Mở cổng 80 cho Nginx
EXPOSE 80

# Lệnh mặc định để chạy Nginx ở foreground
CMD ["nginx", "-g", "daemon off;"]
