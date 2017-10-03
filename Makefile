SHELL := /bin/bash

BASENAME=$(shell echo $(shell basename $(shell pwd)) | tr -dc '[:alnum:]\n\r' | tr '[:upper:]' '[:lower:]')
PHP_CONTAINER=$(BASENAME)_php_1
REDIS_CONTAINER=$(BASENAME)_redis_1
CURRENTDIR=$(shell pwd)

all: docker composer

composer-update:
	docker exec -it $(PHP_CONTAINER) /bin/bash -lc "/root/.bin/composer update -ovn --prefer-source"

composer:
	docker exec -it $(PHP_CONTAINER) /bin/bash -lc "/root/.bin/composer install -ovn --prefer-source"

redis:
	docker exec -it $(REDIS_CONTAINER) redis-cli

php:
	docker exec -it $(PHP_CONTAINER) php -a

docker: down
	docker-compose pull --parallel
ifeq ($(REBUILD), true)
	docker-compose up -d --remove-orphans --force-recreate
else
	docker-compose up -d --remove-orphans
endif

down:
	docker-compose down