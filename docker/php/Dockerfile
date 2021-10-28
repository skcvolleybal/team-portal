FROM joomla:3.10.3-php7.4

RUN \
  pecl install xdebug-3.1.1 && \
  docker-php-ext-enable xdebug && \
  rm $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini && \
  { \
  echo "zend_extension=$(find /usr/local/lib/php/ -name xdebug.so)"; \
  echo "xdebug.mode=debug"; \
  echo "xdebug.start_with_request=yes"; \
  echo "xdebug.client_host=host.docker.internal"; \  
  } > "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini";  