FROM php:7.4-fpm

# Instala dependencias del sistema
RUN apt-get update && apt-get install -y \
    libldap2-dev \
    libzip-dev \
    zip \
    mysql-client \  # Instala el cliente de MySQL
    && rm -rf /var/lib/apt/lists/*

# Instala extensiones de PHP
RUN docker-php-ext-install pdo_mysql ldap zip

# Copia el script SQL
COPY init.sql /tmp/init.sql

# Ejecuta el script SQL (opcional, si quieres que se ejecute al construir la imagen)
RUN mysql -h db -u root -prootpassword taskhive < /tmp/init.sql

# Configura el directorio de trabajo
WORKDIR /var/www/html