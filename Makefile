SHELL := /bin/bash

BASENAME=$(shell echo $(shell basename $(shell pwd)) | tr -dc '[:alnum:]\n\r' | tr '[:upper:]' '[:lower:]')
CURRENTDIR=$(shell pwd)

all: docker composer migrate

enable-xdebug:
	docker-compose exec php /bin/bash -lc "echo xdebug.remote_host=$(IP) | tee -a /etc/php/7.1/conf.d/xdebug.ini"
	docker-compose restart php
	docker-compose exec php php -i | grep xdebug.remote_host

composer-update:
	docker-compose exec php /bin/bash -lc "/root/.bin/composer update -ovn --prefer-source"

composer:
	docker-compose exec php /bin/bash -lc "/root/.bin/composer install -ovn --prefer-source"

sql:
	docker-compose exec mariadb mysql -u local -plocal root

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