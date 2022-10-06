# FROM composer:1.6.5 as build

# WORKDIR /app
# COPY . /app
# RUN composer install --optimize-autoloader --no-dev

# FROM php:7.3-apache

# RUN apt-get update && apt-get install -y \
#     wget zip unzip git \
#     build-essential g++ \
#     libfreetype6-dev \
#     libjpeg62-turbo-dev \
#     libpng-dev \
#     libicu-dev \
#     libzip-dev

# RUN docker-php-ext-install iconv sockets mbstring mysqli pdo pdo_mysql \
#     && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
#     && docker-php-ext-install gd \
#     && docker-php-ext-install intl \
#     && docker-php-ext-install zip

# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# EXPOSE 80
# COPY --from=build /app /app
# COPY vhost.conf /etc/apache2/sites-available/000-default.conf
# RUN chown -R www-data:www-data /app \
#     && a2enmod rewrite 



FROM php:7.4-fpm

# Set working directory
WORKDIR /var/www

# Add docker php ext repo
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install php extensions
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions mbstring pdo_mysql zip exif pcntl gd memcached

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    unzip \
    git \
    curl \
    lua-zlib-dev \
    libmemcached-dev \
    nginx

#instalar SQL Serve
ENV ACCEPT_EULA=Y
RUN apt-get update && apt-get install -y gnupg2
RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - 
RUN curl https://packages.microsoft.com/config/ubuntu/20.04/prod.list > /etc/apt/sources.list.d/mssql-relea`enter code here`se.list 
RUN apt-get update 
RUN ACCEPT_EULA=Y apt-get -y --no-install-recommends install msodbcsql17 unixodbc-dev 
RUN pecl install sqlsrv
RUN pecl install pdo_sqlsrv
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-enable sqlsrv pdo_sqlsrv pdo pdo_mysql

# Install supervisor
RUN apt-get install -y supervisor

RUN apt-get install -y nodejs
RUN apt-get install -y nano 

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy code to /var/www
COPY --chown=www:www-data . /var/www

# add root to www group
RUN chmod -R ug+w /var/www/storage

# Copy nginx/php/supervisor configs
RUN cp docker/supervisor.conf /etc/supervisord.conf
#RUN cp docker/php.ini /usr/local/etc/php/conf.d/app.ini
RUN cp docker/nginx.conf /etc/nginx/sites-enabled/default

# PHP Error Log Files
RUN mkdir /var/log/php
RUN touch /var/log/php/errors.log && chmod 777 /var/log/php/errors.log

#copy.env
#COPY docker/.env.dev /var/www/html/.env

# Deployment steps
RUN composer install --optimize-autoloader --no-dev
RUN chmod +x /var/www/docker/run.sh


EXPOSE 80
ENTRYPOINT ["/var/www/docker/run.sh"]