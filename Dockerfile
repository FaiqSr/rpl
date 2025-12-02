FROM php:8.1-fpm

# System deps
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    gnupg \
    ca-certificates \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mbstring pdo pdo_mysql xml zip bcmath

# Install Composer (copy from official composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Node.js (for building assets)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get update && apt-get install -y nodejs && npm install -g npm@stable \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock ./

# Install PHP deps
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev || true

# Copy remaining application files
COPY . .

# Install node modules and build assets (optional; can be skipped if building assets elsewhere)
RUN if [ -f package.json ]; then npm install && npm run build || true; fi

# Set permissions for Laravel writable dirs
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache || true

EXPOSE 9000

CMD ["php-fpm"]
