SHELL := /bin/bash

all: docker composer migrate

composer-update:
	docker-compose exec php /bin/bash -lc "/root/.bin/composer update -ovn --prefer-source"

composer:
	docker-compose exec php /bin/bash -lc "/root/.bin/composer install -ovn --prefer-source"

sql:
ifeq (, $(shell which mysqlsh))
	docker-compose exec mariadb mysql -u local -plocal root
else
	mysqlsh -u local -plocal -h 127.0.0.1 --database=root --sql
endif

redis:
	docker-compose exec redis redis-cli

php:
	docker-compose exec php php -a

migrate:
	docker-compose exec php ./yii migrate/up --interactive=0

docker: down tls
	docker-compose pull --parallel
ifeq ($(REBUILD), true)
	docker-compose up -d --remove-orphans --force-recreate
else
	docker-compose up -d --remove-orphans
endif
	
	docker-compose exec php /bin/bash -lc "if grep -r 'host.docker.internal' /etc/php/7.2/conf.d/xdebug.ini; then echo 'XDebug Remote host is already defined'; else echo xdebug.remote_host=host.docker.internal | tee -a /etc/php/7.2/conf.d/xdebug.ini; fi"

tls:
	if [ ! -f ./config/.docker/certs/server.crt ]; then \
	  mkdir -p ./config/.docker/certs; \
      openssl req -x509 -nodes -newkey rsa:4096 \
        -keyout ./config/.docker/certs/server.key \
        -out ./config/.docker/certs/server.crt \
        -subj '/C=US/ST=NA/L=NA/O=Docker/OU=Development/CN=127.0.0.1/emailAddress=noreply@example.com'; \
    fi

down:
	docker-compose down