# Dockerfile

# Usa la imagen oficial de PHP 7.4 con FPM
FROM php:7.4-fpm

# Instala el controlador PDO para MySQL
RUN docker-php-ext-install pdo_mysql

# Copia los archivos de la aplicación (opcional, ya que usamos volúmenes en docker-compose)
COPY . /var/www/html

# Establece el directorio de trabajo
WORKDIR /var/www/html