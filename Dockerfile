FROM php:8.4-apache

# Habilitar módulo rewrite de Apache
RUN a2enmod rewrite

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    openssh-server \
    supervisor \
    libzip-dev \
    libicu-dev  # Añadir esta línea para intl

# Instalar extensiones PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar SSH
RUN mkdir -p /run/sshd
RUN echo 'PermitRootLogin yes' >> /etc/ssh/sshd_config
RUN echo 'PasswordAuthentication yes' >> /etc/ssh/sshd_config

# Crear usuario para SSH
RUN useradd -m -s /bin/bash developer
RUN echo "developer:password" | chpasswd

# Configurar Apache para Laravel
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
        Options Indexes FollowSymLinks\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Optimizar Apache
RUN echo 'ServerTokens Prod' >> /etc/apache2/apache2.conf
RUN echo 'ServerSignature Off' >> /etc/apache2/apache2.conf
RUN echo 'KeepAlive On' >> /etc/apache2/apache2.conf
RUN echo 'MaxKeepAliveRequests 100' >> /etc/apache2/apache2.conf
RUN echo 'KeepAliveTimeout 5' >> /etc/apache2/apache2.conf

# Optimizar PHP para desarrollo
RUN { \
        echo 'memory_limit = 256M'; \
        echo 'max_execution_time = 120'; \
        echo 'upload_max_filesize = 20M'; \
        echo 'post_max_size = 20M'; \
        echo 'display_errors = On'; \
        echo 'display_startup_errors = On'; \
        echo 'error_reporting = E_ALL'; \
    } > /usr/local/etc/php/conf.d/dev-php.ini

# Configurar Supervisor para ejecutar SSH y Apache
RUN echo '[supervisord]\n\
nodaemon=true\n\
\n\
[program:apache2]\n\
command=apache2-foreground\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
\n\
[program:sshd]\n\
command=/usr/sbin/sshd -D\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0' > /etc/supervisor/conf.d/supervisord.conf

# Directorio de trabajo
WORKDIR /var/www/html

# Instalar Node.js y npm
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs

# Instalar herramientas de desarrollo y pruebas
RUN apt-get install -y htop apache2-utils siege vim

# Después de copiar la aplicación o montar el volumen
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer puertos
EXPOSE 80 22 5173

# Añadir
COPY docker/scripts/optimize.sh /usr/local/bin/optimize
RUN chmod +x /usr/local/bin/optimize

# Configurar OPCache
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.validate_timestamps=1'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.fast_shutdown=1'; \
} > /usr/local/etc/php/conf.d/opcache-dev.ini

# Optimizar Apache para desarrollo
RUN { \
    echo '<IfModule mpm_prefork_module>'; \
    echo '    StartServers            5'; \
    echo '    MinSpareServers         5'; \
    echo '    MaxSpareServers        10'; \
    echo '    MaxRequestWorkers     150'; \
    echo '    MaxConnectionsPerChild   0'; \
    echo '</IfModule>'; \
} > /etc/apache2/mods-available/mpm_prefork.conf

RUN a2dismod mpm_event && a2enmod mpm_prefork

# Iniciar servicios con Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
