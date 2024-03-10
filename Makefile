SHELL = /bin/bash

uid = $$(id -u)
gid = $$(id -g)
pwd = $$(pwd)

default: help

##
## Help
## ----
##

## help				Print commands help.
.PHONY: help

help: Makefile
	@sed -n 's/^##//p' $<

##
## Docker
## ------
##

## build				Build the Docker images.
.PHONY: build
build:
	docker-compose build

## up				Start the Docker stack.
.PHONY: up
up: .up

.up:
	docker-compose up -d

## down				Stop the Docker stack.
.PHONY: down
down: .down

.down:
	docker-compose down

## update				Rebuild Docker images and start stack.
.PHONY: update
update: build up

## reset				Teardown stack, install and start.
.PHONY: reset
reset: .reset

.PHONY: .reset
.reset: .down .install .up

## install			Install PHP dependencies with the default PHP version (8.2).
.PHONY: .install
install: install-8.3

## install-8.2			Install PHP dependencies with PHP 8.2.
.PHONY: install-8.2
install-8.2:
	docker-compose run --rm php-8.2 composer install

## install-8.3			Install PHP dependencies with PHP 8.3.
.PHONY: install-8.3
install-8.3:
	docker-compose run --rm php-8.3 composer install

## php-cli			Enter a shell for the default PHP version (8.2).
.PHONY: php-cli
php-cli: php-8.3-cli

## php-8.2-cli			Enter a shell for PHP 8.2.
.PHONY: php-8.2-cli
php-8.2-cli:
	docker-compose run --rm php-8.2 sh

## php-8.3-cli			Enter a shell for PHP 8.3.
.PHONY: php-8.3-cli
php-8.3-cli:
	docker-compose run --rm php-8.3 sh

##
## Tests and code validation
## -------------------------
##

## verify				Run all validations and tests.
.PHONY: verify
verify: php-code-validation php-tests

## php-tests			Run the tests for all relevant PHP versions.
.PHONY: php-tests
php-tests: php-8.2-tests php-8.3-tests

## php-8.2-tests			Run tests with PHP 8.2.
.PHONY: php-8.2-tests
php-8.2-tests:
	docker-compose run --rm php-8.2 ./vendor/bin/phpunit

## php-8.3-tests			Run tests with PHP 8.3.
.PHONY: php-8.3-tests
php-8.3-tests:
	docker-compose run --rm php-8.3 ./vendor/bin/phpunit

## php-8.2-tests-html-coverage	Run the tests with PHP 8.2 including coverage report as HTML.
.PHONY: php-8.2-tests-html-coverage
php-8.2-tests-html-coverage:
	docker-compose run --rm php-8.2 ./vendor/bin/phpunit --coverage-html ./coverage

## php-8.3-tests-html-coverage	Run the tests with PHP 8.3 including coverage report as HTML.
.PHONY: php-8.3-tests-html-coverage
php-8.3-tests-html-coverage:
	docker-compose run --rm php-8.3 ./vendor/bin/phpunit --coverage-html ./coverage

## php-code-validation		Run code fixers and linters with default PHP version (8.2).
.PHONY: php-code-validation
php-code-validation:
	docker-compose run --rm php-8.3 ./vendor/bin/php-cs-fixer fix
	docker-compose run --rm php-8.3 ./vendor/bin/psalm --show-info=false --no-diff

##
## CI
## --
##

## php-8.2-tests-ci		Run the tests for PHP 8.2 for CI.
.PHONY: php-8.2-tests-ci
php-8.2-tests-ci:
	docker-compose run --rm php-8.2 ./vendor/bin/phpunit --coverage-clover ./coverage.xml

## php-8.3-tests-ci		Run the tests for PHP 8.3 for CI.
.PHONY: php-8.3-tests-ci
php-8.3-tests-ci:
	docker-compose run --rm php-8.3 ./vendor/bin/phpunit
