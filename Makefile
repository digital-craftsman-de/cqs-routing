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
	docker compose build

## up				Start the Docker stack.
.PHONY: up
up: .up

.up:
	docker compose up -d

## down				Stop the Docker stack.
.PHONY: down
down: .down

.down:
	docker compose down

## update				Rebuild Docker images and start stack.
.PHONY: update
update: build up

## reset				Teardown stack, install and start.
.PHONY: reset
reset: .reset

.PHONY: .reset
.reset: .down .install .up

## install			Install PHP dependencies with the default PHP version (8.4).
.PHONY: .install
install: install-8.4

## install-8.4			Install PHP dependencies with PHP 8.4.
.PHONY: install-8.4
install-8.4:
	docker compose run --rm php-8.4 composer install

## php-cli			Enter a shell for the default PHP version (8.4).
.PHONY: php-cli
php-cli: php-8.4-cli

## php-8.4-cli			Enter a shell for PHP 8.4.
.PHONY: php-8.4-cli
php-8.4-cli:
	docker compose run --rm php-8.4 sh

##
## Tests and code validation
## -------------------------
##

## verify				Run all validations and tests.
.PHONY: verify
verify: php-code-validation php-tests

## php-tests			Run the tests for all relevant PHP versions.
.PHONY: php-tests
php-tests: php-8.4-tests

## php-8.4-tests			Run tests with PHP 8.4.
.PHONY: php-8.4-tests
php-8.4-tests:
	docker compose run --rm php-8.4 ./vendor/bin/phpunit

## php-tests-coverage			Run the tests for all relevant PHP versions.
.PHONY: php-tests-coverage
php-tests-coverage: php-8.4-tests-html-coverage

## php-8.4-tests-html-coverage	Run the tests with PHP 8.4 including coverage report as HTML.
.PHONY: php-8.4-tests-html-coverage
php-8.4-tests-html-coverage:
	docker compose run --rm php-8.4 ./vendor/bin/phpunit --coverage-html ./coverage

## php-code-validation		Run code fixers and linters with default PHP version (8.4).
.PHONY: php-code-validation
php-code-validation:
	docker compose run --rm php-8.4 ./vendor/bin/php-cs-fixer fix
	docker compose run --rm php-8.4 ./vendor/bin/psalm --show-info=false --no-diff --no-cache

##
## CI
## --
##

## php-8.4-tests-ci		Run the tests for PHP 8.4 for CI.
.PHONY: php-8.4-tests-ci
php-8.4-tests-ci:
	docker compose run --rm php-8.4 ./vendor/bin/phpunit --coverage-clover ./coverage.xml
