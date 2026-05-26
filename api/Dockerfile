FROM dunglas/frankenphp:latest-php8.2

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    && curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get install -y symfony-cli \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    zip \
    pdo_mysql \
    intl \
    bcmath \
    opcache \
    apcu

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app