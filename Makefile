SHELL = /bin/bash

uid = $$(id -u)
gid = $$(id -g)
pwd = $$(pwd)

default: up

## update		Rebuild Docker images and start stack.
.PHONY: update
update: build up

## reset		Teardown stack, install and start.
.PHONY: reset
reset: .reset

.PHONY: .reset
.reset: .down .install .up

##
## Docker
## ------
##

## install	Install API and client dependencies as well as setup the database.
.PHONY: install
install: .install

.PHONY: .install
.install:
	docker-compose run --rm php composer install

## build		Build the Docker images.
.PHONY: build
build:
	docker-compose build

## up		Start the Docker stack.
.PHONY: up
up: .up

.up:
	docker-compose up -d

## down		Stop the Docker stack.
.PHONY: down
down: .down

.down:
	docker-compose down

## php-cli	Enter a shell for the API.
.PHONY: php-cli
php-cli:
	docker-compose run --rm php sh

##
## Tests
## -----
##

## php-tests		Run the PHP tests.
.PHONY: php-tests
php-tests:
	docker-compose run --rm php ./vendor/bin/phpunit

##
## Code validations
## ----------------
##

## php-code-validation		Run code fixers and linters for PHP.
.PHONY: php-code-validation
php-code-validation:
	docker-compose run --rm php ./vendor/bin/php-cs-fixer fix
	docker-compose run --rm php ./vendor/bin/psalm --show-info=false --no-diff
