# PHPの公式イメージを使用（PHP 8.2-FPM）
FROM php:8.2-fpm

# 必要な依存パッケージをインストール
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    unzip \
    libmariadb-dev \
    && docker-php-ext-install pdo_mysql \
    && apt-get install -y mysql-client \
    && rm -rf /var/lib/apt/lists/* 

# ワーキングディレクトリの設定
WORKDIR /var/www

# Composerをグローバルインストール
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
