FROM wordpress:php5.6

RUN apt-get update && apt-get install -y less wget subversion mysql-client
RUN apt-get install -y php-pear libyaml-dev && pecl install yaml-1.3.1
RUN echo "extension=yaml.so" > /usr/local/etc/php/conf.d/docker-php-ext-yaml.ini

RUN wget https://phar.phpunit.de/phpunit-5.7.20.phar && \
    chmod +x phpunit-5.7.20.phar && \
    mv phpunit-5.7.20.phar /usr/local/bin/phpunit

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp

