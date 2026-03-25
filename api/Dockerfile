FROM dunglas/frankenphp:latest-php8.2
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*
RUN install-php-extensions \
    zip \
    pdo_mysql \
    intl \
    bcmath
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app