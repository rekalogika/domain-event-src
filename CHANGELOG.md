# Changelog

## 1.2.1

* build: Bump minimal Symfony version to 6.4.
* build: Update `php-cs-fixer` and config.

## 1.2.0

* Supports Symfony 7

## 1.1.9

* php-cs-fixer run
## 1.1.8

* Improve CI
* Remove dev dependency on symfony/uid
* Method signature fixes with earlier Symfony versions

## 1.1.7

* remove unneeded event argument in DomainEventReaper

## 1.1.6

* reap events on console errors & install immediate dispatchers at the beginning of a console command
* fix psalm configuration

## 1.1.5

* fix integration test

## 1.1.4

* fix disabling autodispatch not previously working
* change psalm config to scan everything under packages

## 1.1.3

* lessen version requirements of symfony packages
* add docs about immediate dispatcher installation

## 1.1.2

* Installs immediate dispatcher when ManagerRegistry or EntityManager is initialized
* don't complain if the dispatcher get installed twice

## 1.1.1

* add keywords and fix description
