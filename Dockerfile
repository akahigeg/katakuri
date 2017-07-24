FROM wordpress:php5.6

RUN apt-get update && apt-get install -y less
RUN apt-get install -y php-pear libyaml-dev && pecl install yaml
RUN echo "extension=yaml.so" > /usr/local/etc/php/conf.d/docker-php-ext-yaml.ini

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp
