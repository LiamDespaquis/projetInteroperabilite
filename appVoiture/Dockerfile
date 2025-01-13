FROM canals/php
RUN apt-get update && apt-get install -y libxslt-dev
RUN docker-php-ext-install xsl
COPY php.ini /usr/local/etc/php/

