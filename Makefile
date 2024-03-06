.PHONY: test
test: clean phpstan psalm lint-container phpunit

.PHONY: clean
clean:
	rm -rf var

.PHONY: monorepo
monorepo: validate merge

.PHONY: merge
merge:
	vendor/bin/monorepo-builder merge

.PHONY: validate
validate:
	vendor/bin/monorepo-builder validate

.PHONY: lint-container
lint-container:
	tests/bin/console lint:container

.PHONY: phpstan
phpstan:
	vendor/bin/phpstan analyse

.PHONY: psalm
psalm:
	vendor/bin/psalm

.PHONY: phpunit
phpunit: clean
	$(eval c ?=)
	vendor/bin/phpunit $(c)

.PHONY: php-cs-fixer
php-cs-fixer: tools/php-cs-fixer
	$< fix --config=.php-cs-fixer.dist.php --verbose --allow-risky=yes

.PHONY: tools/php-cs-fixer
tools/php-cs-fixer:
	phive install php-cs-fixer